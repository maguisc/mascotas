<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'auth/verificar_sesion.php';
include '../config/database.php';

// eliminación
if (isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {
    header('Content-Type: application/json');
    
    $id_mascota = $_POST['id_mascota'];
    $respuesta = ['success' => false, 'message' => ''];
    
    try {
        // inicio transacción
        $conn->begin_transaction();
        
        // 1. primero elimino los formularios de tránsito
        $sql_transito = "DELETE FROM formularios_transito WHERE id_mascota = ?";
        $stmt_transito = $conn->prepare($sql_transito);
        $stmt_transito->bind_param("i", $id_mascota);
        $stmt_transito->execute();
        
        // 2. segundo elimino las publicaciones
        $sql_pub = "DELETE FROM publicaciones WHERE id_mascota = ?";
        $stmt_pub = $conn->prepare($sql_pub);
        $stmt_pub->bind_param("i", $id_mascota);
        $stmt_pub->execute();
        
        // 3. tercero obtengo la información de la imagen antes de eliminar la mascota
        $sql_imagen = "SELECT imagen FROM mascotas WHERE id_mascota = ?";
        $stmt_imagen = $conn->prepare($sql_imagen);
        $stmt_imagen->bind_param("i", $id_mascota);
        $stmt_imagen->execute();
        $resultado = $stmt_imagen->get_result();
        $mascota = $resultado->fetch_assoc();
        
        // 4. se elimina la mascota la mascota
        $sql_mascota = "DELETE FROM mascotas WHERE id_mascota = ?";
        $stmt_mascota = $conn->prepare($sql_mascota);
        $stmt_mascota->bind_param("i", $id_mascota);
        
        if ($stmt_mascota->execute()) {
            // confirmo la eliminacion con execute()
            $conn->commit();
            
            // eliminola imagen si es quexiste
            if ($mascota && $mascota['imagen']) {
                $ruta_imagen = "../" . $mascota['imagen'];
                if (file_exists($ruta_imagen)) {
                    unlink($ruta_imagen);
                }
            }
            
            $respuesta['success'] = true;
            $respuesta['message'] = 'Mascota eliminada exitosamente';
        } else {
            throw new Exception('Error al eliminar la mascota');
        }
        
    } catch (Exception $e) {
        // si hay algun error puedo revertir los cambios y muestro esta alerta
        $conn->rollback();
        $respuesta['message'] = 'Error al eliminar la mascota: ' . $e->getMessage();
    }
    
    echo json_encode($respuesta);
    exit;
}

// creación y actualización
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    $genero = $_POST['genero'];
    $raza = $_POST['raza'];
    $tamano = $_POST['tamano'];
    $edad = $_POST['edad'];
    $castrado = $_POST['castrado'];
    $vacunado = $_POST['vacunado'];
    $estado = $_POST['estado'];
    
    // se verifica que es una actualizacion
    $id_mascota = isset($_POST['id_mascota']) ? $_POST['id_mascota'] : null;
    
    // aca esta el manejo de la imagen
    $imagen = "";
    if(isset($_FILES['imagen']) && $_FILES['imagen']['size'] > 0) {
        $extension = pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION);
        $nombre_imagen = uniqid() . '.' . $extension;
        $target_dir = "../uploads/";
        $target_file = $target_dir . $nombre_imagen;
        
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
            $imagen = "uploads/" . $nombre_imagen;
        } else {
            echo "Error al subir la imagen.";
            exit;
        }
    }
    
    if ($id_mascota) {
        // actualizacion
        if ($imagen != "") {
            // si hay una nueva imagen se actualizan todos los datos
            $sql = "UPDATE mascotas SET 
                    nombre = ?, 
                    tipo = ?, 
                    genero = ?, 
                    raza = ?, 
                    tamano = ?, 
                    edad = ?, 
                    castrado = ?, 
                    vacunado = ?, 
                    estado = ?,
                    imagen = ?
                    WHERE id_mascota = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssi", $nombre, $tipo, $genero, $raza, $tamano, $edad, $castrado, $vacunado, $estado, $imagen, $id_mascota);
        } else {
            // si no hay una imagen nueva actualizo todo menos la imagen
            $sql = "UPDATE mascotas SET 
                    nombre = ?, 
                    tipo = ?, 
                    genero = ?, 
                    raza = ?, 
                    tamano = ?, 
                    edad = ?, 
                    castrado = ?, 
                    vacunado = ?, 
                    estado = ?
                    WHERE id_mascota = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssssi", $nombre, $tipo, $genero, $raza, $tamano, $edad, $castrado, $vacunado, $estado, $id_mascota);
        }
    } else {
        // nueva
        $sql = "INSERT INTO mascotas (nombre, tipo, genero, raza, tamano, edad, castrado, vacunado, imagen, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $nombre, $tipo, $genero, $raza, $tamano, $edad, $castrado, $vacunado, $imagen, $estado);
    }
    
    if ($stmt->execute()) {
        header("Location: index.php");
    } else {
        echo "Error: " . $conn->error;
    }
    
    $stmt->close();
}

$conn->close();
?>