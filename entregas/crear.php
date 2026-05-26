<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$alumno_nombre = trim($_POST['alumno_nombre'] ?? '');
$alumno_id     = intval($_POST['alumno_id'] ?? 0);
$asignacion_id = intval($_POST['asignacion_id'] ?? 0);

if (!$alumno_nombre || !$alumno_id || !$asignacion_id) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM entregas WHERE asignacion_id = ? AND alumno_id = ?");
$stmt->execute([$asignacion_id, $alumno_id]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(["error" => "Ya entregaste esta asignación"]);
    exit();
}

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["error" => "Archivo requerido"]);
    exit();
}

$archivo = $_FILES['archivo'];
$maxSize = 5 * 1024 * 1024;
if ($archivo['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(["error" => "El archivo supera los 5MB"]);
    exit();
}

$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$permitidos = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
if (!in_array($ext, $permitidos)) {
    http_response_code(400);
    echo json_encode(["error" => "Tipo de archivo no permitido"]);
    exit();
}

$nombreGuardado = 'entrega_' . uniqid() . '.' . $ext;
$destino = __DIR__ . '/../../uploads/asignaciones/' . $nombreGuardado;

if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
    http_response_code(500);
    echo json_encode(["error" => "Error al guardar el archivo"]);
    exit();
}

$stmt = $pdo->prepare("
    INSERT INTO entregas (alumno_nombre, alumno_id, archivo, estado, asignacion_id, fecha_entrega)
    VALUES (?, ?, ?, 'pendiente', ?, NOW())
");
$stmt->execute([$alumno_nombre, $alumno_id, $nombreGuardado, $asignacion_id]);

echo json_encode(["success" => true]);