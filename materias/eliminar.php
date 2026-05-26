<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);
$profesor_id = intval($data['profesor_id'] ?? 0);

if (!$id || !$profesor_id) {
    http_response_code(400);
    echo json_encode(["error" => "ID requerido"]);
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM materias WHERE id = ? AND profesor_id = ?");
$stmt->execute([$id, $profesor_id]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(["error" => "No autorizado"]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT aa.nombre_guardado 
    FROM asignacion_archivos aa
    INNER JOIN asignaciones a ON aa.asignacion_id = a.id
    WHERE a.materia_id = ?
");
$stmt->execute([$id]);
$archivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($archivos as $archivo) {
    $ruta = $_SERVER['DOCUMENT_ROOT'] . '/educlass/uploads/asignaciones/' . $archivo['nombre_guardado'];
    if (file_exists($ruta)) unlink($ruta);
}

$pdo->prepare("DELETE aa FROM asignacion_archivos aa INNER JOIN asignaciones a ON aa.asignacion_id = a.id WHERE a.materia_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM asignaciones WHERE materia_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM materias WHERE id = ?")->execute([$id]);

echo json_encode(["success" => true]);