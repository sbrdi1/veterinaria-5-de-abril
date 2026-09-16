<?php
require_once __DIR__ . '/../config.php';
auth_require();
$me = auth_user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($page_title) ? $page_title . ' | ' : '' ?><?= SITE_NAME ?> Admin</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="logo">🐾 <?= SITE_NAME ?> <small>Admin</small></div>
    <div class="nav-links">
        <a href="index.php">Dashboard</a>
        <a href="servicios.php">Servicios</a>
        <a href="categorias.php">Categorías</a>
        <a href="testimonios.php">Testimonios</a>
        <a href="mensajes.php">Consultas</a>
        <a href="chat_logs.php">Asistente IA</a>
        <?php if (($me['rol'] ?? '') === 'admin'): ?><a href="usuarios.php">Usuarios</a><?php endif; ?>
        <a href="<?= SITE_URL ?>" target="_blank">Ver sitio</a>
    </div>
    <div class="nav-links">
        <span style="color:var(--muted); font-size:.85rem">👤 <?= sanitize($me['nombre']) ?></span>
        <a href="logout.php" style="color:#dc2626">Salir</a>
    </div>
</nav>
<div class="admin-wrap">
<?php
$ok = flash('ok');
$err = flash('err');
if ($ok): ?><div class="flash flash-ok"><?= sanitize($ok) ?></div><?php endif;
if ($err): ?><div class="flash flash-err"><?= sanitize($err) ?></div><?php endif; ?>