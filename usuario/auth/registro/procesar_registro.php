<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // verificar si el email ya está registrado
    $sql_check = "SELECT COUNT(*) as total FROM usuarios WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();

    if($row['total'] > 0) {
        $_SESSION['error'] = "Este correo electrónico ya está registrado";
        header("Location: registro.php");
        exit();
    }

    // validar contraseña
    if (!(
        strlen($password) >= 8 &&
        preg_match('/[A-Z]/', $password) &&
        preg_match('/[a-z]/', $password) &&
        preg_match('/[0-9]/', $password) &&
        preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)
    )) {
        $_SESSION['error'] = "La contraseña no cumple con los requisitos de seguridad";
        header("Location: registro.php");
        exit();
    }

    // encriptar contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // insertar usuario
    $sql = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nombre, $email, $password_hash);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registro exitoso. Por favor, inicia sesión.";
        header("Location: ../login/login.php");
    } else {
        $_SESSION['error'] = "Error al registrar: " . $conn->error;
        header("Location: registro.php");
    }

    $stmt->close();
}

$conn->close();
?>