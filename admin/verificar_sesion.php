<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error'] = "Debes iniciar sesión para acceder";
    header("Location: login.php");
    exit();
}
?>