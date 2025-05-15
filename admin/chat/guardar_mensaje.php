<?php
// Guardar mensaje del admin
include '../../config/database.php';

// Datos básicos
$id_chat = $_POST['id_chat'] ?? 0;
$email_usuario = $_POST['email_usuario'] ?? '';
$mensaje = $_POST['mensaje'] ?? '';
$imagen_url = $_POST['imagen_url'] ?? '';
$tipo_emisor = 'admin';

// Verificación mínima
if (!$id_chat || empty($email_usuario) || (empty($mensaje) && empty($imagen_url))) {
  exit;
}

// Insertar mensaje
$stmt = $conn->prepare("INSERT INTO mensajes (id_chat, tipo_emisor, mensaje, imagen_url) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $id_chat, $tipo_emisor, $mensaje, $imagen_url);

if ($stmt->execute()) {
  // Actualizar todos los chats de este usuario
  $texto = !empty($imagen_url) ? "[Imagen]" : $mensaje;
  $stmt2 = $conn->prepare("UPDATE chats SET ultimo_mensaje = ?, fecha_ultimo_mesaje = NOW(), fecha_actualizacion = NOW() WHERE email_usuario = ?");
  $stmt2->bind_param("ss", $texto, $email_usuario);
  $stmt2->execute();
}

echo "OK";
?>