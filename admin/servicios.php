<?php require __DIR__ . '/_header.php'; ?>
<?php
$edit = isset($_GET['edit']) ? (int)$_GET['edit'] : -1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = (int)($_POST['id'] ?? 0);
    $nombre   = trim($_POST['nombre'] ?? '');
    $precio   = (int)preg_replace('/[^0-9]/', '', $_POST['precio'] ?? '0');
    $desc     = trim($_POST['descripcion'] ?? '');
    $duracion = trim($_POST['duracion'] ?? '');
    $cat_id   = (int)($_POST['categoria_id'] ?? 0) ?: null;
    $dest     = isset($_POST['destacado']) ? 1 : 0;
    $activo   = isset($_POST['activo']) ? 1 : 0;
    $keep_img = $_POST['keep_img'] ?? '';

    if ($nombre === '') {
        flash('err', 'El nombre del servicio es obligatorio.');
        header('Location: servicios.php?edit=' . $id);
        exit;
    }

    $img = $keep_img;
    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['imagen']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
        if (!in_array($ext, $allowed)) {
            flash('err', 'Formato de imagen no permitido (usa JPG, PNG, WebP o AVIF).');
            header('Location: servicios.php?edit=' . $id);
            exit;
        }
        if ($_FILES['imagen']['size'] > 5 * 1024 * 1024) {
            flash('err', 'La imagen supera los 5MB.');
            header('Location: servicios.php?edit=' . $id);
            exit;
        }
        $new_name = 'svc_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
        if (move_uploaded_file($tmp, SERVICIOS_IMG_DIR . $new_name)) {
            if ($keep_img && file_exists(SERVICIOS_IMG_DIR . $keep_img)) @unlink(SERVICIOS_IMG_DIR . $keep_img);
            $img = $new_name;
        }
    }

    if ($id > 0) {
        $stmt = db()->prepare("UPDATE servicios SET nombre=?, descripcion=?, precio=?, duracion=?, imagen=?, categoria_id=?, destacado=?, activo=? WHERE id=?");
        $stmt->bind_param('ssissiiii', $nombre, $desc, $precio, $duracion, $img, $cat_id, $dest, $activo, $id);
        $stmt->execute();
        $stmt->close();
        flash('ok', 'Servicio actualizado correctamente.');
    } else {
        $stmt = db()->prepare("INSERT INTO servicios (nombre, descripcion, precio, duracion, imagen, categoria_id, destacado, activo) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssissiii', $nombre, $desc, $precio, $duracion, $img, $cat_id, $dest, $activo);
        $stmt->execute();
        $id = db()->insert_id;
        $stmt->close();
        flash('ok', 'Servicio creado correctamente.');
    }
    header('Location: servicios.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $row = db()->query("SELECT imagen FROM servicios WHERE id=$id")->fetch_assoc();
    $stmt = db()->prepare("DELETE FROM servicios WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    if ($row && $row['imagen'] && file_exists(SERVICIOS_IMG_DIR . $row['imagen'])) @unlink(SERVICIOS_IMG_DIR . $row['imagen']);
    flash('ok', 'Servicio eliminado.');
    header('Location: servicios.php');
    exit;
}

$cats = db()->query("SELECT * FROM categorias ORDER BY orden, nombre")->fetch_all(MYSQLI_ASSOC);
$servs = db()->query("SELECT s.*, c.nombre AS cat FROM servicios s LEFT JOIN categorias c ON c.id=s.categoria_id ORDER BY s.id DESC")->fetch_all(MYSQLI_ASSOC);

$current = null;
if ($edit > 0) {
    foreach ($servs as $s) if ((int)$s['id'] === $edit) { $current = $s; break; }
}
?>
<h1 class="admin-header">Servicios</h1>

<?php if ($edit >= 0): ?>
<div class="card-admin">
    <h2 style="font-size:1.1rem; margin-bottom:1rem"><?= $current ? 'Editar Servicio' : 'Nuevo Servicio' ?></h2>
    <form method="post" action="servicios.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $current ? (int)$current['id'] : '0' ?>">
        <input type="hidden" name="keep_img" value="<?= sanitize($current['imagen'] ?? '') ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nombre" required value="<?= sanitize($current['nombre'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Precio (CLP)</label>
                <input type="text" name="precio" value="<?= $current ? number_format((int)$current['precio']) : '' ?>" placeholder="15.000">
            </div>
            <div class="form-group">
                <label>Duración</label>
                <input type="text" name="duracion" value="<?= sanitize($current['duracion'] ?? '') ?>" placeholder="ej: 30 min">
            </div>
            <div class="form-group">
                <label>Categoría</label>
                <select name="categoria_id">
                    <option value="">— Sin categoría —</option>
                    <?php foreach ($cats as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $current && (int)$current['categoria_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= sanitize($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group form-full">
                <label>Descripción</label>
                <textarea name="descripcion"><?= sanitize($current['descripcion'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Imagen del Servicio (JPG, PNG, WebP, AVIF - máx 5MB)</label>
                <?php if (!empty($current['imagen'])): ?>
                <div style="display:flex; align-items:center; gap:1rem; margin-bottom:.5rem">
                    <img src="<?= SITE_URL ?>/uploads/servicios/<?= sanitize($current['imagen']) ?>" alt="" style="width:70px;height:70px;object-fit:cover;border-radius:8px">
                    <span style="font-size:.8rem;color:var(--muted)"><?= sanitize($current['imagen']) ?></span>
                </div>
                <?php endif; ?>
                <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp,image/avif">
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <label><input type="checkbox" name="destacado" <?= $current && $current['destacado'] ? 'checked' : '' ?>> Destacado</label><br>
                <label><input type="checkbox" name="activo" <?= !$current || $current['activo'] ? 'checked' : '' ?>> Activo</label>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-save">Guardar</button>
            <a href="servicios.php" class="btn-cancel">Cancelar</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card-admin">
    <div class="admin-header">
        <h2 style="font-size:1.1rem">Listado de servicios (<?= count($servs) ?>)</h2>
        <a class="btn-new" href="servicios.php?edit=0">+ Nuevo Servicio</a>
    </div>
    <table>
        <tr><th>Imagen</th><th>Servicio</th><th>Categoría</th><th>Precio</th><th>Destacado</th><th>Estado</th><th>Acciones</th></tr>
        <?php foreach ($servs as $s): ?>
        <tr>
            <td>
                <?php if (!empty($s['imagen'])): ?>
                    <img src="<?= SITE_URL ?>/uploads/servicios/<?= sanitize($s['imagen']) ?>" alt="">
                <?php else: ?>
                    <div style="width:48px;height:48px;background:#e2e8f0;border-radius:8px;display:flex;align-items:center;justify-content:center">🐾</div>
                <?php endif; ?>
            </td>
            <td><?= sanitize($s['nombre']) ?></td>
            <td><?= sanitize($s['cat'] ?? '—') ?></td>
            <td><?= format_price((int)$s['precio']) ?></td>
            <td><?= $s['destacado'] ? '⭐' : '—' ?></td>
            <td><?= $s['activo'] ? '<span class="badge">Activo</span>' : '<span style="color:#dc2626;font-size:.8rem">Inactivo</span>' ?></td>
            <td class="actions">
                <a class="btn-edit" href="servicios.php?edit=<?= (int)$s['id'] ?>">Editar</a>
                <a class="btn-del" href="servicios.php?delete=<?= (int)$s['id'] ?>" onclick="return confirm('¿Eliminar este servicio?')">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</div>
</body>
</html>