<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$id           = intval($data['id'] ?? 0);
$calificacion = intval($data['calificacion'] ?? 0);

if (!$id || $calificacion < 1 || $calificacion > 100) {
    http_response_code(400);
    echo json_encode(["error" => "Datos inválidos"]);
    exit();
}

$stmt = $pdo->prepare("UPDATE entregas SET calificacion = ?, estado = 'revisado' WHERE id = ?");
$stmt->execute([$calificacion, $id]);

echo json_encode(["success" => true]);