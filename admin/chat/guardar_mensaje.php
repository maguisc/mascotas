<?php
include '../../config/database.php';
include '../auth/verificar_sesion.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['chat_id']) || !isset($data['mensaje'])) {
    echo json_encode(['success' => false, 'error' => 'Faltan datos']);
    exit;
}

$chat_id = $data['chat_id'];
$mensaje = $data['mensaje'];
$tipo_emisor = 'admin';

$query = "INSERT INTO mensajes (id_chat, id_emisor, tipo_emisor, mensaje, nombre_emisor, email_emisor) 
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("iissss", 
    $chat_id, 
    $_SESSION['admin_id'], 
    $tipo_emisor, 
    $mensaje,
    $_SESSION['admin_nombre'],
    $_SESSION['admin_email']
);

if ($stmt->execute()) {
    // Actualizar último mensaje del chat
    $update_query = "UPDATE chats 
                    SET ultimo_mensaje = ?, 
                        fecha_ultimo_mensaje = NOW() 
                    WHERE id_chat = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $mensaje, $chat_id);
    $update_stmt->execute();
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar mensaje']);
}

$stmt->close();
$conn->close();
?>