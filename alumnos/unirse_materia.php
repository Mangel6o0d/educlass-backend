<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$alumno_id = intval($data['alumno_id'] ?? 0);
$codigo    = trim($data['codigo'] ?? '');

if (!$alumno_id || !$codigo) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

// Buscar materia por código
$stmt = $pdo->prepare("SELECT id, nombre, carrera, hora, codigo FROM materias WHERE codigo = ?");
$stmt->execute([$codigo]);
$materia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$materia) {
    http_response_code(404);
    echo json_encode(["error" => "Código de clase no encontrado"]);
    exit();
}

// Verificar si ya está inscrito
$stmt = $pdo->prepare("SELECT id FROM alumno_materias WHERE alumno_id = ? AND materia_id = ?");
$stmt->execute([$alumno_id, $materia['id']]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(["error" => "Ya estás inscrito en esta clase"]);
    exit();
}

$stmt = $pdo->prepare("INSERT INTO alumno_materias (alumno_id, materia_id) VALUES (?, ?)");
$stmt->execute([$alumno_id, $materia['id']]);

echo json_encode(["success" => true, "materia" => $materia]);