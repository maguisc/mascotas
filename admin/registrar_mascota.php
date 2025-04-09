<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';
include 'auth/verificar_sesion.php';

// obtengo datos de la mascota si es una edición
$mascota = null;
if (isset($_GET['id'])) {
    $id_mascota = $_GET['id'];
    $sql = "SELECT * FROM mascotas WHERE id_mascota = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_mascota);
    $stmt->execute();
    $result = $stmt->get_result();
    $mascota = $result->fetch_assoc();
}
?>

<div class="content">
    <h2><?php echo $mascota ? 'Editar Mascota' : 'Registrar Nueva Mascota'; ?></h2>
    <form action="guardar_mascota.php" method="POST" enctype="multipart/form-data">
        <?php if ($mascota) { ?>
            <input type="hidden" name="id_mascota" value="<?php echo $mascota['id_mascota']; ?>">
        <?php } ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" 
                        value="<?php echo $mascota ? $mascota['nombre'] : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo</label>
                    <select class="form-control" id="tipo" name="tipo" required>
                        <option value="Perro" <?php echo ($mascota && $mascota['tipo'] == 'Perro') ? 'selected' : ''; ?>>Perro</option>
                        <option value="Gato" <?php echo ($mascota && $mascota['tipo'] == 'Gato') ? 'selected' : ''; ?>>Gato</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="genero" class="form-label">Género</label>
                    <select class="form-control" id="genero" name="genero" required>
                        <option value="Macho" <?php echo ($mascota && $mascota['genero'] == 'Macho') ? 'selected' : ''; ?>>Macho</option>
                        <option value="Hembra" <?php echo ($mascota && $mascota['genero'] == 'Hembra') ? 'selected' : ''; ?>>Hembra</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="raza" class="form-label">Raza</label>
                    <input type="text" class="form-control" id="raza" name="raza" 
                        value="<?php echo $mascota ? $mascota['raza'] : ''; ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="tamano" class="form-label">Tamaño</label>
                    <select class="form-control" id="tamano" name="tamano" required>
                        <option value="Pequeño" <?php echo ($mascota && $mascota['tamano'] == 'Pequeño') ? 'selected' : ''; ?>>Chico</option>
                        <option value="Mediano" <?php echo ($mascota && $mascota['tamano'] == 'Mediano') ? 'selected' : ''; ?>>Mediano</option>
                        <option value="Grande" <?php echo ($mascota && $mascota['tamano'] == 'Grande') ? 'selected' : ''; ?>>Grande</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="edad" class="form-label">Edad</label>
                    <input type="text" class="form-control" id="edad" name="edad" 
                        value="<?php echo $mascota ? $mascota['edad'] : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="castrado" class="form-label">Castrado</label>
                    <select class="form-control" id="castrado" name="castrado" required>
                        <option value="Si" <?php echo ($mascota && $mascota['castrado'] == 'Si') ? 'selected' : ''; ?>>Sí</option>
                        <option value="No" <?php echo ($mascota && $mascota['castrado'] == 'No') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="vacunado" class="form-label">Vacunado</label>
                    <select class="form-control" id="vacunado" name="vacunado" required>
                        <option value="Si" <?php echo ($mascota && $mascota['vacunado'] == 'Si') ? 'selected' : ''; ?>>Sí</option>
                        <option value="No" <?php echo ($mascota && $mascota['vacunado'] == 'No') ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
            </div>
            <div class="col-12">
                <div class="mb-3">
                    <label for="imagen" class="form-label">Imagen</label>
                    <?php if ($mascota && $mascota['imagen']) { ?>
                        <div class="mb-3">
                            <img src="../<?php echo $mascota['imagen']; ?>" alt="Imagen actual" 
                                style="max-width: 200px; margin-bottom: 10px; display: block;">
                            <p class="text-muted">Selecciona una nueva imagen solo si deseas cambiar la actual</p>
                        </div>
                    <?php } ?>
                    <input type="file" class="form-control" id="imagen" name="imagen" 
                        accept="image/*" <?php echo $mascota ? '' : 'required'; ?>>
                </div>
                <div class="mb-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-control" id="estado" name="estado" required>
                        <option value="Adopción" <?php echo ($mascota && $mascota['estado'] == 'Adopción') ? 'selected' : ''; ?>>Adopción</option>
                        <option value="Tránsito" <?php echo ($mascota && $mascota['estado'] == 'Tránsito') ? 'selected' : ''; ?>>Tránsito</option>
                        <option value="Perdido" <?php echo ($mascota && $mascota['estado'] == 'Perdido') ? 'selected' : ''; ?>>Perdido</option>
                    </select>
                </div>
                <div class="d-inline-flex gap-2">
                    <button type="submit" class="btn btn-nueva">
                    <?php echo $mascota ? 'Actualizar' : 'Guardar'; ?>
                    </button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.getElementById('imagen').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (!file.type.startsWith('image/')) {
            alert('Por favor, selecciona un archivo de imagen válido.');
            this.value = '';
        }
    }
});
</script>

</body>
</html>