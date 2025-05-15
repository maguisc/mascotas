<?php
include '../../config/database.php';
include '../auth/verificar_sesion.php';

// Habilitar registro de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar parámetros
$idChat = isset($_GET['id_chat']) ? (int)$_GET['id_chat'] : 0;

header('Content-Type: application/json');

// Validar ID de chat
if (!$idChat) {
  echo json_encode([
    'error' => 'ID de chat no proporcionado',
    'id_chat' => $idChat
  ]);
  exit;
}

try {
  // Verificación de permisos
  $consultaVerificar = "SELECT 1 FROM chats WHERE id_chat = ? AND id_usuario = ?";
  $stmtVerificar = $conn->prepare($consultaVerificar);
  $stmtVerificar->bind_param("ii", $idChat, $_SESSION['usuario_id']);
  $stmtVerificar->execute();
  $resultadoVerificar = $stmtVerificar->get_result();
  
  if ($resultadoVerificar->num_rows === 0) {
    echo json_encode([
      'error' => 'No tienes permiso para acceder a este chat',
      'id_chat' => $idChat,
      'usuario_id' => $_SESSION['usuario_id']
    ]);
    exit;
  }

  // Consultar mensajes
  $consultaMensajes = "SELECT * FROM mensajes WHERE id_chat = ? ORDER BY fecha_envio ASC";
  $stmtMensajes = $conn->prepare($consultaMensajes);
  $stmtMensajes->bind_param("i", $idChat);
  $stmtMensajes->execute();
  $resultado = $stmtMensajes->get_result();

  // Convertir a array
  $mensajes = [];
  while ($fila = $resultado->fetch_assoc()) {
    $mensajes[] = $fila;
  }
  
  // Devolver resultados
  echo json_encode($mensajes);
  
} catch (Exception $e) {
  // Capturar cualquier error
  echo json_encode([
    'error' => 'Error en la consulta: ' . $e->getMessage(),
    'id_chat' => $idChat
  ]);
}
?>