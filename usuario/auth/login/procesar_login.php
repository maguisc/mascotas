<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // preparar la consulta
    $sql = "SELECT id_usuario, nombre, email, password FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        // verificar la contraseña
        if (password_verify($password, $usuario['password'])) {
            // Iniciar sesión
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['email'];
            
            header("Location: ../../index.php");
            exit();
        } else {
            $_SESSION['error'] = "Contraseña incorrecta";
        }
    } else {
        $_SESSION['error'] = "No existe un usuario con ese correo";
    }

    header("Location: login.php");
    $stmt->close();
}

$conn->close();
?>