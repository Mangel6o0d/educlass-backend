<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$alumno_id = intval($_GET['alumno_id'] ?? 0);

if (!$alumno_id) {
    http_response_code(400);
    echo json_encode(["error" => "ID requerido"]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT m.id, m.nombre, m.carrera, m.hora, m.codigo
    FROM materias m
    INNER JOIN alumno_materias am ON m.id = am.materia_id
    WHERE am.alumno_id = ?
    ORDER BY am.fecha_union DESC
");
$stmt->execute([$alumno_id]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["success" => true, "materias" => $materias]);