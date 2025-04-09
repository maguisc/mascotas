<?php
include '../../config/database.php';
include '../auth/verificar_sesion.php';

if (!isset($_GET['id']) || !isset($_GET['tipo'])) {
    echo "Parámetros incompletos";
    exit;
}

$id_formulario = $_GET['id'];
$tipo = $_GET['tipo'];

switch ($tipo) {
    case 'Adopción':
        $sql = "SELECT 
                    f.*,
                    f.fecha_solicitud,
                    m.nombre as nombre_mascota,
                    m.imagen as imagen_mascota,
                    m.tipo as tipo_mascota,
                    m.edad as edad_mascota,
                    u.nombre as nombre_usuario,
                    u.email as email_usuario
                FROM formularios_adopcion f
                INNER JOIN mascotas m ON f.id_mascota = m.id_mascota
                INNER JOIN usuarios u ON f.id_usuario = u.id_usuario
                WHERE f.id_formulario = ?";
        break;
    
    case 'Tránsito':
        $sql = "SELECT 
                    t.*,
                    t.fecha_solicitud,
                    m.nombre as nombre_mascota,
                    m.imagen as imagen_mascota,
                    m.tipo as tipo_mascota,
                    m.edad as edad_mascota,
                    u.nombre as nombre_usuario,
                    u.email as email_usuario
                FROM formularios_transito t
                INNER JOIN mascotas m ON t.id_mascota = m.id_mascota
                INNER JOIN usuarios u ON t.id_usuario = u.id_usuario
                WHERE t.id_formulario = ?";
        break;
    
    case 'Contacto':
        $sql = "SELECT 
                    c.*,
                    c.fecha_contacto as fecha_solicitud,
                    m.nombre as nombre_mascota,
                    m.imagen as imagen_mascota,
                    m.tipo as tipo_mascota,
                    m.edad as edad_mascota,
                    u.nombre as nombre_usuario,
                    u.email as email_usuario
                FROM formularios_contacto c
                INNER JOIN mascotas m ON c.id_mascota = m.id_mascota
                INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
                WHERE c.id_contacto = ?";
        break;
    
    default:
        echo "Tipo de solicitud no válido";
        exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_formulario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Solicitud no encontrada";
    exit;
}

$solicitud = $result->fetch_assoc();
?>

<div class="container-fluid">
    <div class="row">
        <!-- Acá está la información de la mascota -->
        <div class="col-md-6">
            <h5>Información de la Mascota</h5>
            <div class="card mb-3">
                <img src="../../<?php echo $solicitud['imagen_mascota']; ?>" 
                    class="card-img-top" 
                    alt="<?php echo $solicitud['nombre_mascota']; ?>"
                    style="max-height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h6 class="card-title"><?php echo $solicitud['nombre_mascota']; ?></h6>
                    <p class="card-text">
                        <small>Tipo: <?php echo $solicitud['tipo_mascota']; ?></small><br>
                        <small>Edad: <?php echo $solicitud['edad_mascota']; ?></small>
                    </p>
                </div>
            </div>
        </div>

        <!-- acá la información del solicitante -->
        <div class="col-md-6">
            <h5>Información del Contacto</h5>
            <table class="table">
                <tr>
                    <th>Nombre:</th>
                    <td><?php echo $solicitud['nombre_completo']; ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo $solicitud['email']; ?></td>
                </tr>
                <tr>
                    <th>Teléfono:</th>
                    <td><?php echo $solicitud['telefono']; ?></td>
                </tr>
                <?php if($tipo != 'Contacto'): ?>
                <tr>
                    <th>Dirección:</th>
                    <td><?php echo $solicitud['direccion']; ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- detalles de la solicitud -->
    <div class="row mt-3">
        <div class="col-12">
            <h5>Detalles de la Solicitud</h5>
            <table class="table">
                <?php if($tipo == 'Contacto'): ?>
                    <tr>
                        <th>Ubicación donde se vio:</th>
                        <td><?php echo $solicitud['ubicacion_vista']; ?></td>
                    </tr>
                    <tr>
                        <th>Fecha del avistamiento:</th>
                        <td><?php echo date('d/m/Y', strtotime($solicitud['fecha_vista'])); ?></td>
                    </tr>
                    <tr>
                        <th>Información adicional:</th>
                        <td><?php echo $solicitud['informacion_adicional']; ?></td>
                    </tr>
                <?php else: ?>
                    <?php if($tipo == 'Tránsito'): ?>
                        <tr>
                            <th>Cubre gastos básicos:</th>
                            <td><?php echo $solicitud['cubre_gastos']; ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Acepta visita:</th>
                        <td><?php echo $solicitud['acepta_visita']; ?></td>
                    </tr>
                    <tr>
                        <th>Tipo de vivienda:</th>
                        <td><?php echo $solicitud['tipo_vivienda']; ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <th>Estado:</th>
                    <td>
                        <span class="badge <?php 
                            echo match($solicitud['estado']) {
                                'Pendiente' => 'bg-warning',
                                'Aprobado' => 'bg-success',
                                'Respondido' => 'bg-success',
                                'Rechazado' => 'bg-danger',
                                'Cerrado' => 'bg-secondary',
                                default => 'bg-secondary'
                            };
                        ?>">
                            <?php echo $solicitud['estado']; ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Fecha de solicitud:</th>
                    <td><?php echo date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>