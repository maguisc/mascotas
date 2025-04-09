<?php
include '../../config/database.php';
include '../auth/verificar_sesion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
   echo json_encode(['success' => false, 'error' => 'No autorizado']);
   exit;
}

// Crear nuevo chat
$query = "INSERT INTO chats (id_usuario, nombre_usuario, email_usuario) 
         VALUES (?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("iss", 
   $_SESSION['usuario_id'],
   $_SESSION['usuario_nombre'], 
   $_SESSION['usuario_email']
);

if ($stmt->execute()) {
   $chat_id = $conn->insert_id;
   echo json_encode([
       'success' => true,
       'chat_id' => $chat_id,
       'redirect' => "chat.php?id=" . $chat_id
   ]);
} else {
   echo json_encode([
       'success' => false, 
       'error' => 'Error al crear el chat: ' . $conn->error
   ]);
}

$stmt->close();
$conn->close();
?>