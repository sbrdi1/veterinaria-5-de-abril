<?php require __DIR__ . '/_header.php'; ?>
<?php
csrf_guard();
if (isset($_GET['export'])) {
    if (!csrf_ok()) { http_response_code(403); exit('error'); }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="chat_logs.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Mensaje', 'Respuesta', 'Modo', 'Creado']);
    foreach (db()->query("SELECT * FROM chat_logs ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC) as $l) {
        fputcsv($out, [$l['id'], $l['mensaje'], $l['respuesta'], $l['modo'], $l['created_at']]);
    }
    fclose($out);
    exit;
}
if (isset($_GET['delete'])) {
    if (!csrf_ok()) { http_response_code(403); exit('Solicitud inválida (error de seguridad).'); }
    $id = (int)$_GET['delete'];
    $stmt = db()->prepare("DELETE FROM chat_logs WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    flash('ok', 'Consulta eliminada.');
    header('Location: chat_logs.php');
    exit;
}
if (isset($_GET['clear'])) {
    if (!csrf_ok()) { http_response_code(403); exit('Solicitud inválida (error de seguridad).'); }
    db()->query("DELETE FROM chat_logs");
    flash('ok', 'Historial del asistente limpiado.');
    header('Location: chat_logs.php');
    exit;
}

$logs = db()->query("SELECT * FROM chat_logs ORDER BY id DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
?>
<h1 class="admin-header">Historial del asistente virtual</h1>
<p style="color:var(--muted); margin-bottom:1.5rem">Estas son las preguntas que hacen los visitantes del sitio. Te sirven para saber qué consulta la gente y mejorar los servicios.</p>

<div class="card-admin">
    <div class="admin-header">
        <h2 style="font-size:1.1rem">Conversaciones (<?= count($logs) ?>)</h2>
        <div style="display:flex; gap:.5rem">
            <a class="btn-view" href="<?= csrf_url('chat_logs.php?export=1') ?>">Exportar CSV</a>
            <a class="btn-del" href="<?= csrf_url('chat_logs.php?clear=1') ?>" onclick="return confirm('¿Borrar TODO el historial?')">Vaciar historial</a>
        </div>
    </div>
    <?php if (empty($logs)): ?>
        <p style="color:var(--muted); font-size:.9rem">Aún no hay consultas registradas. Cuando alguien use el asistente del sitio, aparecerán aquí.</p>
    <?php else: ?>
        <?php foreach ($logs as $l): ?>
        <div class="msg-card" style="margin-bottom:1rem">
            <div class="msg-meta">
                <span style="font-weight:600; color:var(--primary-deep)">#<?= (int)$l['id'] ?> · <?= $l['modo'] === 'gemini' ? 'IA (Gemini)' : 'FAQ automático' ?></span>
                <a href="<?= csrf_url('chat_logs.php?delete=' . (int)$l['id']) ?>" class="btn-del" onclick="return confirm('¿Eliminar esta consulta?')">Eliminar</a>
            </div>
            <div class="msg-text" style="margin-bottom:.6rem">
                <strong>Cliente:</strong><br><?= sanitize($l['mensaje']) ?>
            </div>
            <div class="msg-text" style="color:var(--muted)">
                <strong>Respuesta:</strong><br><?= nl2br(sanitize($l['respuesta'])) ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</div>
</body>
</html>