<?php 
include 'includes/header.php';
include '../config/database.php';
?>

<div class="container mt-4">
    <div class="filtros">
        <input type="text" class="form-control" placeholder="Filtrar...">
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php
        $sql = "SELECT m.*, p.fecha_publicacion 
                FROM mascotas m 
                INNER JOIN publicaciones p ON m.id_mascota = p.id_mascota 
                WHERE p.tipo_publicacion = 'Perdido' 
                AND p.estado_publicacion = 'Activa'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                ?>
                <div class="col">
                    <div class="mascota-card">
                        <a href="detalle_mascota.php?id=<?php echo $row['id_mascota']; ?>" class="text-decoration-none">
                            <img src="<?php echo '../' . $row['imagen']; ?>" class="mascota-imagen" alt="<?php echo $row['nombre']; ?>">
                            <div class="mascota-info">
                                <h5><?php echo $row['nombre'] . " - " . $row['edad']; ?></h5>
                                <button class="btn btn-adoptar">Preguntar por <?php echo $row['nombre']; ?></button>
                            </div>
                        </a>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<div class='col-12'><p class='text-center'>No hay mascotas perdidas reportadas en este momento.</p></div>";
        }
        ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>