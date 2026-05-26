<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$id          = intval($data['id'] ?? 0);
$profesor_id = intval($data['profesor_id'] ?? 0);

if (!$id || !$profesor_id) {
    http_response_code(400);
    echo json_encode(["error" => "ID requerido"]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT a.id FROM asignaciones a
    INNER JOIN materias m ON a.materia_id = m.id
    WHERE a.id = ? AND m.profesor_id = ?
");
$stmt->execute([$id, $profesor_id]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(["error" => "No autorizado"]);
    exit();
}

$stmt = $pdo->prepare("SELECT nombre_guardado FROM asignacion_archivos WHERE asignacion_id = ?");
$stmt->execute([$id]);
$archivos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($archivos as $archivo) {
    $ruta = $_SERVER['DOCUMENT_ROOT'] . '/educlass/uploads/asignaciones/' . $archivo['nombre_guardado'];
    if (file_exists($ruta)) unlink($ruta);
}

$pdo->prepare("DELETE FROM asignacion_archivos WHERE asignacion_id = ?")->execute([$id]);
$pdo->prepare("DELETE FROM asignaciones WHERE id = ?")->execute([$id]);

echo json_encode(["success" => true]);