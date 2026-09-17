<?php require __DIR__ . '/config.php';

$ok = null;
$err = null;
$origen = $_SERVER['HTTP_REFERER'] ?? '';
$servicios = db()->query("SELECT id, nombre, precio, duracion, categoria_id FROM servicios WHERE activo=1 ORDER BY categoria_id, nombre")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $err = 'Tu sesión expiró. Recarga la página y vuelve a enviar el formulario.';
    } else {
        $tipo = ($_POST['tipo'] ?? '') === 'lead' ? 'lead' : 'reserva';
        $nombre = trim($_POST['nombre'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $telefono = preg_replace('/[^0-9+]/', '', $telefono);

        if ($nombre === '' || $telefono === '') {
            $err = 'Por favor ingresa tu nombre y teléfono (es lo que usamos para contactarte).';
        } elseif ((int)$telefono < 8) {
            $err = 'El teléfono no parece válido. Revísalo e inténtalo de nuevo.';
        } elseif ($tipo === 'reserva') {
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL) ? trim($_POST['email']) : null;
            $servicio_id = (int)($_POST['servicio_id'] ?? 0);
            $servicio_nombre = '';
            foreach ($servicios as $s) if ((int)$s['id'] === $servicio_id) { $servicio_nombre = $s['nombre']; break; }
            $mascota = trim($_POST['mascota'] ?? '');
            $especie = in_array($_POST['especie'] ?? '', ['perro', 'gato', 'otro'], true) ? $_POST['especie'] : null;
            $fecha = !empty($_POST['fecha_preferida']) ? date('Y-m-d', strtotime($_POST['fecha_preferida'])) : null;
            $momento = in_array($_POST['momento'] ?? '', ['mañana', 'tarde', 'cualquiera'], true) ? $_POST['momento'] : null;
            $mensaje = trim($_POST['mensaje'] ?? '');

            $stmt = db()->prepare("INSERT INTO reservas (nombre, telefono, email, servicio_id, servicio, mascota, especie, fecha_preferida, momento, mensaje) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('sssissssss', $nombre, $telefono, $email, $servicio_id, $servicio_nombre, $mascota, $especie, $fecha, $momento, $mensaje);
            $stmt->execute();
            $rid = (int)db()->insert_id;
            $stmt->close();

            require __DIR__ . '/incl/notify.php';
            notify_event('solicitud de reserva', [
                'nombre' => $nombre,
                'telefono' => $telefono,
                'servicio' => $servicio_nombre,
                'mascota' => $mascota,
                'fecha preferida' => $fecha ? date('d/m/Y', strtotime($fecha)) : '',
                'momento' => $momento,
                'mensaje' => $mensaje,
            ]);

            $wa_text = urlencode("Hola, soy $nombre y agendé en la web: $servicio_nombre" . ($mascota ? " (mascota: $mascota)" : '') . ". ¿Me confirman la hora?");
            $ok = '¡Solicitud recibida! Te contactaremos por WhatsApp para confirmar el horario.';
            $wa_btn = "https://wa.me/" . WHATSAPP_NUMBER . "?text=" . $wa_text;
        } else {
            $mascota = trim($_POST['mascota'] ?? '');
            $stmt = db()->prepare("INSERT INTO leads (nombre, telefono, mascota) VALUES (?,?,?)");
            $stmt->bind_param('sss', $nombre, $telefono, $mascota);
            $stmt->execute();
            $stmt->close();
            require __DIR__ . '/incl/notify.php';
            notify_event('lead capturado', [
                'nombre' => $nombre,
                'telefono' => $telefono,
                'mascota' => $mascota,
            ]);
            flash('pub_ok', '¡Listo! Te avisaremos por WhatsApp cuando tengamos disponibilidad.');
            $destino = ($_POST['origen'] ?? '') === 'landing' ? '/landing.php' : '/index.php';
            header('Location: ' . SITE_URL . $destino);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agenda tu hora | <?= SITE_NAME ?></title>
<meta name="description" content="Agenda una hora en <?= SITE_NAME ?> (Av. Lafquén 260, Maipú). Completa el formulario y te confirmamos por WhatsApp.">
<link rel="canonical" href="<?= SITE_URL ?>/agenda.php">
<link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/img/favicon.svg">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo"><?= SITE_NAME ?> <small>Cuidamos a tu mascota</small></div>
    <div class="nav-links">
        <a href="<?= SITE_URL ?>">Inicio</a>
        <a href="<?= SITE_URL ?>/#servicios">Servicios</a>
        <a href="<?= SITE_URL ?>/landing.php">Promo</a>
    </div>
    <a class="btn-wa" href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Hola, quiero agendar una hora" target="_blank">WhatsApp</a>
</nav>

<section class="agenda-sect">
    <h1>Agenda una hora</h1>
    <p class="sub">Cuéntanos qué necesitas y te confirmamos por WhatsApp. Es una solicitud: nosotros coordinamos el día y horario.</p>

    <?php if ($ok): ?>
    <div class="flash flash-ok"><?= sanitize($ok) ?></div>
    <?php if (!empty($wa_btn)): ?>
    <p style="text-align:center; margin:1rem 0">
        <a class="btn-wa-lg" target="_blank" href="<?= $wa_btn ?>">Enviar por WhatsApp ahora</a>
    </p>
    <?php endif; ?>
    <p style="text-align:center; color:var(--muted)">¿Prefieres arreglarlo directo? <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Hola, quiero agendar una hora" target="_blank">Escríbenos por WhatsApp</a></p>
    <p style="text-align:center"><a href="<?= SITE_URL ?>" class="btn-cancel">Volver al inicio</a></p>

    <?php else: ?>

    <?php if ($err): ?><div class="flash flash-err"><?= sanitize($err) ?></div><?php endif; ?>

    <form method="post" action="agenda.php" class="agenda-form" id="form-reserva">
        <?= csrf_field() ?>
        <input type="hidden" name="tipo" value="reserva">
        <div class="form-grid">
            <div class="form-group">
                <label>Tu nombre *</label>
                <input type="text" name="nombre" required value="<?= sanitize($_POST['nombre'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Tu WhatsApp *</label>
                <input type="tel" name="telefono" required placeholder="+56 9 1234 5678" value="<?= sanitize($_POST['telefono'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Correo (opcional)</label>
                <input type="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Servicio *</label>
                <select name="servicio_id" required>
                    <option value="">— Elige un servicio —</option>
                    <?php foreach ($servicios as $s): ?>
                    <option value="<?= (int)$s['id'] ?>" <?= (int)($_POST['servicio_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>><?= sanitize($s['nombre']) ?> · <?= format_price((int)$s['precio']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Nombre de tu mascota</label>
                <input type="text" name="mascota" value="<?= sanitize($_POST['mascota'] ?? '') ?>" placeholder="Ej: Rocky">
            </div>
            <div class="form-group">
                <label>Es tu mascota...</label>
                <select name="especie">
                    <option value="">—</option>
                    <option value="perro" <?= ($_POST['especie'] ?? '') === 'perro' ? 'selected' : '' ?>>Perro</option>
                    <option value="gato" <?= ($_POST['especie'] ?? '') === 'gato' ? 'selected' : '' ?>>Gato</option>
                    <option value="otro" <?= ($_POST['especie'] ?? '') === 'otro' ? 'selected' : '' ?>>Otra</option>
                </select>
            </div>
            <div class="form-group">
                <label>Fecha preferida</label>
                <input type="date" name="fecha_preferida" min="<?= date('Y-m-d') ?>" value="<?= sanitize($_POST['fecha_preferida'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>¿Qué momento te acomoda?</label>
                <select name="momento">
                    <option value="cualquiera" <?= ($_POST['momento'] ?? '') === 'cualquiera' ? 'selected' : '' ?>>Cualquiera</option>
                    <option value="mañana" <?= ($_POST['momento'] ?? '') === 'mañana' ? 'selected' : '' ?>>Mañana</option>
                    <option value="tarde" <?= ($_POST['momento'] ?? '') === 'tarde' ? 'selected' : '' ?>>Tarde</option>
                </select>
            </div>
            <div class="form-group form-full">
                <label>Cuéntanos algo más (opcional)</label>
                <textarea name="mensaje" placeholder="Síntomas, dudas, lo que quieras..."><?= sanitize($_POST['mensaje'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="form-actions" style="justify-content:flex-start">
            <button type="submit" class="btn-new">Solicitar hora</button>
            <a href="<?= SITE_URL ?>" class="btn-cancel">Cancelar</a>
        </div>
        <p style="font-size:.8rem; color:var(--muted)">Al enviar aceptas nuestra <a href="<?= SITE_URL ?>/privacidad.php">política de privacidad</a>.</p>
    </form>
    <?php endif; ?>
</section>

<footer class="footer">
    <p><?= SITE_NAME ?> · Av. Lafquén 260, Maipú · <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>">+569 9599 9482</a></p>
    <p><a href="<?= SITE_URL ?>/privacidad.php">Política de Privacidad</a> · <a href="<?= SITE_URL ?>/terminos.php">Términos y Condiciones</a></p>
    <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?></p>
</footer>

</body>
</html>