<?php require __DIR__ . '/_header.php'; ?>
<?php
csrf_guard();
$edit = isset($_GET['edit']) ? (int)$_GET['edit'] : -1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $orden = (int)$_POST['orden'] ?? 0;
    $activa = isset($_POST['activa']) ? 1 : 0;
    $keep_img = $_POST['keep_img'] ?? '';

    if ($nombre === '') {
        flash('err', 'El nombre de la categoría es obligatorio.');
        header('Location: categorias.php?edit=' . $id);
        exit;
    }

    $img = $keep_img;
    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['imagen']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
        if (in_array($ext, $allowed) && $_FILES['imagen']['size'] <= 5 * 1024 * 1024) {
            $new_name = 'cat_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
            if (move_uploaded_file($tmp, CATEGORIAS_IMG_DIR . $new_name)) {
                if ($keep_img && file_exists(CATEGORIAS_IMG_DIR . $keep_img)) @unlink(CATEGORIAS_IMG_DIR . $keep_img);
                $img = $new_name;
            }
        } else {
            flash('err', 'Imagen inválida (usa JPG, PNG, WebP o AVIF, máx 5MB).');
            header('Location: categorias.php?edit=' . $id);
            exit;
        }
    }

    if ($id > 0) {
        $stmt = db()->prepare("UPDATE categorias SET nombre=?, orden=?, imagen=?, activa=? WHERE id=?");
        $stmt->bind_param('sissi', $nombre, $orden, $img, $activa, $id);
        $stmt->execute();
        $stmt->close();
        flash('ok', 'Categoría actualizada correctamente.');
    } else {
        $stmt = db()->prepare("INSERT INTO categorias (nombre, orden, imagen, activa) VALUES (?,?,?,?)");
        $stmt->bind_param('siss', $nombre, $orden, $img, $activa);
        $stmt->execute();
        $stmt->close();
        flash('ok', 'Categoría creada correctamente.');
    }
    header('Location: categorias.php');
    exit;
}

if (isset($_GET['delete'])) {
    if (!csrf_ok()) { http_response_code(403); exit('Solicitud inválida (error de seguridad).'); }
    $id = (int)$_GET['delete'];
    $count = (int)db()->query("SELECT COUNT(*) FROM servicios WHERE categoria_id=$id")->fetch_row()[0];
    if ($count > 0) {
        flash('err', 'No se puede eliminar la categoría: tiene ' . $count . ' servicios asignados.');
        header('Location: categorias.php');
        exit;
    }
    $row = db()->query("SELECT imagen FROM categorias WHERE id=$id")->fetch_assoc();
    $stmt = db()->prepare("DELETE FROM categorias WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    if ($row && $row['imagen'] && file_exists(CATEGORIAS_IMG_DIR . $row['imagen'])) @unlink(CATEGORIAS_IMG_DIR . $row['imagen']);
    flash('ok', 'Categoría eliminada.');
    header('Location: categorias.php');
    exit;
}

$cats = db()->query("SELECT c.*, (SELECT COUNT(*) FROM servicios s WHERE s.categoria_id=c.id) AS total FROM categorias c ORDER BY c.orden, c.nombre")->fetch_all(MYSQLI_ASSOC);
$current = null;
if ($edit > 0) foreach ($cats as $c) if ((int)$c['id'] === $edit) { $current = $c; break; }
?>
<h1 class="admin-header">Categorías</h1>

<?php if ($edit >= 0): ?>
<div class="card-admin" style="max-width:520px">
    <h2 style="font-size:1.1rem; margin-bottom:1rem"><?= $current ? 'Editar Categoría' : 'Nueva Categoría' ?></h2>
    <form method="post" action="categorias.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $current ? (int)$current['id'] : '0' ?>">
        <input type="hidden" name="keep_img" value="<?= sanitize($current['imagen'] ?? '') ?>">
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required value="<?= sanitize($current['nombre'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Orden</label>
            <input type="number" name="orden" value="<?= $current ? (int)$current['orden'] : '0' ?>">
        </div>
        <div class="form-group">
            <label>Imagen (JPG, PNG, WebP, AVIF - máx 5MB)</label>
            <?php if (!empty($current['imagen'])): ?>
            <div style="margin-bottom:.5rem">
                <img src="<?= SITE_URL ?>/uploads/categorias/<?= sanitize($current['imagen']) ?>" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:8px">
            </div>
            <?php endif; ?>
            <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp,image/avif">
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="activa" <?= !$current || $current['activa'] ? 'checked' : '' ?>> Activa</label>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-save">Guardar</button>
            <a href="categorias.php" class="btn-cancel">Cancelar</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card-admin" style="max-width:620px">
    <div class="admin-header">
        <h2 style="font-size:1.1rem">Listado (<?= count($cats) ?>)</h2>
        <a class="btn-new" href="categorias.php?edit=0">+ Nueva Categoría</a>
    </div>
    <table>
        <tr><th>Nombre</th><th>Orden</th><th>Servicios</th><th>Estado</th><th>Acciones</th></tr>
        <?php foreach ($cats as $c): ?>
        <tr>
            <td><?= sanitize($c['nombre']) ?></td>
            <td><?= (int)$c['orden'] ?></td>
            <td><?= (int)$c['total'] ?></td>
            <td><?= $c['activa'] ? '<span class="badge">Activa</span>' : '<span style="color:#dc2626;font-size:.8rem">Inactiva</span>' ?></td>
            <td class="actions">
                <a class="btn-edit" href="categorias.php?edit=<?= (int)$c['id'] ?>">Editar</a>
                <a class="btn-del" href="<?= csrf_url('categorias.php?delete=' . (int)$c['id']) ?>" onclick="return confirm('¿Eliminar esta categoría?')">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</div>
</body>
</html>