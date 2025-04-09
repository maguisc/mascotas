<?php
include '../../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../auth/verificar_sesion.php';

$query = "SELECT 
            c.id_chat,
            u.nombre as nombre_usuario,
            u.email as email_usuario,
            m.mensaje as ultimo_mensaje,
            m.fecha_envio as fecha_ultimo_mensaje
            FROM (
                SELECT MAX(id_chat) as id_chat, id_usuario
                FROM chats
                GROUP BY id_usuario
            ) as ultimos_chats
            JOIN chats c ON c.id_chat = ultimos_chats.id_chat
            JOIN usuarios u ON c.id_usuario = u.id_usuario
            LEFT JOIN mensajes m ON m.id_chat = c.id_chat
            AND m.id_mensaje = (
                SELECT id_mensaje 
                FROM mensajes 
                WHERE id_chat = c.id_chat 
                ORDER BY fecha_envio DESC 
                LIMIT 1
            )
            ORDER BY m.fecha_envio DESC";

$result = $conn->query($query);
?>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <!-- Esta es la lista de Chats -->
            <div class="col-md-4 chat-list">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Chats Activos</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php if($result && $result->num_rows > 0): ?>
                            <?php while($chat = $result->fetch_assoc()): ?>
                                <a href="#" class="list-group-item list-group-item-action chat-item" 
                                    onclick="loadChat(<?php echo $chat['id_chat']; ?>)"
                                    data-chat-id="<?php echo $chat['id_chat']; ?>">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($chat['nombre_usuario']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($chat['email_usuario']); ?></small>
                                            <?php if($chat['ultimo_mensaje']): ?>
                                                <p class="mb-1 text-truncate">
                                                    <?php echo htmlspecialchars($chat['ultimo_mensaje']); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <?php if($chat['fecha_ultimo_mensaje']): ?>
                                            <small class="text-muted">
                                                <?php echo date('H:i', strtotime($chat['fecha_ultimo_mensaje'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="list-group-item text-center text-muted">
                                No hay chats activos
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Chat Activo -->
            <div class="col-md-8">
                <div class="card chat-window">
                    <div class="card-header" id="chat-header">
                        <h5 class="card-title mb-0">Chat</h5>
                    </div>
                    <div class="card-body chat-messages" id="chat-messages">
                        <!-- Los mensajes se van a cargar en esta parte -->
                    </div>
                    <div class="card-footer">
                    <form id="chat-form" class="d-none">
    <div class="input-group">
        <input type="text" class="form-control" id="message-input" 
            placeholder="Escribe un mensaje...">
        <input type="file" id="image-input" accept="image/*" style="display: none;">
        <button type="button" class="btn btn-light" onclick="document.getElementById('image-input').click()">
            <i class="fas fa-image"></i>
        </button>
        <button type="submit" class="btn btn-primary">Enviar</button>
    </div>
</form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.0.1/socket.io.min.js"></script>
<script>
let currentChatId = null;
const socket = io('http://localhost:3000', {
    transports: ['websocket']
});

socket.on('connect', () => {
    console.log('Conectado al servidor de chat');
});

socket.on('receive_message', (data) => {
    if(data.chatId == currentChatId) {
        appendMessage(data);
    }
});

function loadChat(chatId) {
    currentChatId = chatId;
    document.getElementById('chat-form').classList.remove('d-none');
    
    // Marcar chat como activo
    document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`.chat-item[data-chat-id="${chatId}"]`).classList.add('active');
    
    socket.emit('join_chat', chatId);

    // Limpiar y mostrar carga
    const messagesContainer = document.getElementById('chat-messages');
    messagesContainer.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';

    // Cargar mensajes
    fetch(`cargar_mensajes.php?id_chat=${chatId}`)
        .then(response => response.json())
        .then(data => {
            messagesContainer.innerHTML = '';
            if (Array.isArray(data)) {
                data.forEach(message => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `chat-message ${message.tipo_emisor === 'admin' ? 'sent' : 'received'}`;
                    
                    let content = `<div class="message-content">`;
                    // Si hay imagen se muestra
                    if (message.imagen_url) {
                        content += `<img src="../../${message.imagen_url}" class="message-image" alt="Imagen">`;
                    }
                    // Mostrar mensaje si existe y no es imagen enviada
                    if (message.mensaje && message.mensaje !== 'Imagen enviada') {
                        content += `<p>${message.mensaje}</p>`;
                    }
                    content += '</div>';
                    
                    messageDiv.innerHTML = content;
                    messagesContainer.appendChild(messageDiv);
                });
            }
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        })
        .catch(error => {
            console.error('Error:', error);
            messagesContainer.innerHTML = '<div class="alert alert-danger">Error al cargar mensajes</div>';
        });
}

function appendMessage(message) {
    const messagesContainer = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${message.tipo_emisor === 'admin' ? 'sent' : 'received'}`;
    
    let content = `<div class="message-content">`;
    // Si hay imagen se muestra
    if (message.imagen_url) {
        content += `<img src="../../${message.imagen_url}" class="message-image" alt="Imagen">`;
    }
    // Mostrar mensaje si existe y no es imagen enviada
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
    
    if(message && currentChatId) {
        // Solamente mando el mensaje a través del socket
        socket.emit('send_message', {
            chatId: currentChatId,
            userId: <?php echo $_SESSION['admin_id']; ?>,
            message: message,
            tipo_emisor: 'admin',
            userName: '<?php echo $_SESSION['admin_nombre']; ?>',
            userEmail: '<?php echo $_SESSION['admin_email']; ?>'
        });
        
        
        input.value = '';
    }
});
</script>
</body>
</html>