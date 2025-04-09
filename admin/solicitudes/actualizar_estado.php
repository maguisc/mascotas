<?php
include '../../config/database.php';
include '../auth/verificar_sesion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo = $_POST['tipo'];
    $id_formulario = $_POST['id'];
    $nuevo_estado = $_POST['estado'];
    
    // Determinar la tabla y estados válidos según el tipo de formulario
    $tabla = match($tipo) {
        'Adopción' => 'formularios_adopcion',
        'Tránsito' => 'formularios_transito',
        'Contacto' => 'formularios_contacto',
        default => ''
    };

    // Verificar que se haya encontrado una tabla válida
    if ($tabla === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Tipo de formulario no válido'
        ]);
        exit;
    }

    // Definir estados válidos según el tipo
    if ($tipo == 'Contacto') {
        $estados_validos = ['Pendiente', 'Respondido', 'Cerrado'];
    } else {
        $estados_validos = ['Pendiente', 'Aprobado', 'Rechazado'];
    }

    // Validar que el estado sea válido
    if (!in_array($nuevo_estado, $estados_validos)) {
        echo json_encode([
            'success' => false,
            'message' => 'Estado no válido para este tipo de formulario'
        ]);
        exit;
    }
    
    // Determinar el campo ID según el tipo
    $campo_id = $tipo == 'Contacto' ? 'id_contacto' : 'id_formulario';
    
    // Actualizar el estado
    $sql = "UPDATE " . $tabla . " SET estado = ? WHERE " . $campo_id . " = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nuevo_estado, $id_formulario);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true]);
    } else {
        $stmt->close();
        $conn->close();
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el estado'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>