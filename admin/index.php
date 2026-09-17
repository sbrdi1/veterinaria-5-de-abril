<?php require __DIR__ . '/_header.php'; ?>
<?php
$services    = (int)db()->query("SELECT COUNT(*) FROM servicios")->fetch_row()[0];
$active      = (int)db()->query("SELECT COUNT(*) FROM servicios WHERE activo=1")->fetch_row()[0];
$cats        = (int)db()->query("SELECT COUNT(*) FROM categorias")->fetch_row()[0];
$msgs        = (int)db()->query("SELECT COUNT(*) FROM mensajes WHERE leido=0")->fetch_row()[0];
$reservas    = (int)db()->query("SELECT COUNT(*) FROM reservas")->fetch_row()[0];
$pendientes  = (int)db()->query("SELECT COUNT(*) FROM reservas WHERE estado='pendiente'")->fetch_row()[0];
$leads       = (int)db()->query("SELECT COUNT(*) FROM leads")->fetch_row()[0];
$leads_nuev  = (int)db()->query("SELECT COUNT(*) FROM leads WHERE seguido=0")->fetch_row()[0];
$chat_total  = (int)db()->query("SELECT COUNT(*) FROM chat_logs")->fetch_row()[0];
$chat_week   = (int)db()->query("SELECT COUNT(*) FROM chat_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_row()[0];
$recent      = db()->query("SELECT s.*, c.nombre AS cat FROM servicios s LEFT JOIN categorias c ON c.id=s.categoria_id ORDER BY s.id DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$top_serv    = db()->query("SELECT servicio, COUNT(*) AS n FROM reservas WHERE servicio IS NOT NULL AND servicio<>'' GROUP BY servicio ORDER BY n DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$chat_days   = db()->query("SELECT DATE(created_at) AS d, COUNT(*) AS n FROM chat_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) GROUP BY DATE(created_at) ORDER BY d")->fetch_all(MYSQLI_ASSOC);
?>
<h1 class="admin-header">Dashboard</h1>
<div class="stat-grid">
    <div class="stat-box"><div class="num"><?= $pendientes ?></div><div class="label">Reservas pendientes</div></div>
    <div class="stat-box"><div class="num"><?= $reservas ?></div><div class="label">Reservas totales</div></div>
    <div class="stat-box"><div class="num"><?= $leads_nuev ?></div><div class="label">Leads nuevos</div></div>
    <div class="stat-box"><div class="num"><?= $leads ?></div><div class="label">Leads totales</div></div>
    <div class="stat-box"><div class="num"><?= $chat_week ?></div><div class="label">Consultas chat (7 días)</div></div>
    <div class="stat-box"><div class="num"><?= $msgs ?></div><div class="label">Mensajes sin leer</div></div>
</div>

<div class="card-admin">
    <div class="admin-header">
        <h2 style="font-size:1.1rem">Últimos servicios</h2>
        <a class="btn-new" href="servicios.php?edit=0">+ Nuevo Servicio</a>
    </div>
    <table>
        <tr><th>Servicio</th><th>Categoría</th><th>Precio</th><th>Estado</th></tr>
        <?php foreach ($recent as $s): ?>
        <tr>
            <td><a href="servicios.php?edit=<?= (int)$s['id'] ?>"><?= sanitize($s['nombre']) ?></a></td>
            <td><?= sanitize($s['cat'] ?? '—') ?></td>
            <td><?= format_price((int)$s['precio']) ?></td>
            <td><?= $s['activo'] ? '<span class="badge">Activo</span>' : '<span style="color:#dc2626;font-size:.8rem">Inactivo</span>' ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<?php if (!empty($top_serv)): ?>
<div class="card-admin">
    <h2 style="font-size:1.1rem; margin-bottom:1rem">Servicios más reservados</h2>
    <table>
        <tr><th>Servicio</th><th>Reservas</th></tr>
        <?php foreach ($top_serv as $t): ?>
        <tr>
            <td><?= sanitize($t['servicio']) ?></td>
            <td><?= (int)$t['n'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php endif; ?>

<?php if (!empty($chat_days)): ?>
<div class="card-admin">
    <h2 style="font-size:1.1rem; margin-bottom:1rem">Consultas al asistente (últimos 14 días)</h2>
    <div style="display:flex; flex-wrap:wrap; gap:.6rem">
        <?php foreach ($chat_days as $c): ?>
        <div class="stat-box" style="padding:.8rem">
            <div class="num" style="font-size:1.2rem"><?= (int)$c['n'] ?></div>
            <div class="label" style="font-size:.75rem"><?= date('d/m', strtotime($c['d'])) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
</div>
</body>
</html>