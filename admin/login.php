<?php
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $error = 'Sesión expirada, recarga la página e inténtalo de nuevo.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $bkey = brute_key($email);

        $block = brute_status($bkey);
        if ($block['blocked']) {
            $mins = (int)ceil($block['retry_in'] / 60);
            $error = 'Demasiados intentos fallidos. Intenta de nuevo en ' . $mins . ' min.';
        } else {
            $stmt = db()->prepare("SELECT * FROM usuarios WHERE email=? AND activo=1 LIMIT 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $u = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($u && password_verify($pass, $u['password'])) {
                brute_ok($bkey);
                session_regenerate_id(true);
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['user'] = ['id' => $u['id'], 'nombre' => $u['nombre'], 'email' => $u['email'], 'rol' => $u['rol']];
                header('Location: index.php');
                exit;
            }
            brute_fail($bkey);
            $error = 'Correo o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acceso | <?= SITE_NAME ?> Admin</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <h1>🐾 <?= SITE_NAME ?></h1>
        <div class="sub">Panel de administración</div>
        <?php if (isset($error)): ?><div class="flash flash-err"><?= sanitize($error) ?></div><?php endif; ?>
        <form method="post" action="">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Correo electrónico</label>
                <input type="email" name="email" required autofocus>
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-new" style="width:100%">Ingresar</button>
        </form>
        <p style="text-align:center; margin-top:1rem; font-size:.8rem; color:var(--muted)">
            <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>" target="_blank">¿Olvidaste tu contraseña? Contacta soporte</a>
        </p>
    </div>
</div>
</body>
</html>