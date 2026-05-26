<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$alumno_id  = intval($_GET['alumno_id'] ?? 0);
$materia_id = intval($_GET['materia_id'] ?? 0);

if (!$alumno_id || !$materia_id) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT e.asignacion_id, e.estado, e.calificacion
    FROM entregas e
    INNER JOIN asignaciones a ON e.asignacion_id = a.id
    WHERE a.materia_id = ? AND e.alumno_id = ?
");
$stmt->execute([$materia_id, $alumno_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$entregas = [];
foreach ($rows as $r) {
    $entregas[$r['asignacion_id']] = [
        'estado'       => $r['estado'],
        'calificacion' => $r['calificacion']
    ];
}

echo json_encode(["success" => true, "entregas" => $entregas]);