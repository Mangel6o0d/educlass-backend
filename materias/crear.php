<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['nombre']) || empty($data['carrera']) || empty($data['hora']) || empty($data['profesor_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Todos los campos son requeridos"]);
    exit();
}

function generarCodigo($pdo) {
    $intentos = 0;
    while ($intentos < 10) {
        $codigo = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM materias WHERE codigo = ?");
        $stmt->execute([$codigo]);
        if ($stmt->fetchColumn() == 0) return $codigo;
        $intentos++;
    }
    return strtoupper(uniqid());
}

$codigo      = generarCodigo($pdo);
$nombre      = trim($data['nombre']);
$carrera     = trim($data['carrera']);
$hora        = trim($data['hora']);
$profesor_id = intval($data['profesor_id']);

$stmt = $pdo->prepare("INSERT INTO materias (nombre, carrera, hora, codigo, profesor_id) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$nombre, $carrera, $hora, $codigo, $profesor_id]);

echo json_encode([
    "success" => true,
    "materia" => [
        "id"      => $pdo->lastInsertId(),
        "nombre"  => $nombre,
        "carrera" => $carrera,
        "hora"    => $hora,
        "codigo"  => $codigo
    ]
]);