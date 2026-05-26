<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$correo = trim($data['correo'] ?? '');
$password = $data['password'] ?? '';
$rol = $data['rol'] ?? '';

if (!$correo || !$password || !$rol) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$tabla = $rol === 'profesor' ? 'profesores' : 'alumnos';

$stmt = $pdo->prepare("SELECT * FROM $tabla WHERE correo = ?");
$stmt->execute([$correo]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || !password_verify($password, $usuario['password'])) {
    http_response_code(401);
    echo json_encode(["error" => "Correo o contraseña incorrectos"]);
    exit();
}

echo json_encode([
    "success" => true,
    "rol" => $rol,
    "id" => $usuario['id'],
    "nombre" => $usuario['nombre'] ?? $usuario['correo'],
    "correo" => $usuario['correo']
]);