<?php
/*
 * Asistente Virtual - endpoint AJAX
 * Responde consultas sobre la veterinaria. Si AI_API_KEY está configurada
 * usa Google Gemini (free tier); si no, responde con FAQ por palabras clave.
 */
require __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

// --- Solo POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

// --- Rate limit básico (1 mensaje cada ~2.5s por sesión) ---
$now = time();
$last = (int)($_SESSION['chat_last'] ?? 0);
if ($now - $last < 2) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Un momento… respóndeme la pregunta anterior.']);
    exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
$message = trim((string)($body['message'] ?? ''));

if ($message === '' || mb_strlen($message) > 600) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Mensaje vacío o demasiado largo.']);
    exit;
}

$_SESSION['chat_last'] = $now;

// --- Contexto real desde la BD ---
$ctx_servicios = [];
$rows = db()->query("SELECT s.nombre, s.precio, s.duracion, s.descripcion, c.nombre AS cat
                     FROM servicios s LEFT JOIN categorias c ON c.id = s.categoria_id
                     WHERE s.activo = 1 ORDER BY c.orden, s.nombre")->fetch_all(MYSQLI_ASSOC);
foreach ($rows as $r) {
    $precio = format_price((int)$r['precio']);
    $dur = $r['duracion'] ? ' · ' . $r['duracion'] : '';
    $ctx_servicios[] = "- {$r['nombre']}: {$precio}{$dur} (categoría {$r['cat']})";
}
$contexto_servicios = implode("\n", $ctx_servicios);

$wa = WHATSAPP_NUMBER;
$wa_link = "https://wa.me/{$wa}?text=" . urlencode('Hola, quiero agendar una hora.');

// --- Generar respuesta ---
$modo = 'faq';
$respuesta = '';

if (AI_API_KEY !== '') {
    $modo = 'gemini';
    $respuesta = gemini_preguntar($message, $contexto_servicios, $wa_link);
}

if ($respuesta === '') {
    $modo = 'faq';
    $respuesta = faq_responder($message, $contexto_servicios, $wa, $wa_link);
}

// --- Guardar en chat_logs ---
$stmt = db()->prepare("INSERT INTO chat_logs (mensaje, respuesta, modo) VALUES (?,?,?)");
$stmt->bind_param('sss', $message, $respuesta, $modo);
$stmt->execute();
$stmt->close();

echo json_encode(['ok' => true, 'response' => $respuesta, 'modo' => $modo]);
exit;

/* ------------------------------------------------------------------ */

/**
 * Llama a Google Gemini (free tier). Devuelve '' si falla.
 */
function gemini_preguntar(string $msg, string $ctx, string $wa_link): string {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . AI_MODEL . ':generateContent';

    $system = "Eres el asistente virtual de \"Veterinaria 5 de Abril\", una clínica veterinaria en Av. Lafquén 260, Maipú, Santiago de Chile. " .
        "Atención: Lunes a Sábado. WhatsApp: +56 9 9599 9482.\n" .
        "Responde SIEMPRE en español de Chile, de forma amable y BREVE (máximo 6 líneas). NO inventes precios que no estén en la lista. " .
        "Cuando el cliente quiera agendar, entrégalo textualmente: Agendar por WhatsApp: {$wa_link}\n" .
        "Servicios y precios reales:\n{$ctx}";

    $payload = [
        'systemInstruction' => ['parts' => [['text' => $system]]],
        'contents' => [['role' => 'user', 'parts' => [['text' => $msg]]]],
    ];

    $json = http_post_json($url . '?key=' . urlencode(AI_API_KEY), $payload);
    if ($json === null) return '';

    $data = json_decode($json, true);
    if (!$data) return '';

    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if (!trim((string)$text)) return '';
    return trim($text);
}

/**
 * POST JSON vía cURL (o file_get_contents como respaldo).
 */
function http_post_json(string $url, array $payload): ?string {
    $data = json_encode($payload);
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15,
        ]);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        return $err ? null : $resp;
    }
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $data,
            'timeout' => 15,
        ],
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    return $resp === false ? null : $resp;
}

/* ------------------------------------------------------------------ */

/**
 * Modo FAQ: responde por palabras clave usando los datos reales.
 */
