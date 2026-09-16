<?php require __DIR__ . '/_header.php'; ?>
<?php
$services = (int)db()->query("SELECT COUNT(*) FROM servicios")->fetch_row()[0];
$active    = (int)db()->query("SELECT COUNT(*) FROM servicios WHERE activo=1")->fetch_row()[0];
$cats      = (int)db()->query("SELECT COUNT(*) FROM categorias")->fetch_row()[0];
$msgs      = (int)db()->query("SELECT COUNT(*) FROM mensajes WHERE leido=0")->fetch_row()[0];
$recent    = db()->query("SELECT s.*, c.nombre AS cat FROM servicios s LEFT JOIN categorias c ON c.id=s.categoria_id ORDER BY s.id DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
?>
<h1 class="admin-header">Dashboard</h1>
<div class="stat-grid">
    <div class="stat-box"><div class="num"><?= $services ?></div><div class="label">Servicios totales</div></div>
    <div class="stat-box"><div class="num"><?= $active ?></div><div class="label">Servicios activos</div></div>
    <div class="stat-box"><div class="num"><?= $cats ?></div><div class="label">Categorías</div></div>
    <div class="stat-box"><div class="num"><?= $msgs ?></div><div class="label">Consultas sin leer</div></div>
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
</div>
</body>
</html>