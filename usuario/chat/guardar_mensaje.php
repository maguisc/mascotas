<?php
include '../../config/database.php';
include '../auth/verificar_sesion.php';

// Obtener datos
$idChat = $_POST['id_chat'] ?? 0;
$mensaje = $_POST['mensaje'] ?? '';
$imagenUrl = $_POST['imagen_url'] ?? '';
$tipoEmisor = 'usuario';
$respuesta = ["success" => false];

// Validación
if (!$idChat || (empty($mensaje) && empty($imagenUrl))) {
  echo json_encode($respuesta);
  exit;
}

// Verificación
$consultaVerificar = "SELECT 1 FROM chats WHERE id_chat = ? AND id_usuario = ?";
$stmtVerificar = $conn->prepare($consultaVerificar);
$stmtVerificar->bind_param("ii", $idChat, $_SESSION['usuario_id']);
$stmtVerificar->execute();
if ($stmtVerificar->get_result()->num_rows === 0) {
  echo json_encode($respuesta);
  exit;
}

// Insertar mensaje
$stmtInsertar = $conn->prepare("INSERT INTO mensajes (id_chat, tipo_emisor, mensaje, imagen_url) VALUES (?, ?, ?, ?)");
$stmtInsertar->bind_param("isss", $idChat, $tipoEmisor, $mensaje, $imagenUrl);

if ($stmtInsertar->execute()) {
  // Actualizar chat
  $textoResumen = !empty($imagenUrl) ? "[Imagen]" : $mensaje;
  $conn->query("UPDATE chats SET ultimo_mensaje = '$textoResumen', fecha_ultimo_mesaje = NOW(), fecha_actualizacion = NOW() WHERE id_chat = $idChat");
  
  $respuesta["success"] = true;
  $respuesta["mensaje_id"] = $stmtInsertar->insert_id;
}

echo json_encode($respuesta);
?>