function faq_responder(string $msg, string $ctx, string $wa, string $wa_link): string {
    $m = mb_strtolower($msg);

    if (preg_match('/hola|buenas|holi|hey/i', $m)) {
        return "¡Hola! Soy el asistente de Veterinaria 5 de Abril.\nPuedo ayudarte con precios, vacunas, castración, horarios y más.\nY si prefieres, escríbenos por WhatsApp: {$wa_link}";
    }

    if (preg_match('/precio|cu[áa]nto|cuanto|valor|tarifa|costo|cuesta|vale/i', $m)) {
        return "Estos son nuestros precios:\n{$ctx}\nMás información: Agendar por WhatsApp: {$wa_link}";
    }

    if (preg_match('/vacu|antir[r]?[áa]bica|polivalente|rabia|dosis/i', $m)) {
        $ext = '';
        if (preg_match('/gato|felino/i', $m)) $ext = 'Para gatos también tenemos vacuna de leucemia felina.';
        if (preg_match('/perro|canino/i', $m)) $ext = 'Para perros: antirrábica y polivalente.';
        $lin = faq_filtrar($ctx, ['Vacuna']);
        return "Sobre vacunas:\n{$lin}\n{$ext}Agenda tu vacuna aquí: {$wa_link}";
    }

    if (preg_match('/castr|esteriliz|operaci[óo]n|cirug/i', $m)) {
        $lin = faq_filtrar($ctx, ['Castración', 'Limpieza Dental']);
        return "Sobre castración:\n{$lin}\nEs un procedimiento seguro con seguimiento post-operatorio.\nConsulta disponibilidad: {$wa_link}";
    }

    if (preg_match('/desparasit|parasito|pulgas|garrapatas|anti-pulgas|antipulgas/i', $m)) {
        $lin = faq_filtrar($ctx, ['Desparasitación']);
        return "Sobre desparasitación:\n{$lin}\nConsulta el peso de tu mascota y te guiamos: {$wa_link}";
    }

    if (preg_match('/ba[ñn]o|peluquer|est[ée]tica|corte de u[ñn]as/i', $m)) {
        $lin = faq_filtrar($ctx, ['Baño', 'Corte de Uñas']);
        return "Servicios de higiene:\n{$lin}\nAgenda tu hora: {$wa_link}";
    }

    if (preg_match('/horari|hora de atenci|abre|cierra|atiende/i', $m)) {
        return "Atendemos de Lunes a Sábado.\nDirección: Av. Lafquén 260, Maipú.\nPara reservar tu hora: {$wa_link}";
    }

    if (preg_match('/d[óo]nde|direcci[óo]n|ubicaci[óo]n|mapa|llegar|queda/i', $m)) {
        return "Nos encuentras en Av. Lafquén 260, Maipú, Santiago (a pasos del centro de Maipú).\n¿Quieres que te guiemos llegando? Escríbenos: {$wa_link}";
    }

    if (preg_match('/urgen|emergen|ahora|intoxic|atropello|mordida|24/i', $m)) {
        $lin = faq_filtrar($ctx, ['Urgencia']);
        return "Para urgencias:\n{$lin}\nSi es una emergencia, escríbenos lo antes posible por WhatsApp y avisamos al equipo: {$wa_link}";
    }

    if (preg_match('/consulta|examen|revisi[óo]n|diagnost/i', $m)) {
        $lin = faq_filtrar($ctx, ['Consulta']);
        return "Nuestras consultas:\n{$lin}\n¿Quieres agendar una evaluación completa? {$wa_link}";
    }

    if (preg_match('/domicilio|a casa|visita/i', $m)) {
        return "La atención es en nuestro local (Av. Lafquén 260, Maipú).\nPara casos especiales escríbenos y vemos opciones: {$wa_link}";
    }

    if (preg_match('/whatsapp|escribir|llamar|telefono|telf/i', $m)) {
        return "Puedes escribirnos al WhatsApp: +56 9 9599 9482\n→ {$wa_link}";
    }

    return "No estoy seguro de haber entendido tu consulta.\nPuedo ayudarte con precios, vacunas, castración, horarios y dirección.\nO escríbenos directo por WhatsApp: {$wa_link}";
}

/**
 * Filtra las líneas del contexto que contengan alguna de las palabras clave.
 */
function faq_filtrar(string $ctx, array $keys): string {
    $lines = explode("\n", $ctx);
    $out = [];
    foreach ($lines as $line) {
        foreach ($keys as $k) {
            if (mb_stripos($line, $k) !== false) {
                $out[] = $line;
                break;
            }
        }
    }
    return $out ? implode("\n", $out) : $ctx;
}