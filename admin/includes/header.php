<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - Gestión de Mascotas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php
    $currentPath = $_SERVER['PHP_SELF'];
    $depth = substr_count(dirname($currentPath), '/') - 1;
    $cssPath = str_repeat('../', $depth) . 'css/styles.css';
    ?>
    <link href="<?php echo $cssPath; ?>" rel="stylesheet">
</head>
<body>
    <!-- Botón para la vista móvil -->
    <button id="sidebarToggle" class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </button>
</body>