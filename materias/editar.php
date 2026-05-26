<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$id          = intval($data['id'] ?? 0);
$profesor_id = intval($data['profesor_id'] ?? 0);
$nombre      = trim($data['nombre'] ?? '');
$carrera     = trim($data['carrera'] ?? '');
$hora        = trim($data['hora'] ?? '');

if (!$id || !$profesor_id || !$nombre || !$carrera || !$hora) {
    http_response_code(400);
    echo json_encode(["error" => "Todos los campos son requeridos"]);
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM materias WHERE id = ? AND profesor_id = ?");
$stmt->execute([$id, $profesor_id]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(["error" => "No autorizado"]);
    exit();
}

$stmt = $pdo->prepare("UPDATE materias SET nombre = ?, carrera = ?, hora = ? WHERE id = ?");
$stmt->execute([$nombre, $carrera, $hora, $id]);

echo json_encode(["success" => true]);