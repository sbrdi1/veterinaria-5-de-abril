<?php require __DIR__ . '/_header.php'; ?>
<?php
csrf_guard();
$edit = isset($_GET['edit']) ? (int)$_GET['edit'] : -1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $texto = trim($_POST['texto'] ?? '');
    $estrellas = (int)($_POST['estrellas'] ?? 5);
    $activo = isset($_POST['activo']) ? 1 : 0;

    if ($estrellas < 1) $estrellas = 1;
    if ($estrellas > 5) $estrellas = 5;

    if ($nombre === '' || $texto === '') {
        flash('err', 'El nombre y el texto son obligatorios.');
        header('Location: testimonios.php?edit=' . $id);
        exit;
    }

    if ($id > 0) {
        $stmt = db()->prepare("UPDATE testimonios SET nombre=?, texto=?, estrellas=?, activo=? WHERE id=?");
        $stmt->bind_param('ssiii', $nombre, $texto, $estrellas, $activo, $id);
        $stmt->execute();
        $stmt->close();
        flash('ok', 'Testimonio actualizado correctamente.');
    } else {
        $stmt = db()->prepare("INSERT INTO testimonios (nombre, texto, estrellas, activo) VALUES (?,?,?,?)");
        $stmt->bind_param('ssii', $nombre, $texto, $estrellas, $activo);
        $stmt->execute();
        $stmt->close();
        flash('ok', 'Testimonio creado correctamente.');
    }
    header('Location: testimonios.php');
    exit;
}

if (isset($_GET['delete'])) {
    if (!csrf_ok()) { http_response_code(403); exit('Solicitud inválida (error de seguridad).'); }
    $id = (int)$_GET['delete'];
    $stmt = db()->prepare("DELETE FROM testimonios WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    flash('ok', 'Testimonio eliminado.');
    header('Location: testimonios.php');
    exit;
}

$testimonios = db()->query("SELECT * FROM testimonios ORDER BY activo DESC, created_at DESC")->fetch_all(MYSQLI_ASSOC);
$current = null;
if ($edit > 0) foreach ($testimonios as $t) if ((int)$t['id'] === $edit) { $current = $t; break; }
?>
<h1 class="admin-header">Testimonios</h1>

<?php if ($edit >= 0): ?>
<div class="card-admin" style="max-width:560px">
    <h2 style="font-size:1.1rem; margin-bottom:1rem"><?= $current ? 'Editar Testimonio' : 'Nuevo Testimonio' ?></h2>
    <form method="post" action="testimonios.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $current ? (int)$current['id'] : '0' ?>">
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required value="<?= sanitize($current['nombre'] ?? '') ?>" placeholder="Ej: María José">
        </div>
        <div class="form-group">
            <label>Texto *</label>
            <textarea name="texto" required placeholder="Qué dijo el/la cliente/a sobre el servicio..."><?= sanitize($current['texto'] ?? '') ?></textarea>
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>Estrellas (1-5)</label>
                <input type="number" name="estrellas" min="1" max="5" value="<?= $current ? (int)$current['estrellas'] : '5' ?>">
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <label><input type="checkbox" name="activo" <?= !$current || $current['activo'] ? 'checked' : '' ?>> Visible en el sitio</label>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-save">Guardar</button>
            <a href="testimonios.php" class="btn-cancel">Cancelar</a>
        </div>
    </form>
    <p style="font-size:.8rem; color:var(--muted); margin-top:.8rem">Tip: usa reseñas reales para generar confianza. Los clientes felices suelen aceptar que publiques su comentario.</p>
</div>
<?php endif; ?>

<div class="card-admin">
    <div class="admin-header">
        <h2 style="font-size:1.1rem">Listado (<?= count($testimonios) ?>)</h2>
        <a class="btn-new" href="testimonios.php?edit=0">+ Nuevo Testimonio</a>
    </div>
    <table>
        <tr><th>Nombre</th><th>Estrellas</th><th>Texto</th><th>Estado</th><th>Acciones</th></tr>
        <?php foreach ($testimonios as $t): ?>
        <tr>
            <td><?= sanitize($t['nombre']) ?></td>
            <td><?= str_repeat('★', (int)$t['estrellas']) ?><span style="color:var(--muted)"><?= str_repeat('☆', 5 - (int)$t['estrellas']) ?></span></td>
            <td style="max-width:420px"><?= sanitize(mb_strimwidth($t['texto'], 0, 90, '...')) ?></td>
            <td><?= $t['activo'] ? '<span class="badge">Visible</span>' : '<span style="color:#dc2626;font-size:.8rem">Oculto</span>' ?></td>
            <td class="actions">
                <a class="btn-edit" href="testimonios.php?edit=<?= (int)$t['id'] ?>">Editar</a>
                <a class="btn-del" href="<?= csrf_url('testimonios.php?delete=' . (int)$t['id']) ?>" onclick="return confirm('¿Eliminar este testimonio?')">Eliminar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</div>
</body>
</html>