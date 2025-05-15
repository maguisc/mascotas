<?php
session_start();
include_once '../includes/sidebar.php';
include_once(__DIR__ . '/../../config/database.php');

// Consulta para agrupar por email_usuario
$consulta = $conn->query("SELECT email_usuario, nombre_usuario, MAX(id_chat) as id_chat, 
                          MAX(fecha_actualizacion) as fecha_actualizacion, ultimo_mensaje 
                          FROM chats 
                          GROUP BY email_usuario 
                          ORDER BY fecha_actualizacion DESC");
$chats = $consulta->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid mt-4">
  <div class="row">
    <div class="col-md-3"></div>
    
    <!-- Lista de chats -->
    <div class="col-md-3">
      <div class="card">
        <div class="card-header">
          <h4>Chats Activos</h4>
        </div>
        <div class="card-body p-0">
          <div style="max-height: 500px; overflow-y: auto;">
            <ul class="list-group" id="lista-chats">
              <?php foreach ($chats as $chat): ?>
              <li class="list-group-item chat-item" data-id="<?= $chat['id_chat'] ?>" data-email="<?= $chat['email_usuario'] ?>">
                <b><?= $chat['nombre_usuario'] ?></b><br>
                <small><?= $chat['email_usuario'] ?></small>
                <?php if (!empty($chat['ultimo_mensaje'])): ?>
                <p class="mb-0"><small><?= $chat['ultimo_mensaje'] ?></small></p>
                <?php endif; ?>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel del chat -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h4>Chat</h4>
        </div>
        <div class="card-body">
          <div id="chat-box" style="height: 400px; overflow-y: auto; background-color: white; padding: 10px;"></div>
          
          <!-- Formulario -->
          <form id="chat-form" class="mt-3">
            <input type="hidden" name="id_chat" id="chat-id">
            <input type="hidden" name="email_usuario" id="email-usuario">
            <input type="hidden" name="imagen_url" id="imagen-url" value="">
            
            <div class="input-group mb-2">
              <input type="text" class="form-control" id="message-input" name="mensaje" placeholder="Escribe un mensaje...">
              <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
            
            <div>
              <button type="button" id="image-button" class="btn btn-secondary btn-sm">Adjuntar imagen</button>
              <input type="file" id="image-upload" style="display: none;" accept="image/jpeg,image/png,image/gif">
              <div id="image-preview" class="d-none mt-2">
                <img src="" style="max-height: 40px;">
                <button type="button" id="remove-image" class="btn btn-sm btn-danger">X</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Variables globales
const INTERVALO_ACTUALIZACION = 5000; // 5 segundos

// Funciones principales
function cargarMensajes(chatId, email, desplazarAbajo = true) {
  fetch('cargar_mensajes.php?email=' + email)
    .then(response => response.text())
    .then(data => {
      document.getElementById('chat-box').innerHTML = data;
      if (desplazarAbajo) {
        document.getElementById('chat-box').scrollTop = 99999;
      }
    });
}

function enviarMensaje() {
  const mensaje = document.getElementById('message-input').value.trim();
  const imagenUrl = document.getElementById('imagen-url').value;
  const chatId = document.getElementById('chat-id').value;
  const email = document.getElementById('email-usuario').value;
  
  if ((!mensaje && !imagenUrl) || !chatId || !email) return;
  
  const formData = new FormData();
  formData.append('id_chat', chatId);
  formData.append('email_usuario', email);
  formData.append('mensaje', mensaje);
  formData.append('imagen_url', imagenUrl);
  
  const boton = document.querySelector('#chat-form button[type="submit"]');
  boton.disabled = true;
  
  fetch('guardar_mensaje.php', {
    method: 'POST',
    body: formData
  })
  .then(() => {
    document.getElementById('message-input').value = '';
    document.getElementById('imagen-url').value = '';
    document.getElementById('image-preview').classList.add('d-none');
    document.getElementById('image-upload').value = '';
    cargarMensajes(chatId, email);
    boton.disabled = false;
  })
  .catch(() => {
    boton.disabled = false;
  });
}

function subirImagen(archivo) {
  const chatId = document.getElementById('chat-id').value;
  if (!chatId) return;
  
  // Vista previa
  const lector = new FileReader();
  lector.onload = function(e) {
    document.querySelector('#image-preview img').src = e.target.result;
    document.getElementById('image-preview').classList.remove('d-none');
  };
  lector.readAsDataURL(archivo);
  
  // Subir al servidor
  const formData = new FormData();
  formData.append('image', archivo);
  formData.append('chat_id', chatId);
  
  fetch('subir_imagen.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      document.getElementById('imagen-url').value = data.url;
      document.getElementById('message-input').value = '[Imagen]';
    } else {
      document.getElementById('image-upload').value = '';
      document.getElementById('image-preview').classList.add('d-none');
      alert('Error al subir la imagen');
    }
  });
}

// Inicialización y eventos
document.addEventListener('DOMContentLoaded', function() {
  // Seleccionar primer chat
  const elementosChat = document.querySelectorAll('.chat-item');
  if (elementosChat.length > 0) {
    elementosChat[0].classList.add('active');
    document.getElementById('chat-id').value = elementosChat[0].getAttribute('data-id');
    document.getElementById('email-usuario').value = elementosChat[0].getAttribute('data-email');
    cargarMensajes(elementosChat[0].getAttribute('data-id'), elementosChat[0].getAttribute('data-email'));
  }
  
  // Eventos de click en chats
  document.querySelectorAll('.chat-item').forEach(item => {
    item.addEventListener('click', function() {
      document.querySelectorAll('.chat-item').forEach(c => c.classList.remove('active'));
      item.classList.add('active');
      
      const chatId = item.getAttribute('data-id');
      const email = item.getAttribute('data-email');
      document.getElementById('chat-id').value = chatId;
      document.getElementById('email-usuario').value = email;
      cargarMensajes(chatId, email);
    });
  });

  // Envío de mensajes
  document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    enviarMensaje();
  });
  
  // Manejo de imágenes
  document.getElementById('image-button').addEventListener('click', function() {
    document.getElementById('image-upload').click();
  });
  
  document.getElementById('image-upload').addEventListener('change', function(e) {
    if (e.target.files[0]) subirImagen(e.target.files[0]);
  });
  
  document.getElementById('remove-image').addEventListener('click', function() {
    document.getElementById('image-upload').value = '';
    document.getElementById('imagen-url').value = '';
    document.getElementById('message-input').value = '';
    document.getElementById('image-preview').classList.add('d-none');
  });
  
  // Actualización automática
  setInterval(function() {
    const chatId = document.getElementById('chat-id').value;
    const email = document.getElementById('email-usuario').value;
    if (chatId && email) cargarMensajes(chatId, email, false);
  }, INTERVALO_ACTUALIZACION);
});
</script>