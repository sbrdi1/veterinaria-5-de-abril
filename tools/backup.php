<?php
/**
 * Respaldo de la base de datos (solo para el rol admin).
 * Genera un dump SQL con mysqldump en /backups y permite descargarlos o borrarlos.
 */
require_once __DIR__ . '/../config.php';
auth_require();
require_admin();

$backup_dir = __DIR__ . '/../backups';
if (!is_dir($backup_dir)) @mkdir($backup_dir, 0777, true);

function mysqldump_bin(): ?string {
    $candidates = [
        'C:\\xampp\\mysql\\bin\\mysqldump.exe',
        '/usr/bin/mysqldump',
        '/opt/homebrew/bin/mysqldump',
        '/usr/local/bin/mysqldump',
    ];
    foreach ($candidates as $c) {
        if (is_file($c)) return $c;
    }
    $which = @shell_exec('command -v mysqldump 2>/dev/null');
    return $which ? trim($which) : null;
}

function list_backups(string $dir): array {
    $out = [];
    foreach (glob($dir . '/*.sql*') ?: [] as $f) {
        $out[] = ['name' => basename($f), 'size' => filesize($f), 'time' => filemtime($f)];
    }
    usort($out, fn($a, $b) => $b['time'] <=> $a['time']);
    return $out;
}

$action = $_GET['action'] ?? '';
if ($action === 'crear') {
    if (!csrf_ok()) {
        http_response_code(403);
        exit('Token de seguridad inválido.');
    }
    $bin = mysqldump_bin();
    if (!$bin) {
        flash('err', 'No se encontró mysqldump. Ajusta la ruta en tools/backup.php.');
        header('Location: ' . SITE_URL . '/tools/backup.php');
        exit;
    }
    $file = $backup_dir . '/vet5abril_' . date('Ymd_His') . '.sql';
    $cmd = escapeshellarg($bin)
        . ' --host=' . escapeshellarg(DB_HOST)
        . ' --user=' . escapeshellarg(DB_USER)
        . (DB_PASS !== '' ? ' --password=' . escapeshellarg(DB_PASS) : ' --password=')
        . ' --default-character-set=utf8mb4 --single-transaction --routines --triggers '
        . escapeshellarg(DB_NAME) . ' > ' . escapeshellarg($file) . ' 2>' . escapeshellarg($file . '.err');
    exec($cmd);
    if (is_file($file) && filesize($file) > 0) {
        @unlink($file . '.err');
        flash('ok', 'Respaldo creado: ' . basename($file));
    } else {
        $err = is_file($file . '.err') ? file_get_contents($file . '.err') : 'sin detalles';
        @unlink($file);
        @unlink($file . '.err');
        flash('err', 'Error al crear el respaldo: ' . trim($err));
    }
    header('Location: ' . SITE_URL . '/tools/backup.php');
    exit;
}

if ($action === 'borrar' && isset($_GET['file'])) {
    if (!csrf_ok()) {
        http_response_code(403);
        exit('Token de seguridad inválido.');
    }
    $name = basename($_GET['file']);
    if (preg_match('/^vet5abril_\d{8}_\d{6}\.sql$/', $name)) {
        @unlink($backup_dir . '/' . $name);
        @unlink($backup_dir . '/' . $name . '.err');
        flash('ok', 'Respaldo eliminado: ' . $name);
    }
    header('Location: ' . SITE_URL . '/tools/backup.php');
    exit;
}

if ($action === 'descargar' && isset($_GET['file'])) {
    if (!csrf_ok()) {
        http_response_code(403);
        exit('Token de seguridad inválido.');
    }
    $name = basename($_GET['file']);
    $path = $backup_dir . '/' . $name;
    if (preg_match('/^vet5abril_\d{8}_\d{6}\.sql$/', $name) && is_file($path)) {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
    header('Location: ' . SITE_URL . '/tools/backup.php');
    exit;
}

$backups = list_backups($backup_dir);
$bin = mysqldump_bin();
$page_title = 'Respaldo';
include __DIR__ . '/../admin/_header.php';
?>
<h1>Respaldo de la base de datos</h1>
<p style="color:var(--muted)">Genera una copia de seguridad de la base <strong><?= SITE_NAME ?></strong> y descárgala a tu computador. Los respaldos se guardan en la carpeta <code>backups/</code> del servidor (no se suben a GitHub).</p>

<?php if (!$bin): ?>
    <div class="flash flash-err">No se pudo localizar <code>mysqldump</code>. Revisa la ruta en <code>tools/backup.php</code>.</div>
<?php else: ?>
    <a class="btn-wa-sm" style="text-decoration:none" href="<?= csrf_url('backup.php?action=crear') ?>">Crear respaldo ahora</a>
<?php endif; ?>

<h2 style="margin-top:2rem">Respaldos guardados (<?= count($backups) ?>)</h2>
<?php if (empty($backups)): ?>
    <p style="color:var(--muted)">Todavía no hay respaldos.</p>
<?php else: ?>
    <table class="tbl">
        <thead><tr><th>Archivo</th><th>Tamaño</th><th>Fecha</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($backups as $b): ?>
            <tr>
                <td><code><?= sanitize($b['name']) ?></code></td>
                <td><?= number_format($b['size'] / 1024, 1, ',', '.') ?> KB</td>
                <td><?= date('d/m/Y H:i', $b['time']) ?></td>
                <td style="white-space:nowrap">
                    <a href="<?= csrf_url('backup.php?action=descargar&file=' . urlencode($b['name'])) ?>">Descargar</a>
                    &nbsp;·&nbsp;
                    <a href="<?= csrf_url('backup.php?action=borrar&file=' . urlencode($b['name'])) ?>" onclick="return confirm('¿Eliminar este respaldo?')" style="color:#dc2626">Borrar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</div>
</body>
</html>