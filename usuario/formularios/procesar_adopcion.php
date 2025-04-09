<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../../config/database.php';
include '../auth/verificar_sesion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger los datos del formulario
    $id_mascota = $_POST['id_mascota'];
    $id_usuario = $_SESSION['usuario_id'];
    $nombre_completo = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $nombre_mascota = trim($_POST['nombre_mascota']);
    $experiencia_previa = $_POST['experiencia_previa'];
    $acepta_visita = $_POST['acepta_visita'];
    $tipo_vivienda = $_POST['tipo_vivienda'];

    try {
        // Preparar la consulta SQL
        $sql = "INSERT INTO formularios_adopcion (
                    id_mascota, 
                    id_usuario, 
                    nombre_completo, 
                    email, 
                    telefono, 
                    direccion, 
                    nombre_mascota,
                    experiencia_previa, 
                    acepta_visita, 
                    tipo_vivienda
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iissssssss", 
            $id_mascota, 
            $id_usuario, 
            $nombre_completo, 
            $email, 
            $telefono, 
            $direccion, 
            $nombre_mascota,
            $experiencia_previa, 
            $acepta_visita, 
            $tipo_vivienda
        );

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            $_SESSION['success'] = "¡Formulario de adopción enviado con éxito! Nos pondremos en contacto con vos a la brevedad.";
            header("Location: ../index.php");
            exit();
        } else {
            $stmt->close();
            throw new Exception("Error al procesar la solicitud");
        }

    } catch (Exception $e) {
        $conn->close();
        $_SESSION['error'] = "Error al procesar el formulario: " . $e->getMessage();
        header("Location: formulario_adopcion.php?id=" . $id_mascota);
        exit();
    }

} else {
    $conn->close();
    header("Location: ../index.php");
    exit();
}
?>