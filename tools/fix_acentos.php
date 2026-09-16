<?php
// Corrige los acentos/ñ que se guardaron corruptos en la BD
// Ejecutar con: C:\xampp\php\php.exe tools\fix_acentos.php
$conn = new mysqli('localhost', 'root', '', 'vet5abril');
if ($conn->connect_error) die('DB error: ' . $conn->connect_error);
$conn->set_charset('utf8mb4');

$servicios = [
    ['Consulta General', 'Revisión completa de salud, diagnóstico y orientación veterinaria para tu mascota.'],
    ['Consulta de Control', 'Seguimiento post-tratamiento o control periódico de salud.'],
    ['Vacuna Rabia', 'Vacunación antirrábica obligatoria certificada por SAG.'],
    ['Vacuna Polivalente', 'Vacuna triple o cuádruple según edad y condición de la mascota.'],
    ['Vacuna Leucemia Felina', 'Vacunación contra el virus de leucemia felina (FeLV).'],
    ['Castración Perro', 'Cirugía de esterilización para perros con control del dolor y seguimiento post-operatorio.'],
    ['Castración Gato', 'Cirugía de esterilización felina con monitorización completa.'],
    ['Limpieza Dental', 'Limpieza dental profesional bajo anestesia liviana con ultrasonido.'],
    ['Baño y Peluquería', 'Baño completo, corte de uñas, limpieza de oídos y peluquería estética.'],
    ['Corte de Uñas', 'Corte de uñas y limpieza básica de almohadillas.'],
    ['Análisis de Sangre', 'Examen completo de sangre con resultados en 24 horas.'],
    ['Análisis de Orina', 'Examen completo de orina para diagnóstico.'],
    ['Radiografía Digital', 'Radiografía digital de alta resolución.'],
    ['Ecografía Abdominal', 'Ecografía completa del abdomen con imágenes digitales.'],
    ['Atención Urgencia 24h', 'Atención veterinaria de emergencia las 24 horas del día.'],
    ['Desparasitación Interna', 'Desparasitación interna según peso y tipo de mascota.'],
    ['Desparasitación Externa', 'Tratamiento antipulgas, garrapatas y parásitos externos.'],
    ['Consulta Cardiología', 'Especialista en cardiología veterinaria. ECG y ecocardiograma.'],
    ['Consulta Dermatología', 'Especialista en dermatología. Tratamiento de alergias, hongos y parásitos cutáneos.'],
    ['Consulta Oftalmología', 'Especialista en oftalmología. Glaucoma, cataratas, conjuntivitis.'],
];

$categorias = [
    'Consultas', 'Vacunación', 'Cirugía', 'Estética y Baño', 'Laboratorio',
    'Emergencias', 'Especialistas', 'Desparasitación',
];

$ok = 0;
// Actualizar servicios por id (orden de inserción = id 1..20)
foreach ($servicios as $i => [$nombre, $desc]) {
    $id = $i + 1;
    $stmt = $conn->prepare('UPDATE servicios SET nombre=?, descripcion=? WHERE id=?');
    $stmt->bind_param('ssi', $nombre, $desc, $id);
    $stmt->execute();
    $ok += $stmt->affected_rows;
    $stmt->close();
}

foreach ($categorias as $i => $nombre) {
    $id = $i + 1;
    $stmt = $conn->prepare('UPDATE categorias SET nombre=? WHERE id=?');
    $stmt->bind_param('si', $nombre, $id);
    $stmt->execute();
    $ok += $stmt->affected_rows;
    $stmt->close();
}

$stmt = $conn->prepare("UPDATE usuarios SET nombre=? WHERE rol='admin'");
$mama = 'Mamá (admin)';
$stmt->bind_param('s', $mama);
$stmt->execute();
$ok += $stmt->affected_rows;
$stmt->close();

echo "Registros actualizados: $ok\n";

// Verificación: contar cuántos tienen "?" negativo
$bad = $conn->query("SELECT COUNT(*) AS c FROM servicios WHERE nombre LIKE '%?%' OR descripcion LIKE '%?%'")->fetch_assoc()['c'];
$bad += $conn->query("SELECT COUNT(*) AS c FROM categorias WHERE nombre LIKE '%?%'")->fetch_assoc()['c'];
$bad += $conn->query("SELECT COUNT(*) AS c FROM usuarios WHERE nombre LIKE '%?%'")->fetch_assoc()['c'];
echo "Registros aún con '?': $bad\n";