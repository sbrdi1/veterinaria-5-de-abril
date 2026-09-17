<?php require __DIR__ . '/_header.php'; ?>
<?php
csrf_guard();
require_admin();

$edit = isset($_GET['edit']) ? (int)$_GET['edit'] : -1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = ($_POST['rol'] ?? 'editor') === 'admin' ? 'admin' : 'editor';
    $activo = isset($_POST['activo']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    if ($nombre === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('err', 'Nombre y correo válido son obligatorios.');
        header('Location: usuarios.php?edit=' . $id);
        exit;
    }

    // Evitar cederse el rol admin a un editor / cambiar el propio rol
    $self = (int)auth_user()['id'] ?? 0;
    if ($id === $self) $rol = (string)auth_user()['rol'];

    $stmt = db()->prepare("SELECT COUNT(*) FROM usuarios WHERE email=? AND id<>?");
    $stmt->bind_param('si', $email, $id);
    $stmt->execute();
    $dup = (int)$stmt->get_result()->fetch_row()[0];
    $stmt->close();
    if ($dup > 0) {
        flash('err', 'Ya existe un usuario con ese correo.');
        header('Location: usuarios.php?edit=' . $id);
        exit;
    }

    // No permitir desactivar/eliminar el último admin activo
    if (!$activo && $id > 0) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM usuarios WHERE rol='admin' AND activo=1");
        $stmt->execute();
        $admins = (int)$stmt->get_result()->fetch_row()[0];
        $stmt->close();
        $is_self_admin = $id === $self;
        if (($admins <= 1 && $is_self_admin) || $admins <= 1) {
            flash('err', 'No puedes desactivar el último administrador activo.');
            header('Location: usuarios.php?edit=' . $id);
            exit;
        }
    }

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = db()->prepare("UPDATE usuarios SET nombre=?, email=?, rol=?, password=?, activo=? WHERE id=?");
        $stmt->bind_param('ssssii', $nombre, $email, $rol, $hash, $activo, $id);
    } else {
        $stmt = db()->prepare("UPDATE usuarios SET nombre=?, email=?, rol=?, activo=? WHERE id=?");
        $stmt->bind_param('sssii', $nombre, $email, $rol, $activo, $id);
    }
    if ($id > 0) {
        $stmt->execute();
        $stmt->close();
        flash('ok', 'Usuario actualizado.');
    } else {
        if ($password === '') {
            flash('err', 'Debes ingresar una contraseña al crear un usuario.');
            header('Location: usuarios.php?edit=0');
            exit;
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = db()->prepare("INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES (?,?,?,?,?)");
        $stmt->bind_param('ssssi', $nombre, $email, $hash, $rol, $activo);
        $stmt->execute();
        $stmt->close();
        flash('ok', 'Usuario creado.');
    }
    header('Location: usuarios.php');
    exit;
}

if (isset($_GET['delete'])) {
    if (!csrf_ok()) { http_response_code(403); exit('Solicitud inválida (error de seguridad).'); }
    $id = (int)$_GET['delete'];
    $self = isset(auth_user()['id']) ? (int)auth_user()['id'] : 0;
    if ($id === $self) {
        flash('err', 'No puedes eliminarte a ti mismo.');
        header('Location: usuarios.php');
        exit;
    }
    $row = db()->query("SELECT rol, activo FROM usuarios WHERE id=$id")->fetch_assoc();
    if ($row && $row['rol'] === 'admin' && $row['activo']) {
        $admins = (int)db()->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin' AND activo=1")->fetch_row()[0];
        if ($admins <= 1) {
            flash('err', 'No puedes eliminar el último administrador activo.');
            header('Location: usuarios.php');
            exit;
        }
    }
    $stmt = db()->prepare("DELETE FROM usuarios WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    flash('ok', 'Usuario eliminado.');
    header('Location: usuarios.php');
    exit;
}

$usuarios = db()->query("SELECT * FROM usuarios ORDER BY rol DESC, nombre")->fetch_all(MYSQLI_ASSOC);
$current = null;
if ($edit > 0) foreach ($usuarios as $u) if ((int)$u['id'] === $edit) { $current = $u; break; }
$me_id = isset(auth_user()['id']) ? (int)auth_user()['id'] : 0;
?>
<h1 class="admin-header">Usuarios del sistema</h1>
<p style="color:var(--muted); margin-bottom:1.5rem">Solo el rol administrador puede gestionar usuarios. El editor no ve esta página.</p>

<?php if ($edit >= 0): ?>
<div class="card-admin" style="max-width:520px">
    <h2 style="font-size:1.1rem; margin-bottom:1rem"><?= $current ? 'Editar Usuario' : 'Nuevo Usuario' ?></h2>
    <form method="post" action="usuarios.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $current ? (int)$current['id'] : '0' ?>">
        <div class="form-group">
            <label>Nombre *</label>
            <input type="text" name="nombre" required value="<?= sanitize($current['nombre'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Correo electrónico *</label>
            <input type="email" name="email" required value="<?= sanitize($current['email'] ?? '') ?>">
        </div>
        <div class="form-grid">
            <div class="form-group">
                <label>Rol</label>
                <select name="rol" <?= $current && (int)$current['id'] === $me_id ? 'disabled' : '' ?>>
                    <option value="admin" <?= ($current['rol'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    <option value="editor" <?= ($current['rol'] ?? '') === 'editor' ? 'selected' : '' ?>>Editor</option>
                </select>
                <?php if ($current && (int)$current['id'] === $me_id): ?><input type="hidden" name="rol" value="<?= sanitize($current['rol']) ?>"><?php endif; ?>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <label><input type="checkbox" name="activo" <?= !$current || $current['activo'] ? 'checked' : '' ?>> Activo</label>
            </div>
        </div>
        <div class="form-group">
            <label>Contraseña <?= $current ? '(dejar en blanco para no cambiar)' : '*' ?></label>
            <input type="password" name="password" autocomplete="new-password" <?= $current ? '' : 'required' ?>>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-save">Guardar</button>
            <a href="usuarios.php" class="btn-cancel">Cancelar</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card-admin">
    <div class="admin-header">
        <h2 style="font-size:1.1rem">Listado (<?= count($usuarios) ?>)</h2>
        <a class="btn-new" href="usuarios.php?edit=0">+ Nuevo Usuario</a>
    </div>
    <table>
        <tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
        <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= sanitize($u['nombre']) ?><?= (int)$u['id'] === $me_id ? ' (tú)' : '' ?></td>
            <td><?= sanitize($u['email']) ?></td>
            <td><?= $u['rol'] === 'admin' ? '<span class="badge">Admin</span>' : '<span class="btn-view">Editor</span>' ?></td>
            <td><?= $u['activo'] ? '<span style="color:var(--green-dark);font-size:.85rem">Activo</span>' : '<span style="color:#dc2626;font-size:.85rem">Inactivo</span>' ?></td>
            <td class="actions">
                <a class="btn-edit" href="usuarios.php?edit=<?= (int)$u['id'] ?>">Editar</a>
                <?php if ((int)$u['id'] !== $me_id): ?>
                <a class="btn-del" href="<?= csrf_url('usuarios.php?delete=' . (int)$u['id']) ?>" onclick="return confirm('¿Eliminar este usuario?')">Eliminar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
</div>
</body>
</html>