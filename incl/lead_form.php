<?php $pub = flash('pub_ok'); ?>
<?php if ($pub): ?><div class="flash flash-ok lead-flash"><?= sanitize($pub) ?></div><?php endif; ?>

<div class="lead-box" id="aviso-hora">
    <h3>¿No hay hora cuando la necesitas?</h3>
    <p>Déjanos tu WhatsApp y te avisamos apenas tengamos disponibilidad.</p>
    <form method="post" action="<?= SITE_URL ?>/agenda.php" class="lead-form">
        <?= csrf_field() ?>
        <input type="hidden" name="tipo" value="lead">
        <input type="hidden" name="origen" value="<?= $lead_origen ?? 'index' ?>">
        <div class="lead-grid">
            <input type="text" name="nombre" placeholder="Tu nombre" required>
            <input type="tel" name="telefono" placeholder="WhatsApp" required>
            <input type="text" name="mascota" placeholder="Mascota (ej: Rocky)">
            <button type="submit" class="btn-new">Avísame</button>
        </div>
        <p class="lead-note">Solo te escribiremos cuando haya cupo. Nunca spam.</p>
    </form>
</div>