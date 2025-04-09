<?php
$from_chat = true; // Agregar esta línea
include '../includes/header.php';
include '../../config/database.php';
include '../auth/verificar_sesion.php';

if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$id_chat = $_GET['id'];

// verificar que el chat pertenece al usuario
$query = "SELECT * FROM chats WHERE id_chat = ? AND id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_chat, $_SESSION['usuario_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Adoptame Saladillo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="../../css/styles.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Chat con Adoptame Saladillo</h5>
            </div>
            <div class="card-body chat-messages" id="chat-messages">
                <!-- los mensajes se cargan aca -->
            </div>
            <div class="card-footer">
                <form id="chat-form">
                    <div class="input-group">
                        <input type="file" id="image-input" accept="image/*" style="display: none">
                        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('image-input').click()">
                            <i class="fas fa-image"></i>
                        </button>
                        <input type="text" class="form-control" id="message-input" placeholder="Escribe un mensaje...">
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.socket.io/4.0.1/socket.io.min.js"></script>
    <script>
    const socket = io('http://localhost:3000', {
        transports: ['websocket']
    });

    const chatId = <?php echo $id_chat; ?>;

    function cargarMensajesAnteriores() {
        fetch(`cargar_mensajes.php?id_chat=${chatId}`)
            .then(response => response.json())
            .then(data => {
                const messagesContainer = document.getElementById('chat-messages');
                messagesContainer.innerHTML = '';
                data.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `chat-message ${message.tipo_emisor === 'usuario' ? 'sent' : 'received'}`;
                    
                    let content = `<div class="message-content">`;
                    if (message.imagen_url) {
                        content += `<img src="../../${message.imagen_url}" class="message-image" alt="Imagen">`;
                    }
                    if (message.mensaje && message.mensaje !== 'Imagen enviada') {
                        content += `<p>${message.mensaje}</p>`;
                    }
                    content += '</div>';
                    
                    messageDiv.innerHTML = content;
                    messagesContainer.appendChild(messageDiv);
                });
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            });
    }

    socket.on('connect', () => {
        console.log('Conectado al chat');
        socket.emit('join_chat', chatId);
        cargarMensajesAnteriores();
    });

    socket.on('receive_message', (data) => {
        appendMessage(data);
    });

    function appendMessage(message) {
        const messagesContainer = document.getElementById('chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${message.tipo_emisor === 'usuario' ? 'sent' : 'received'}`;
        
        let content = `<div class="message-content">`;
        if (message.imagen_url) {
            content += `<img src="../../${message.imagen_url}" class="message-image" alt="Imagen">`;
        }
        if ((message.message || message.mensaje) && message.message !== 'Imagen enviada') {
            content += `<p>${message.message || message.mensaje}</p>`;
        }
        content += '</div>';
        
        messageDiv.innerHTML = content;
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if(message) {
        // solo envio el mensaje a través del socket
        socket.emit('send_message', {
            chatId: chatId,
            userId: <?php echo $_SESSION['usuario_id']; ?>,
            message: message,
            tipo_emisor: 'usuario',
            userName: '<?php echo $_SESSION['usuario_nombre']; ?>',
            userEmail: '<?php echo $_SESSION['usuario_email']; ?>'
        });
        input.value = '';
    }
});

    // manejo de imágenes
    document.getElementById('image-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('image', file);
            formData.append('chat_id', chatId);
            
            fetch('subir_imagen.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    socket.emit('send_message', {
                        chatId: chatId,
                        userId: <?php echo $_SESSION['usuario_id']; ?>,
                        mensaje: '',  // No enviamos mensaje de "Imagen enviada"
                        imagen_url: data.url,
                        tipo_emisor: 'usuario',
                        userName: '<?php echo $_SESSION['usuario_nombre']; ?>',
                        userEmail: '<?php echo $_SESSION['usuario_email']; ?>'
                    });
                }
            });
        }
    });
    </script>
</body>
</html>