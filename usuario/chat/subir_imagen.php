<?php
include '../../config/database.php';
include '../auth/verificar_sesion.php';

header('Content-Type: application/json');

if (!isset($_FILES['image']) || !isset($_POST['chat_id'])) {
    echo json_encode(['success' => false, 'error' => 'No se proporcionó imagen o ID de chat']);
    exit;
}

$chat_id = $_POST['chat_id'];
$file = $_FILES['image'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'error' => 'Tipo de archivo no permitido']);
    exit;
}

$upload_dir = '../../uploads/chat/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$filename = uniqid() . '_' . basename($file['name']);
$upload_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    // guardar el mensaje con la imagen en la base de datos
    $query = "INSERT INTO mensajes (id_chat, id_emisor, tipo_emisor, mensaje, imagen_url) 
              VALUES (?, ?, 'usuario', 'Imagen enviada', ?)";
    
    $relative_path = 'uploads/chat/' . $filename;
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $chat_id, $_SESSION['usuario_id'], $relative_path);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'url' => $relative_path
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Error al guardar en la base de datos'
        ]);
    }
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Error al subir la imagen'
    ]);
}

$conn->close();
?>