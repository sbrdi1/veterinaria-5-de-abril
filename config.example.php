<?php
// Copia este archivo como config.php y completa los valores reales.
// config.php NO se sube al repositorio (.gitignore).
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'vet5abril');
define('DB_USER', 'TU_USUARIO_BD');
define('DB_PASS', 'TU_CONTRASEÑA_BD');

define('SITE_NAME', 'Veterinaria 5 de Abril');
define('SITE_URL', 'https://TU-DOMINIO.cl'); // sin barra final
define('WHATSAPP_NUMBER', '56995999482');

// Dirección del local
define('ADDRESS', 'Av. Lafquén 260, Maipú, Santiago');
define('ADDRESS_SHORT', 'Lafquén 260, Maipú');
define('HORARIOS', 'Lunes a Sábado'); // ej: 'Lunes a Viernes 10:00-19:00 · Sábado 10:00-14:00'

// Mapa embebido (Google Maps sin API)
define('MAPS_EMBED_URL', 'https://maps.google.com/maps?q=' . urlencode('Av. Lafquén 260, Maipú, Santiago, Chile') . '&z=15&output=embed');

// Google Analytics / Google Ads - Measurement ID (ej: G-XXXXXXXXXX)
// Poner '' para desactivar el tag
define('GA_MEASUREMENT_ID', '');

define('UPLOADS_DIR', __DIR__ . '/uploads/');
define('SERVICIOS_IMG_DIR', UPLOADS_DIR . 'servicios/');
define('CATEGORIAS_IMG_DIR', UPLOADS_DIR . 'categorias/');

function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) die('DB error: ' . $conn->connect_error);
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function auth_check(): bool {
    return isset($_SESSION['user_id']);
}

function auth_require(): void {
    if (!auth_check()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function require_admin(): void {
    auth_require();
    $u = auth_user();
    if (empty($u['rol']) || $u['rol'] !== 'admin') {
        header('Location: ' . SITE_URL . '/admin/index.php');
        exit;
    }
}

function auth_user(): array {
    if (!auth_check()) return [];
    return $_SESSION['user'] ?? [];
}

function sanitize(string $val): string {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

function flash(string $key, ?string $msg = null): ?string {
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
        return null;
    }
    $val = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $val;
}

function format_price(int $clp): string {
    return '$' . number_format($clp, 0, ',', '.');
}