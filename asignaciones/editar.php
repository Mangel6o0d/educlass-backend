<?php
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit();
}

$id           = intval($_POST['id'] ?? 0);
$profesor_id  = intval($_POST['profesor_id'] ?? 0);
$titulo       = trim($_POST['titulo'] ?? '');
$descripcion  = trim($_POST['descripcion'] ?? '');
$fecha_limite = trim($_POST['fecha_limite'] ?? '');
$eliminarIds  = json_decode($_POST['eliminar_ids'] ?? '[]', true);

if (!$id || !$profesor_id || !$titulo || !$descripcion || !$fecha_limite) {
    http_response_code(400);
    echo json_encode(["error" => "Todos los campos son requeridos"]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT a.id FROM asignaciones a
    INNER JOIN materias m ON a.materia_id = m.id
    WHERE a.id = ? AND m.profesor_id = ?
");
$stmt->execute([$id, $profesor_id]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(["error" => "No autorizado"]);
    exit();
}

$stmt = $pdo->prepare("UPDATE asignaciones SET titulo = ?, descripcion = ?, fecha_limite = ? WHERE id = ?");
$stmt->execute([$titulo, $descripcion, $fecha_limite, $id]);

if (!empty($eliminarIds)) {
    foreach ($eliminarIds as $archivoId) {
        $archivoId = intval($archivoId);
        $stmt = $pdo->prepare("SELECT nombre_guardado FROM asignacion_archivos WHERE id = ?");
        $stmt->execute([$archivoId]);
        $archivo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($archivo) {
            $ruta = $_SERVER['DOCUMENT_ROOT'] . '/educlass/uploads/asignaciones/' . $archivo['nombre_guardado'];
            if (file_exists($ruta)) unlink($ruta);
            $pdo->prepare("DELETE FROM asignacion_archivos WHERE id = ?")->execute([$archivoId]);
        }
    }
}

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
        if (move_uploaded_file($_FILES['archivos']['tmp_name'][$i], $uploadDir . $nombreGuardado)) {
            $stmt = $pdo->prepare("INSERT INTO asignacion_archivos (asignacion_id, nombre_original, nombre_guardado) VALUES (?, ?, ?)");
            $stmt->execute([$id, $nombreOriginal, $nombreGuardado]);
        }
    }
}

echo json_encode(["success" => true]);