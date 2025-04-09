<?php
include '../../config/database.php';
include '../includes/header.php';
include '../includes/sidebar.php';
include '../auth/verificar_sesion.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id_chat = $_GET['id'];

// Obtengo la información del chat
$query = "SELECT c.*, u.nombre as nombre_usuario, u.email as email_usuario
            FROM chats c
            INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
            WHERE c.id_chat = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_chat);
$stmt->execute();
$result = $stmt->get_result();
$chat = $result->fetch_assoc();

if (!$chat) {
    header('Location: index.php');
    exit;
}
?>

<div class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><?php echo htmlspecialchars($chat['nombre_usuario']); ?></h5>
                        <small class="text-muted"><?php echo htmlspecialchars($chat['email_usuario']); ?></small>
                    </div>
                    <div>
                        <span class="badge <?php echo $chat['estado'] === 'activo' ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo ucfirst($chat['estado']); ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body chat-messages" id="chat-messages">
            </div>
            <div class="card-footer">
                <form id="chat-form">
                    <div class="input-group">
                        <input type="text" class="form-control" id="message-input" 
                                    placeholder="Escribe un mensaje...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="document.getElementById('image-input').click()">
                                <i class="fas fa-image"></i>
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                    <input type="file" id="image-input" accept="image/*" style="display: none">
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.0.1/socket.io.min.js"></script>
<script>
const socket = io('http://localhost:3000', {
    transports: ['websocket']
});

const chatId = <?php echo $id_chat; ?>;

socket.on('connect', () => {
    console.log('Conectado al servidor de chat');
    socket.emit('join_chat', chatId);
    loadMessages();
});

socket.on('receive_message', (data) => {
    appendMessage(data);
});

function loadMessages() {
    fetch(`cargar_mensajes.php?id_chat=${chatId}`)
        .then(response => response.json())
        .then(data => {
            const messagesContainer = document.getElementById('chat-messages');
            messagesContainer.innerHTML = '';
            data.forEach(message => appendMessage(message));
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        });
}

function appendMessage(message) {
    const messagesContainer = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${message.userType === 'admin' ? 'sent' : 'received'}`;
    
    let content = `<div class="message-content">`;
    if (message.message) {  // Esto verifica si hay mensajes
        content += `<p>${message.message}</p>`;
    }
    if (message.timestamp) {
        content += `<small class="text-muted">${new Date(message.timestamp).toLocaleTimeString()}</small>`;
    }
    content += `</div>`;
    
    messageDiv.innerHTML = content;
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const input = document.getElementById('message-input');
    const message = input.value.trim();
    
    if(message) {
        socket.emit('send_message', {
            chatId: chatId,
            userId: <?php echo $_SESSION['admin_id']; ?>,
            message: message,
            userType: 'admin',
            userName: '<?php echo $_SESSION['admin_nombre']; ?>',
            userEmail: '<?php echo $_SESSION['admin_email']; ?>'
        });
        input.value = '';
    }
});

// Manejar subida de imágenes
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
                    userId: <?php echo $_SESSION['admin_id']; ?>,
                    message: 'Imagen enviada',
                    imagen_url: data.url,
                    userType: 'admin',
                    userName: '<?php echo $_SESSION['admin_nombre']; ?>',
                    userEmail: '<?php echo $_SESSION['admin_email']; ?>'
                });
            }
        });
    }
});
</script>

