<?php require __DIR__ . '/_header.php'; ?>
<?php
csrf_guard();
if (isset($_GET['export'])) {
    if (!csrf_ok()) { http_response_code(403); exit('error'); }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="mensajes.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Nombre', 'Correo', 'Telefono', 'Servicio', 'Mensaje', 'Leido', 'Creado']);
    foreach (db()->query("SELECT * FROM mensajes ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC) as $m) {
        fputcsv($out, [$m['id'], $m['nombre'], $m['email'], $m['telefono'], $m['servicio'], $m['mensaje'], $m['leido'], $m['created_at']]);
    }
    fclose($out);
    exit;
}
if (isset($_GET['leido'])) {
    if (!csrf_ok()) { http_response_code(403); exit('Solicitud inválida (error de seguridad).'); }
    $id = (int)$_GET['leido'];
    $stmt = db()->prepare("UPDATE mensajes SET leido=1 WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header('Location: mensajes.php');
    exit;
}
if (isset($_GET['delete'])) {
    if (!csrf_ok()) { http_response_code(403); exit('Solicitud inválida (error de seguridad).'); }
    $id = (int)$_GET['delete'];
    $stmt = db()->prepare("DELETE FROM mensajes WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    flash('ok', 'Consulta eliminada.');
    header('Location: mensajes.php');
    exit;
}
$msgs = db()->query("SELECT * FROM mensajes ORDER BY leido ASC, created_at DESC")->fetch_all(MYSQLI_ASSOC);
$unread = db()->query("SELECT COUNT(*) FROM mensajes WHERE leido=0")->fetch_row()[0];
?>
<h1 class="admin-header">Consultas y Mensajes <?= $unread ? '<span class="badge">' . $unread . ' sin leer</span>' : '' ?>
    <a class="btn-view" href="<?= csrf_url('mensajes.php?export=1') ?>" style="margin-left:.5rem">Exportar CSV</a>
</h1>

<div class="card-admin">
    <?php if (!$msgs): ?>
        <p style="color:var(--muted)">Aún no hay consultas recibidas.</p>
    <?php endif; ?>
    <?php foreach ($msgs as $m): ?>
    <div class="msg-card <?= $m['leido'] ? '' : 'unread' ?>">
        <div class="msg-meta">
            <span><strong><?= sanitize($m['nombre']) ?></strong> <?= $m['telefono'] ? '· ' . sanitize($m['telefono']) : '' ?></span>
            <span><?= $m['created_at'] ?></span>
        </div>
        <?php if ($m['servicio']): ?><div style="font-size:.8rem;color:var(--muted);margin-bottom:.25rem">Servicio: <?= sanitize($m['servicio']) ?></div><?php endif; ?>
        <?php if ($m['email']): ?><div style="font-size:.8rem;color:var(--muted)"><?= sanitize($m['email']) ?></div><?php endif; ?>
        <div class="msg-text"><?= nl2br(sanitize($m['mensaje'])) ?></div>
        <div style="margin-top:.6rem; display:flex; gap:.5rem">
            <?php if (!$m['leido']): ?><a class="btn-mark" href="<?= csrf_url('mensajes.php?leido=' . (int)$m['id']) ?>">Marcar leído</a><?php endif; ?>
            <a class="btn-mark" href="<?= csrf_url('mensajes.php?delete=' . (int)$m['id']) ?>" onclick="return confirm('¿Eliminar consulta?')">Eliminar</a>
            <?php if ($m['telefono']): ?><a class="btn-wa-sm" target="_blank" href="https://wa.me/<?= str_replace(['+', ' ', '-'], '', $m['telefono']) ?>">Responder WhatsApp</a><?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</div>
</body>
</html>