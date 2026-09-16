<?php
require __DIR__ . '/config.php';

$cats = db()->query("SELECT * FROM categorias WHERE activa=1 ORDER BY orden, nombre")->fetch_all(MYSQLI_ASSOC);

$by_cat = [];
foreach ($cats as $c) {
    $by_cat[$c['id']] = [
        'cat' => $c,
        'servs' => db()->query("SELECT * FROM servicios WHERE activo=1 AND categoria_id={$c['id']} ORDER BY id")->fetch_all(MYSQLI_ASSOC),
    ];
}
$dest = db()->query("SELECT * FROM servicios WHERE activo=1 AND destacado=1 ORDER BY created_at DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$testimonios = db()->query("SELECT * FROM testimonios WHERE activo=1 ORDER BY created_at DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$img_cache = [];
function svc_img($img) {
    if (!$img) return null;
    return SITE_URL . '/uploads/servicios/' . $img;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= SITE_NAME ?> | Servicios Veterinarios</title>
<meta name="description" content="Veterinaria en Maipú (Av. Lafquén 260). Atención general, vacunas, castración y urgencias para perros y gatos. Agenda por WhatsApp al +569 9599 9482.">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= SITE_NAME ?>">
<meta property="og:title" content="<?= SITE_NAME ?> | Servicios Veterinarios en Maipú">
<meta property="og:description" content="Atención general, vacunas, castración y urgencias. Av. Lafquén 260, Maipú. Agenda por WhatsApp.">
<meta property="og:locale" content="es_CL">
<meta property="og:url" content="<?= SITE_URL ?>">
<link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/img/favicon.svg">
<link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "VeterinaryCare",
  "name": "<?= SITE_NAME ?>",
  "url": "<?= SITE_URL ?>",
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

<nav class="navbar">
    <div class="logo"><?= SITE_NAME ?> <small>Cuidamos a tu mascota</small></div>
    <div class="nav-links">
        <a href="<?= SITE_URL ?>" class="active">Inicio</a>
        <a href="#servicios">Servicios</a>
        <a href="#higiene">Higiene</a>
    </div>
    <a class="btn-wa" href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=Hola, quiero agendar una hora" target="_blank">WhatsApp</a>
</nav>

<section class="hero">
    <h1>Bienvenidos a <?= SITE_NAME ?></h1>
    <p>Servicios veterinarios profesionales con precios claros y atención con cariño para tu mascota.</p>
    <div class="addr">Av. Lafquén 260, Maipú, Santiago</div>
    <div class="hero-cta">
        <a class="btn-wa-lg" target="_blank"
           href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=<?= urlencode('Hola, quiero agendar una hora en Veterinaria 5 de Abril.') ?>">Agendar por WhatsApp</a>
    </div>
</section>

<section class="cats-section" id="servicios">
    <h2>Nuestros Servicios</h2>

    <?php if (!empty($dest)): ?>
    <div class="cat-group">
        <h3>Destacados</h3>
        <div class="grid">
            <?php foreach ($dest as $s): ?>
            <div class="card">
                <?php $im = svc_img($s['imagen']); ?>
                <?php if ($im): ?><img src="<?= $im ?>" alt="<?= sanitize($s['nombre']) ?>" loading="lazy">
                <?php else: ?><div class="no-img"><?= SITE_NAME ?></div><?php endif; ?>
                <div class="card-body">
                    <h4><?= sanitize($s['nombre']) ?></h4>
                    <p><?= sanitize($s['descripcion']) ?></p>
                    <div class="card-meta">
                        <span class="card-price"><?= format_price((int)$s['precio']) ?></span>
                        <?php if ($s['duracion']): ?><span class="card-dur"><?= sanitize($s['duracion']) ?></span><?php endif; ?>
                    </div>
                    <div style="margin-top:.75rem">
                        <a class="btn-wa-sm" target="_blank"
                           href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=<?= urlencode('Hola, quiero agendar: ' . $s['nombre'] . ' (' . format_price((int)$s['precio']) . ')') ?>">Agendar por WhatsApp</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($by_cat as $group): ?>
    <?php if (empty($group['servs'])) continue; ?>
    <div class="cat-group">
        <h3><?= sanitize($group['cat']['nombre']) ?></h3>
        <div class="grid">
            <?php foreach ($group['servs'] as $s): ?>
            <div class="card">
                <?php $im = svc_img($s['imagen']); ?>
                <?php if ($im): ?><img src="<?= $im ?>" alt="<?= sanitize($s['nombre']) ?>" loading="lazy">
                <?php else: ?><div class="no-img"><?= SITE_NAME ?></div><?php endif; ?>
                <div class="card-body">
                    <h4><?= sanitize($s['nombre']) ?></h4>
                    <p><?= sanitize($s['descripcion']) ?></p>
                    <div class="card-meta">
                        <span class="card-price"><?= format_price((int)$s['precio']) ?></span>
                        <?php if ($s['duracion']): ?><span class="card-dur"><?= sanitize($s['duracion']) ?></span><?php endif; ?>
                    </div>
                    <div style="margin-top:.75rem">
                        <a class="btn-wa-sm" target="_blank"
                           href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=<?= urlencode('Hola, quiero agendar: ' . $s['nombre'] . ' (' . format_price((int)$s['precio']) . ')') ?>">Agendar por WhatsApp</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<?php if (!empty($testimonios)): ?>
<div class="cat-group">
    <h3>Nuestros clientes nos recomiendan</h3>
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
</div>
<?php endif; ?>
</section>

<footer class="footer">
    <p><?= SITE_NAME ?> · Av. Lafquén 260, Maipú · <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>">+569 9599 9482</a></p>
    <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?></p>
</footer>

</body>
</html>
</html>