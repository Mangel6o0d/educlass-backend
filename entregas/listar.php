<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$asignacion_id = intval($_GET['asignacion_id'] ?? 0);

if (!$asignacion_id) {
    http_response_code(400);
    echo json_encode(["error" => "asignacion_id es requerido"]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT id, alumno_nombre, archivo, estado, calificacion, fecha_entrega
    FROM entregas
    WHERE asignacion_id = ?
    ORDER BY fecha_entrega DESC
");
$stmt->execute([$asignacion_id]);
$entregas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["success" => true, "entregas" => $entregas]);