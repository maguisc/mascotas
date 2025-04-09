<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ajustar rutas si se accede desde chat
$base_url = isset($from_chat) ? "../" : "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoptame Saladillo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>../css/styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo $base_url; ?>index.php">Adoptame Saladillo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mascotas_adopcion.php' ? 'active' : ''; ?>" 
                            href="<?php echo $base_url; ?>mascotas_adopcion.php">Mascotas en Adopción</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mascotas_transito.php' ? 'active' : ''; ?>" 
                            href="<?php echo $base_url; ?>mascotas_transito.php">Mascotas en Tránsito</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'mascotas_perdidas.php' ? 'active' : ''; ?>" 
                            href="<?php echo $base_url; ?>mascotas_perdidas.php">Mascotas Perdidas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reportar_mascota.php' ? 'active' : ''; ?>" 
                        href="#" onclick="iniciarChat(); return false;">Reportar Mascota</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'realizar_donacion.php' ? 'active' : ''; ?>" 
                            href="<?php echo $base_url; ?>realizar_donacion.php">Realizar Donación</a>
                    </li>
                </ul>
                <div class="ms-auto">
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                        <span class="navbar-text me-3">
                            Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?>
                        </span>
                        <a class="btn btn-outline-danger" href="<?php echo $base_url; ?>auth/logout/logout.php">Cerrar Sesión</a>
                    <?php else: ?>
                        <a class="btn btn-outline-primary me-2" href="<?php echo $base_url; ?>auth/login/login.php">Iniciar Sesión</a>
                        <a class="btn btn-primary" href="<?php echo $base_url; ?>auth/registro/registro.php">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script src="https://cdn.socket.io/4.0.1/socket.io.min.js"></script>
    <script>
    function iniciarChat() {
        <?php if(!isset($_SESSION['usuario_id'])): ?>
            alert('Debes iniciar sesión para reportar una mascota');
            window.location.href = '<?php echo $base_url; ?>auth/login/login.php';
            return;
        <?php endif; ?>

        fetch('<?php echo $base_url; ?>chat/iniciar.php')
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    window.location.href = '<?php echo $base_url; ?>chat/' + data.redirect;
                } else {
                    alert('Error al iniciar el chat');
                }
            });
    }
    </script>
</body>