<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../config/database.php';
include 'auth/verificar_sesion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_mascota = $_POST['id_mascota'];
    $tipo = $_POST['tipo'];
    
    // verifico si ya existe una publicación activa para esta mascota
    $sql_check = "SELECT * FROM publicaciones WHERE id_mascota = ? AND estado_publicacion = 'Activa'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_mascota);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        // ya existe una publicación activa
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una publicación activa para esta mascota'
        ]);
        exit;
    }
    
    // insertar nueva publicación
    $sql = "INSERT INTO publicaciones (id_mascota, tipo_publicacion) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_mascota, $tipo);
    
    if ($stmt->execute()) {
        // actualizar el estado de la mascota
        $sql_update = "UPDATE mascotas SET estado = ? WHERE id_mascota = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $tipo, $id_mascota);
        
        if ($stmt_update->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el estado de la mascota'
            ]);
        }
        $stmt_update->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear la publicación'
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}

$conn->close();
?>