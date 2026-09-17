<?php
require __DIR__ . '/config.php';

// Servicios destacados para la landing: traer los que correspondan a los 3 anchors
$cats = db()->query("SELECT * FROM categorias WHERE activa=1 ORDER BY orden, nombre")->fetch_all(MYSQLI_ASSOC);

function wa_link(string $msg): string {
    return 'https://wa.me/' . WHATSAPP_NUMBER . '?text=' . urlencode($msg);
}

$anchors = [
    'atencion' => [
        'cat_id'   => 1, // Consultas
        'titulo'   => 'Atención General',
        'sub'      => 'Consulta y revisión completa para tu mascota',
        'msg'      => 'Hola, quiero agendar una consulta general para mi mascota.',
    ],
    'vacunas' => [
        'cat_id'   => 2, // Vacunación
        'titulo'   => 'Vacunas',
        'sub'      => 'Vacunas antirrábica y polivalente con certificación',
        'msg'      => 'Hola, quiero consultar por las vacunas para mi mascota.',
    ],
    'castracion' => [
        'cat_id'   => 3, // Cirugía
        'titulo'   => 'Castración',
        'sub'      => 'Castración de perros y gatos con seguimiento',
        'msg'      => 'Hola, quiero consultar por la castración de mi mascota.',
    ],
];

foreach ($anchors as $k => &$a) {
    $a['servs'] = db()->query("SELECT * FROM servicios WHERE activo=1 AND categoria_id={$a['cat_id']} ORDER BY id LIMIT 4")->fetch_all(MYSQLI_ASSOC);
}
unset($a);

$whatsapp_msg_general = wa_link('Hola, quiero agendar una hora en Veterinaria 5 de Abril.');

$testimonios = db()->query("SELECT * FROM testimonios WHERE activo=1 ORDER BY created_at DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= SITE_NAME ?> | Maipú - Atención, Vacunas y Castración</title>
<meta name="description" content="Veterinaria en Maipú (Av. Lafquén 260). Atención general, vacunas y castración de perros y gatos. Agenda por WhatsApp al +569 9599 9482.">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= SITE_NAME ?>">
<meta property="og:title" content="<?= SITE_NAME ?> en Maipú - Agenda por WhatsApp">
<meta property="og:description" content="Atención general, vacunas y castración. Av. Lafquén 260, Maipú. Agenda hoy por WhatsApp.">
<meta property="og:locale" content="es_CL">
<meta property="og:url" content="<?= SITE_URL ?>/landing.php">
<meta property="og:image" content="<?= SITE_URL ?>/img/og-cover.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="<?= SITE_NAME ?> en Maipú">
<link rel="canonical" href="<?= SITE_URL ?>/landing.php">
<link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/img/favicon.svg">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
<link rel="stylesheet" href="<?= SITE_URL ?>/chat/chat.css">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "VeterinaryCare",
  "name": "<?= SITE_NAME ?>",
  "url": "<?= SITE_URL ?>/landing.php",
  "image": "<?= SITE_URL ?>/img/og-cover.png",
  "telephone": "+56 9 9599 9482",
  "priceRange": "$$",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Av. Lafquén 260",
    "addressLocality": "Maipú",
    "addressRegion": "Región Metropolitana",
    "addressCountry": "CL"
  },
  "openingHours": "Mo-Sa 10:00-19:00"
}
</script>
<?php if (GA_MEASUREMENT_ID !== ''): ?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= GA_MEASUREMENT_ID ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= GA_MEASUREMENT_ID ?>');
  document.addEventListener('click', function (e) {
    const a = e.target.closest('a[href*="wa.me"]');
    if (a) gtag('event', 'whatsapp_lead', { event_category: 'contacto', event_label: 'whatsapp' });
  });
</script>
<?php endif; ?>
</head>
<body>

<section class="land-hero">
    <h1>Veterinaria 5 de Abril</h1>
    <p class="lead">Atención general, vacunas y castración para perros y gatos en Maipú.</p>
    <div class="addr">Av. Lafquén 260, Maipú · Atención de lunes a sábado</div>
    <div class="hero-cta">
        <a class="btn-wa-lg" href="<?= $whatsapp_msg_general ?>" target="_blank">Agenda por WhatsApp</a>
        <a class="btn-sec" href="<?= SITE_URL ?>/agenda.php">Agendar por formulario</a>
    </div>
</section>

