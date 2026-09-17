<?php require __DIR__ . '/_header.php'; ?>
<?php
csrf_guard();

if (isset($_GET['export'])) {
    if (!csrf_ok()) { http_response_code(403); exit('error'); }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reservas.csv"');
    echo "\xEF\xBB\xBF"; // BOM para Excel
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Nombre', 'Telefono', 'Email', 'Servicio', 'Mascota', 'Especie', 'Fecha preferida', 'Momento', 'Mensaje', 'Estado', 'Creado']);
    foreach (db()->query("SELECT * FROM reservas ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC) as $r) {
        fputcsv($out, [$r['id'], $r['nombre'], $r['telefono'], $r['email'], $r['servicio'], $r['mascota'], $r['especie'], $r['fecha_preferida'], $r['momento'], $r['mensaje'], $r['estado'], $r['created_at']]);
    }
    fclose($out);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['estado'])) {
        $estado = in_array($_POST['estado'], ['pendiente', 'confirmada', 'cancelada', 'completada'], true) ? $_POST['estado'] : 'pendiente';
        $stmt = db()->prepare("UPDATE reservas SET estado=? WHERE id=?");
        $stmt->bind_param('si', $estado, $id);
        $stmt->execute();
        $stmt->close();
        flash('ok', 'Estado de la reserva actualizado.');
    }
    header('Location: reservas.php');
    exit;
}

if (isset($_GET['delete'])) {
    if (!csrf_ok()) { http_response_code(403); exit('error'); }
    $id = (int)$_GET['delete'];
    $stmt = db()->prepare("DELETE FROM reservas WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    flash('ok', 'Reserva eliminada.');
    header('Location: reservas.php');
    exit;
}

$reservas = db()->query("SELECT * FROM reservas ORDER BY FIELD(estado,'pendiente','confirmada','completada','cancelada'), created_at DESC")->fetch_all(MYSQLI_ASSOC);
$pendientes = (int)db()->query("SELECT COUNT(*) FROM reservas WHERE estado='pendiente'")->fetch_row()[0];
?>
<h1 class="admin-header">Reservas de horas <?= $pendientes ? '<span class="badge">' . $pendientes . ' pendientes</span>' : '' ?></h1>

<div class="card-admin">
    <div class="admin-header">
        <h2 style="font-size:1.1rem">Solicitudes (<?= count($reservas) ?>)</h2>
        <a class="btn-view" href="<?= csrf_url('reservas.php?export=1') ?>">Exportar CSV</a>
    </div>
    <?php if (!$reservas): ?>
        <p style="color:var(--muted)">Aún no hay solicitudes de reserva. Cuando alguien agende desde el sitio, aparecerá aquí.</p>
    <?php else: ?>
    <table>
        <tr><th>ID</th><th>Cliente</th><th>Servicio</th><th>Mascota</th><th>Preferencia</th><th>Estado</th><th>Acciones</th></tr>
        <?php foreach ($reservas as $r): ?>
        <tr>
            <td>#<?= (int)$r['id'] ?></td>
            <td>
                <strong><?= sanitize($r['nombre']) ?></strong><br>
                <span style="color:var(--muted);font-size:.8rem"><?= sanitize($r['telefono']) ?><?= $r['email'] ? ' · ' . sanitize($r['email']) : '' ?></span>
            </td>
            <td><?= sanitize($r['servicio'] ?? '—') ?></td>
            <td><?= sanitize($r['mascota'] ?? '—') ?><?= $r['especie'] ? ' (' . sanitize($r['especie']) . ')' : '' ?></td>
            <td style="font-size:.85rem">
                <?= $r['fecha_preferida'] ? date('d/m/Y', strtotime($r['fecha_preferida'])) : '—' ?><br>
                <span style="color:var(--muted)"><?= sanitize($r['momento'] ?? '') ?></span>
                <?php if ($r['mensaje']): ?><br><span style="color:var(--muted)">"<?= sanitize(mb_strimwidth($r['mensaje'], 0, 60, '...')) ?>"</span><?php endif; ?>
            </td>
            <td>
                <form method="post" action="reservas.php" style="display:flex; gap:.3rem; align-items:center">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                    <select name="estado" style="padding:.3rem .4rem; border:1px solid var(--sky-200); border-radius:6px; font-size:.8rem">
                        <?php foreach (['pendiente', 'confirmada', 'completada', 'cancelada'] as $e): ?>
                        <option value="<?= $e ?>" <?= $r['estado'] === $e ? 'selected' : '' ?>><?= $e ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn-edit" style="border:none">OK</button>
                </form>
            </td>
            <td class="actions">
                <a class="btn-wa-sm" target="_blank" href="https://wa.me/<?= str_replace(['+', ' ', '-'], '', $r['telefono']) ?>?text=<?= urlencode('Hola ' . $r['nombre'] . ', somos ' . SITE_NAME . ', te confirmamos tu solicitud de ' . ($r['servicio'] ?? 'atención') . '. ¿Te va bien?') ?>">WhatsApp</a>
                <a class="btn-del" href="<?= csrf_url('reservas.php?delete=' . (int)$r['id']) ?>" onclick="return confirm('¿Eliminar esta reserva?')">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</div>
</div>
</body>
</html>