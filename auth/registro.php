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
$nombre = trim($data['nombre'] ?? '');
$rol = $data['rol'] ?? '';

if (!$correo || !$password || !$rol) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$tabla = $rol === 'profesor' ? 'profesores' : 'alumnos';
$tablaOtra = $rol === 'profesor' ? 'alumnos' : 'profesores';

$stmt = $pdo->prepare("SELECT id FROM $tablaOtra WHERE correo = ?");
$stmt->execute([$correo]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(["error" => "Este correo ya está registrado como " . ($rol === 'profesor' ? 'alumno' : 'profesor')]);
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM $tabla WHERE correo = ?");
$stmt->execute([$correo]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(["error" => "Este correo ya está registrado"]);
    exit();
}

$hash = password_hash($password, PASSWORD_DEFAULT);

if ($rol === 'profesor') {
    $stmt = $pdo->prepare("INSERT INTO profesores (nombre, correo, password) VALUES (?, ?, ?)");
    $stmt->execute([$nombre ?: $correo, $correo, $hash]);
} else {
    $stmt = $pdo->prepare("INSERT INTO alumnos (nombre, correo, password) VALUES (?, ?, ?)");
    $stmt->execute([$nombre ?: $correo, $correo, $hash]);
}

$id = $pdo->lastInsertId();

echo json_encode([
    "success" => true,
    "rol" => $rol,
    "id" => $id,
    "nombre" => $nombre ?: $correo,
    "correo" => $correo
]);