<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$profesor_id = intval($_GET['profesor_id'] ?? 0);

if (!$profesor_id) {
    http_response_code(400);
    echo json_encode(["error" => "profesor_id es requerido"]);
    exit();
}

$stmt = $pdo->prepare("SELECT id, nombre, carrera, hora, codigo FROM materias WHERE profesor_id = ?");
$stmt->execute([$profesor_id]);
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["success" => true, "materias" => $materias]);