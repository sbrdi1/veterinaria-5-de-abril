<?php
/*
 * Notificaciones por correo (SMTP) al dueño del sitio.
 * Solo actúa si SMTP_HOST está configurado en config.php. Si no, no hace nada.
 * Uso: require incl/notify.php; notify_event('reserva', [...datos...]);
 */

if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', '');
    define('SMTP_PORT', 587);
    define('SMTP_USER', '');
    define('SMTP_PASS', '');
    define('SMTP_SECURE', 'tls'); // tls | ssl | '' (sin cifrado)
    define('SMTP_TO', '');
}

function notify_event(string $tipo, array $datos): void {
    if (SMTP_HOST === '' || SMTP_TO === '') return;

    $asunto = '[' . SITE_NAME . '] Nueva ' . $tipo . ' recibida';
    $body = "Se registro una nueva " . $tipo . " en el sitio web.\n\n";
    foreach ($datos as $clave => $valor) {
        if ($valor === null || $valor === '') $valor = '-';
        $body .= ucfirst((string)$clave) . ": " . $valor . "\n";
    }
    $body .= "\nRevisa el panel administrativo: " . SITE_URL . "/admin/\n";

    smtp_send(SMTP_TO, $asunto, $body);
}

function smtp_send(string $to, string $subject, string $body): bool {
    $host = SMTP_HOST;
    $port = (int)SMTP_PORT ?: 587;
    $secure = SMTP_SECURE;

    $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
    if ($secure === 'ssl') {
        $conn = @stream_socket_client("ssl://$host:$port", $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
    } else {
        $conn = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 15);
    }
    if (!$conn) return false;
    stream_set_timeout($conn, 15);

    $read = static function () use ($conn) { return fgets($conn, 4096); };
    $cmd = static function (string $line) use ($conn, $read) {
        fwrite($conn, $line . "\r\n");
        return $read();
    };

    $read();
    $cmd('EHLO ' . (gethostname() ?: 'localhost'));
    if ($secure === 'tls') {
        $cmd('STARTTLS');
        stream_socket_enable_crypto($conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $cmd('EHLO ' . (gethostname() ?: 'localhost'));
    }
    $cmd('AUTH LOGIN');
    $cmd(base64_encode(SMTP_USER));
    $cmd(base64_encode(SMTP_PASS));
    $cmd('MAIL FROM:<' . SMTP_USER . '>');
    $cmd('RCPT TO:<' . $to . '>');
    $cmd('DATA');

    $headers = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n" .
        "To: " . $to . "\r\n" .
        "MIME-Version: 1.0\r\n" .
        "Content-Type: text/plain; charset=UTF-8\r\n" .
        "Content-Transfer-Encoding: base64\r\n\r\n";
    fwrite($conn, $headers . chunk_split(base64_encode($body)) . "\r\n.\r\n");
    $read();
    $cmd('QUIT');
    fclose($conn);
    return true;
}