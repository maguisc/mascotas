<?php
include '../../config/database.php';
include '../auth/verificar_sesion.php';

header('Content-Type: application/json');

if (!isset($_GET['id_chat'])) {
    echo json_encode(['error' => 'ID de chat no proporcionado']);
    exit;
}

$id_chat = $_GET['id_chat'];

// consulta todos los mensajes del chat ordenados por id_mensaje
$query = "SELECT 
            m.*,
            COALESCE(u.nombre, a.nombre) as nombre_emisor,
            COALESCE(u.email, a.email) as email_emisor
        FROM mensajes m
        LEFT JOIN usuarios u ON m.id_emisor = u.id_usuario AND m.tipo_emisor = 'usuario'
        LEFT JOIN administrador a ON m.id_emisor = a.id_admin AND m.tipo_emisor = 'admin'
        WHERE m.id_chat = ?
        ORDER BY m.id_mensaje ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_chat);
$stmt->execute();
$result = $stmt->get_result();

$mensajes = [];
while ($row = $result->fetch_assoc()) {
    $mensajes[] = [
        'id' => $row['id_mensaje'],
        'mensaje' => $row['mensaje'],
        'tipo_emisor' => $row['tipo_emisor'],
        'nombre_emisor' => $row['nombre_emisor'],
        'email_emisor' => $row['email_emisor'],
        'imagen_url' => $row['imagen_url'],
        'timestamp' => strtotime($row['fecha_envio']) * 1000
    ];
}

echo json_encode($mensajes);
$stmt->close();
$conn->close();
?>