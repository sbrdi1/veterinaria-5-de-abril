<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Términos y Condiciones | <?= SITE_NAME ?></title>
<meta name="robots" content="index, follow">
<link rel="canonical" href="<?= SITE_URL ?>/terminos.php">
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
    <h1>Términos y Condiciones</h1>
    <p class="legal-strong">Última actualización: <?= date('d/m/Y') ?></p>

    <h2>1. Uso del sitio</h2>
    <p>Este sitio informa sobre los servicios de <?= SITE_NAME ?> y permite agendar horas y consultar precios. Los precios publicados pueden variar: confirma siempre el valor final al momento de agendar.</p>

    <h2>2. Agendamiento de horas</h2>
    <p>Agendar una hora a través del sitio es una solicitud, no una confirmación automática. Nuestro equipo te contactará por WhatsApp (o por el medio que hayas indicado) para confirmar el día y horario disponible. En algunos casos el servicio puede requerir evaluación previa de la mascota.</p>

    <h2>3. Atención veterinaria</h2>
    <ul>
        <li>La atención es presencial en nuestro local (<?= ADDRESS ?>).</li>
        <li>Las urgencias tienen prioridad; ante una emergencia, escríbenos por WhatsApp para coordinar.</li>
        <li>El asistente virtual entrega información general e informativa y no reemplaza el diagnóstico de un veterinario.</li>
        <li>Los procedimientos quirúrgicos (como castraciones) requieren consentimiento informado en el local.</li>
    </ul>

    <h2>4. Pagos</h2>
    <p>Los pagos se realizan en el local al momento de la atención (efectivo o medios disponibles según lo indicado por el equipo). No realizamos cobros previos por internet.</p>

    <h2>5. Responsabilidad</h2>
    <p>Nos esforzamos por mantener la información actualizada y correcta, pero no garantizamos que sea completamente exacta en todo momento. No seremos responsables por daños derivados del mal uso del sitio o por decisiones médicas tomadas sin la evaluación de un profesional.</p>

    <h2>6. Contacto</h2>
    <p>Para consultas sobre estos términos escríbenos por WhatsApp al <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>">+56 9 9599 9482</a>.</p>
</section>

<footer class="footer">
    <p><?= SITE_NAME ?> · <?= ADDRESS ?> · <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>">+569 9599 9482</a></p>
    <p><a href="<?= SITE_URL ?>/privacidad.php">Política de Privacidad</a> · <a href="<?= SITE_URL ?>/terminos.php">Términos y Condiciones</a></p>
    <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?></p>
</footer>

</body>
</html>