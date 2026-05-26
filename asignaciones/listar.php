<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$materia_id = intval($_GET['materia_id'] ?? 0);

if (!$materia_id) {
    http_response_code(400);
    echo json_encode(["error" => "materia_id es requerido"]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT a.id, a.titulo, a.descripcion, a.fecha_limite,
           aa.id as archivo_id, aa.nombre_original, aa.nombre_guardado
    FROM asignaciones a
    LEFT JOIN asignacion_archivos aa ON aa.asignacion_id = a.id
    WHERE a.materia_id = ?
    ORDER BY a.fecha_limite ASC
");
$stmt->execute([$materia_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$asignaciones = [];
foreach ($rows as $row) {
    $id = $row['id'];
    if (!isset($asignaciones[$id])) {
        $asignaciones[$id] = [
            'id'          => $row['id'],
            'titulo'      => $row['titulo'],
            'descripcion' => $row['descripcion'],
            'fecha_limite'=> $row['fecha_limite'],
            'archivos'    => []
        ];
    }
    if ($row['nombre_original']) {
        $asignaciones[$id]['archivos'][] = [
            'id'              => $row['archivo_id'],
            'nombre_original' => $row['nombre_original'],
            'nombre_guardado' => $row['nombre_guardado']
        ];
    }
}

echo json_encode(["success" => true, "asignaciones" => array_values($asignaciones)]);