<?php 
include 'includes/header.php';
include '../config/database.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoptame Saladillo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/mascotas/CSS/styles.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<?php if(isset($_SESSION['success'])): ?>
    <div class="container mt-3">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<div class="hero-section">
    <img src="/mascotas/uploads/portada.jpg" alt="Portada Adoptame Saladillo" class="hero-image">
    <div class="hero-text">
        <h1>Bienvenidos a Adoptame Saladillo</h1>
        <p>¡Encontrá a tu compañero perfecto, o ayudá a una mascota a encontrar su hogar!</p>
    </div>
</div>


<div class="container mb-5">
    <div class="row g-4">
        <!-- primera fila con 3 tarjetas -->
        <div class="col-md-4">
            <div class="card h-100 card-adopcion">
                <div class="card-body">
                    <i class="fas fa-paw card-icon"></i>
                    <h3 class="card-title">Mascotas en Adopción</h3>
                    <p class="card-text">Encontrá a tu próximo compañero y dale un hogar lleno de amor.</p>
                    <a href="mascotas_adopcion.php" class="btn btn-adoptar">Ver mascotas</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 card-transito">
                <div class="card-body">
                    <i class="fas fa-home card-icon"></i>
                    <h3 class="card-title">Mascotas en Tránsito</h3>
                    <p class="card-text">Ayudá temporalmente a una mascota mientras encuentra su hogar definitivo.</p>
                    <a href="mascotas_transito.php" class="btn btn-adoptar">Ver mascotas</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 card-perdidas">
                <div class="card-body">
                    <i class="fas fa-search card-icon"></i>
                    <h3 class="card-title">Mascotas Perdidas</h3>
                    <p class="card-text">Ayudanos a reunir mascotas perdidas con sus familias.</p>
                    <a href="mascotas_perdidas.php" class="btn btn-adoptar">Ver mascotas</a>
                </div>
            </div>
        </div>

        <!-- segunda fila con 2 tarjetas centradas para que queden bien los espacios -->
        <div class="col-md-6">
            <div class="card h-100 card-reportar">
                <div class="card-body">
                    <i class="fas fa-bullhorn card-icon"></i>
                    <h3 class="card-title">Reportar Mascota</h3>
                    <p class="card-text">¿Encontraste o perdiste una mascota? Reportala acá y te ayudamos a difundir.</p>
                    <a href="#" onclick="iniciarChat(); return false;" class="btn btn-adoptar">Reportar</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100 card-donacion">
                <div class="card-body">
                    <i class="fas fa-heart card-icon"></i>
                    <h3 class="card-title">Realizar Donación</h3>
                    <p class="card-text">Tu ayuda es fundamental para continuar con nuestra labor de rescate y cuidado.</p>
                    <a href="realizar_donacion.php" class="btn btn-adoptar">Donar</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>