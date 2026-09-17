<?php require __DIR__ . '/_header.php'; ?>
<?php
csrf_guard();

if (isset($_GET['export'])) {
    if (!csrf_ok()) { http_response_code(403); exit('error'); }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="leads.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Nombre', 'Telefono', 'Mascota', 'Seguido', 'Creado']);
    foreach (db()->query("SELECT * FROM leads ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC) as $l) {
        fputcsv($out, [$l['id'], $l['nombre'], $l['telefono'], $l['mascota'], $l['seguido'] ? 'si' : 'no', $l['created_at']]);
    }
    fclose($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['seguido'])) {
        db()->query("UPDATE leads SET seguido=" . ((int)$_POST['seguido'] ? 1 : 0) . " WHERE id=$id");
        flash('ok', 'Lead actualizado.');
    }
    header('Location: leads.php');
    exit;
}

if (isset($_GET['delete'])) {
    if (!csrf_ok()) { http_response_code(403); exit('error'); }
    $id = (int)$_GET['delete'];
    $stmt = db()->prepare("DELETE FROM leads WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    flash('ok', 'Lead eliminado.');
    header('Location: leads.php');
    exit;
}

$leads = db()->query("SELECT * FROM leads ORDER BY seguido ASC, created_at DESC")->fetch_all(MYSQLI_ASSOC);
$nuevos = (int)db()->query("SELECT COUNT(*) FROM leads WHERE seguido=0")->fetch_row()[0];
?>
<h1 class="admin-header">Leads capturados <?= $nuevos ? '<span class="badge" style="background:var(--orange,#f59e0b)">' . $nuevos . ' nuevos</span>' : '' ?></h1>
<p style="color:var(--muted); margin-bottom:1.5rem">Personas que pidieron "avísame cuando haya hora". Son clientes potenciales para tus campañas.</p>

<div class="card-admin">
    <div class="admin-header">
        <h2 style="font-size:1.1rem">Lista (<?= count($leads) ?>)</h2>
        <a class="btn-view" href="<?= csrf_url('leads.php?export=1') ?>">Exportar CSV</a>
    </div>
    <?php if (!$leads): ?>
        <p style="color:var(--muted)">Aún no hay leads capturados.</p>
    <?php else: ?>
    <table>
        <tr><th>Nombre</th><th>Teléfono</th><th>Mascota</th><th>Fecha</th><th>Seguimiento</th><th>Acciones</th></tr>
        <?php foreach ($leads as $l): ?>
        <tr>
            <td><?= sanitize($l['nombre'] ?? '—') ?></td>
            <td><?= sanitize($l['telefono']) ?></td>
            <td><?= sanitize($l['mascota'] ?? '—') ?></td>
            <td style="font-size:.85rem"><?= $l['created_at'] ?></td>
            <td>
                <form method="post" action="leads.php" style="display:flex; gap:.3rem; align-items:center">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
                    <select name="seguido" style="padding:.3rem .4rem; border:1px solid var(--sky-200); border-radius:6px; font-size:.8rem">
                        <option value="0" <?= !$l['seguido'] ? 'selected' : '' ?>>Pendiente</option>
                        <option value="1" <?= $l['seguido'] ? 'selected' : '' ?>>Contactado</option>
                    </select>
                    <button class="btn-edit" style="border:none">OK</button>
                </form>
            </td>
            <td class="actions">
                <a class="btn-wa-sm" target="_blank" href="https://wa.me/<?= str_replace(['+', ' ', '-'], '', $l['telefono']) ?>?text=<?= urlencode('Hola ' . ($l['nombre'] ?? '') . ', te saludamos de ' . SITE_NAME . '. Avísanos cuando quieras una hora, estamos en Maipú.') ?>">WhatsApp</a>
                <a class="btn-del" href="<?= csrf_url('leads.php?delete=' . (int)$l['id']) ?>" onclick="return confirm('¿Eliminar este lead?')">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>
</div>
</body>
</html>