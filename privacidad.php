<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Política de Privacidad | <?= SITE_NAME ?></title>
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= SITE_URL ?>/privacidad.php">
<link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/img/favicon.svg">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="logo"><?= SITE_NAME ?> <small>Cuidamos a tu mascota</small></div>
    <div class="nav-links">
        <a href="<?= SITE_URL ?>">Inicio</a>
        <a href="<?= SITE_URL ?>/#servicios">Servicios</a>
    </div>
    <a class="btn-wa" href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Hola, quiero agendar una hora" target="_blank">WhatsApp</a>
</nav>

<section class="legal-sect">
    <h1>Política de Privacidad</h1>
    <p class="legal-strong">Última actualización: <?= date('d/m/Y') ?></p>

    <h2>1. ¿Quién es el responsable?</h2>
    <p><?= SITE_NAME ?>, con local en <?= ADDRESS ?>. Puedes contactarnos por WhatsApp al <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>">+56 9 9599 9482</a>.</p>

    <h2>2. ¿Qué datos recopilamos?</h2>
    <p>Recopilamos únicamente los datos que nos entregas voluntariamente al usar el sitio:</p>
    <ul>
        <li>Nombre, teléfono y/o correo, al agendar una hora o usar formularios.</li>
        <li>El contenido de las consultas que haces por el asistente virtual o por formularios.</li>
        <li>Si el sitio tiene activo Google Analytics/Ads, datos de navegación anónimos (páginas visitadas, dispositivo) para medir el tráfico.</li>
    </ul>

    <h2>3. ¿Para qué usamos tus datos?</h2>
    <ul>
        <li>Para responder tus consultas y coordinar las atenciones veterinarias.</li>
        <li>Para recordarte horas agendadas y darte seguimiento post-atención.</li>
        <li>Para mejorar nuestros servicios (análisis agregado y anónimo de consultas).</li>
    </ul>

    <h2>4. ¿Compartimos tus datos?</h2>
    <p>No vendemos ni alquilamos tus datos. Solo los vemos nosotros y los servicios técnicos que usamos para funcionar (como el hosting). La información que compartes con el asistente virtual puede ser procesada por un proveedor de inteligencia artificial (Google Gemini) únicamente para generar la respuesta.</p>

    <h2>5. WhatsApp</h2>
    <p>Si nos escribes por WhatsApp, la conversación queda sujeta a las condiciones de privacidad de WhatsApp/Meta además de esta política.</p>

    <h2>6. Cookies</h2>
    <p>Este sitio usa cookies técnicas necesarias para su funcionamiento. Si activamos Google Analytics/Ads, se instalarán cookies de terceros; si eso ocurre, te mostraremos un aviso previo.</p>

    <h2>7. Seguridad</h2>
    <p>Tomamos medidas razonables para proteger tu información (acceso restringido al panel administrativo, conexión segura en producción). Ninguna transmisión por internet es 100% segura.</p>

    <h2>8. Tus derechos</h2>
    <p>Puedes pedirnos que accedamos, corrijamos o eliminemos tus datos, o dejar de recibir comunicaciones, escribiéndonos por WhatsApp. El asistente virtual no guarda datos personales más allá de la consulta realizada.</p>

    <h2>9. Cambios a esta política</h2>
    <p>Si hacemos cambios importantes, los publicaremos en esta misma página, actualizando la fecha.</p>
</section>

<footer class="footer">
    <p><?= SITE_NAME ?> · <?= ADDRESS ?> · <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>">+569 9599 9482</a></p>
    <p><a href="<?= SITE_URL ?>/privacidad.php">Política de Privacidad</a> · <a href="<?= SITE_URL ?>/terminos.php">Términos y Condiciones</a></p>
    <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?></p>
</footer>

</body>
</html>