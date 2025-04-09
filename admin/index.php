<?php
include '../config/database.php';
include 'includes/header.php';
include 'includes/sidebar.php';
include 'auth/verificar_sesion.php';
?>

<div class="content">
    <h2>Mascotas</h2>
    <a href="registrar_mascota.php" class="btn btn-nueva">Nueva</a>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID Mascota</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Edad</th>
                    <th>Estado</th>
                    <th>Crear publicación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM mascotas ORDER BY id_mascota DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id_mascota"] . "</td>";
                        echo "<td><img src='../" . $row["imagen"] . "' alt='Imagen de " . $row["nombre"] . "' style='width: 50px; height: 50px; object-fit: cover; border-radius: 5px;'></td>";
                        echo "<td>" . $row["nombre"] . "</td>";
                        echo "<td>" . $row["edad"] . "</td>";
                        echo "<td>" . $row["estado"] . "</td>";
                        echo "<td><button class='btn btn-publicar' onclick='abrirModalPublicacion(" . $row["id_mascota"] . ", \"" . $row["nombre"] . "\")'>Publicar</button></td>";
                        echo "<td>
                                <button class='btn btn-editar' onclick='editarMascota(" . $row["id_mascota"] . ")'>Editar</button>
                                <button class='btn btn-eliminar' onclick='eliminarMascota(" . $row["id_mascota"] . ")'>Eliminar</button>
                                </td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- modal de publicación -->
<div class="modal fade" id="modalPublicacion" tabindex="-1" aria-labelledby="modalPublicacionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPublicacionLabel">Crear Publicación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Seleccione el tipo de publicación para <span id="nombreMascota"></span>:</p>
                <input type="hidden" id="mascotaId" value="">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary mb-2" onclick="crearPublicacion('Adopción')">Mascota en Adopción</button>
                    <button class="btn btn-info mb-2" onclick="crearPublicacion('Tránsito')">Mascota en Tránsito</button>
                    <button class="btn btn-warning" onclick="crearPublicacion('Perdido')">Mascota Perdida</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function abrirModalPublicacion(id, nombre) {
    document.getElementById('mascotaId').value = id;
    document.getElementById('nombreMascota').textContent = nombre;
    var modal = new bootstrap.Modal(document.getElementById('modalPublicacion'));
    modal.show();
}

function crearPublicacion(tipo) {
    const mascotaId = document.getElementById('mascotaId').value;
    
    fetch('crear_publicacion.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id_mascota=${mascotaId}&tipo=${tipo}`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Publicación creada exitosamente');
            location.reload();
        } else {
            alert('Error al crear la publicación: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al crear la publicación');
    });
}

function eliminarMascota(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta mascota?')) {
        fetch('guardar_mascota.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `accion=eliminar&id_mascota=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar la mascota');
        });
    }
}

function editarMascota(id) {
    window.location.href = `registrar_mascota.php?id=${id}`;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>