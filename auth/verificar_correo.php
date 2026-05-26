<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$correo = trim($data['correo'] ?? '');
$rol = $data['rol'] ?? '';

if (!$correo || !$rol) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$tabla = $rol === 'profesor' ? 'profesores' : 'alumnos';
$tablaOtra = $rol === 'profesor' ? 'alumnos' : 'profesores';

$stmt = $pdo->prepare("SELECT id FROM $tablaOtra WHERE correo = ?");
$stmt->execute([$correo]);
if ($stmt->fetch()) {
    echo json_encode([
        "success" => false,
        "error" => "Este correo ya está registrado."
    ]);
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM $tabla WHERE correo = ?");
$stmt->execute([$correo]);
$existe = $stmt->fetch();

echo json_encode([
    "success" => true,
    "existe" => $existe ? true : false
]);