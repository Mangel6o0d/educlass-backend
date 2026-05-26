<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$titulo       = trim($_POST['titulo'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');
$fecha_limite = trim($_POST['fecha_limite'] ?? '');
$materia_id   = intval($_POST['materia_id'] ?? 0);

if (!$titulo || !$descripcion || !$fecha_limite || !$materia_id) {
    http_response_code(400);
    echo json_encode(["error" => "Todos los campos son requeridos"]);
    exit();
}

$stmt = $pdo->prepare("INSERT INTO asignaciones (titulo, descripcion, fecha_limite, materia_id) VALUES (?, ?, ?, ?)");
$stmt->execute([$titulo, $descripcion, $fecha_limite, $materia_id]);
$asignacion_id = $pdo->lastInsertId();

$archivosGuardados = [];
if (!empty($_FILES['archivos'])) {
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/educlass/uploads/asignaciones/';
    $permitidos = ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt'];
    $maxSize = 5 * 1024 * 1024;

    $total = count($_FILES['archivos']['name']);
    for ($i = 0; $i < $total; $i++) {
        if ($_FILES['archivos']['error'][$i] !== UPLOAD_ERR_OK) continue;
        if ($_FILES['archivos']['size'][$i] > $maxSize) continue;
        $nombreOriginal = $_FILES['archivos']['name'][$i];
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        if (!in_array($ext, $permitidos)) continue;
        $nombreGuardado = uniqid('archivo_', true) . '.' . $ext;
        $destino = $uploadDir . $nombreGuardado;
        if (move_uploaded_file($_FILES['archivos']['tmp_name'][$i], $destino)) {
            $stmt2 = $pdo->prepare("INSERT INTO asignacion_archivos (asignacion_id, nombre_original, nombre_guardado) VALUES (?, ?, ?)");
            $stmt2->execute([$asignacion_id, $nombreOriginal, $nombreGuardado]);
            $archivosGuardados[] = $nombreOriginal;
        }
    }
}

echo json_encode([
    "success"       => true,
    "asignacion_id" => $asignacion_id,
    "archivos"      => $archivosGuardados
]);