<div class="trust-strip">
    <span>Perros y gatos</span>
    <span>+569 9599 9482</span>
    <span>Precios claros</span>
</div>

<?php foreach ($anchors as $k => $a): ?>
<section class="land-section" id="<?= $k ?>">
    <h2><?= $a['titulo'] ?></h2>
    <p class="sec-sub"><?= $a['sub'] ?></p>
    <div class="grid">
        <?php foreach ($a['servs'] as $s): ?>
        <div class="card" style="min-height:0">
            <div class="card-body" style="min-height:0">
                <h4><?= sanitize($s['nombre']) ?></h4>
                <div class="card-meta" style="margin-top:0">
                    <span class="card-price"><?= format_price((int)$s['precio']) ?></span>
                    <?php if ($s['duracion']): ?><span class="card-dur"><?= sanitize($s['duracion']) ?></span><?php endif; ?>
                </div>
                <div style="margin-top:.75rem">
                    <a class="btn-wa-sm" target="_blank" href="<?= wa_link('Hola, quiero agendar: ' . $s['nombre'] . ' (' . format_price((int)$s['precio']) . ')') ?>">Agendar por WhatsApp</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="sec-cta">
        <a class="btn-wa-lg" href="<?= wa_link($a['msg']) ?>" target="_blank">Quiero <?= strtolower($a['titulo']) ?> · Agendar ya</a>
    </div>
</section>
<?php endforeach; ?>

<?php if (!empty($testimonios)): ?>
<section class="land-section">
    <h2>Nuestros clientes nos recomiendan</h2>
    <p class="sec-sub">Reseñas de vecinos de Maipú que ya confían en nosotros.</p>
    <div class="grid">
        <?php foreach ($testimonios as $t): ?>
        <div class="card" style="padding:1.2rem 1.2rem 1rem; box-shadow:none">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.5rem">
                <strong><?= sanitize($t['nombre']) ?></strong>
                <span style="color:#f59e0b; font-size:.9rem"><?= str_repeat('★', (int)$t['estrellas']) ?><span style="color:var(--muted)"><?= str_repeat('☆', 5 - (int)$t['estrellas']) ?></span></span>
            </div>
            <p style="color:var(--text); font-size:.92rem; flex:none; margin:0"><?= sanitize($t['texto']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="land-section" style="text-align:center">
    <h2>¿Dónde estamos?</h2>
    <p style="color:var(--muted); margin-bottom:1.2rem"><?= ADDRESS ?> · <?= HORARIOS ?></p>
    <div style="max-width:720px; margin:0 auto; border-radius:14px; overflow:hidden; border:1px solid var(--sky-200); box-shadow:0 8px 24px rgba(14,165,233,.12)">
        <iframe src="<?= MAPS_EMBED_URL ?>" width="100%" height="380" style="border:0" loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade" title="Mapa Veterinaria 5 de Abril"></iframe>
    </div>
    <div class="sec-cta">
        <a class="btn-wa-lg" href="<?= wa_link('Hola, quiero agendar una hora en Veterinaria 5 de Abril (Av. Lafquén 260, Maipú).') ?>" target="_blank">Agendar una hora</a>
    </div>
</section>

<section class="land-section" style="text-align:center">
    <div style="font-size:1.4rem; margin-bottom:.5rem; color:var(--primary-deep)">¿Dudas sobre el servicio?</div>
    <p style="color:var(--muted); margin-bottom:1.2rem">Escríbenos y te respondemos en minutos durante horario de atención.</p>
    <a class="btn-wa-lg" href="<?= wa_link('Hola, tengo una consulta sobre sus servicios.') ?>" target="_blank">Hablar con nosotros</a>
</section>

<?php $lead_origen = 'landing'; include __DIR__ . '/incl/lead_form.php'; ?>

<footer class="footer">
    <p><?= SITE_NAME ?> · Av. Lafquén 260, Maipú · <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>">+569 9599 9482</a></p>
    <p><a href="<?= SITE_URL ?>/privacidad.php">Política de Privacidad</a> · <a href="<?= SITE_URL ?>/terminos.php">Términos y Condiciones</a></p>
    <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?></p>
</footer>

<a class="wa-fixed" href="<?= $whatsapp_msg_general ?>" target="_blank">WhatsApp</a>

<?php include __DIR__ . '/chat/_widget.php'; ?>

</body>
</html>