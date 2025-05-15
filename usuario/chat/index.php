<?php
$from_chat = true;
include '../includes/header.php';
include '../../config/database.php';
include '../auth/verificar_sesion.php';

// Validación
if (!isset($_GET['id'])) {
    header('Location: ../index.php');
    exit;
}

$idChat = $_GET['id'];

// Verificación
$consulta = "SELECT 1 FROM chats WHERE id_chat = ? AND id_usuario = ?";
$stmt = $conn->prepare($consulta);
$stmt->bind_param("ii", $idChat, $_SESSION['usuario_id']);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    header('Location: ../index.php');
    exit;
}
?>

<style>
/* Estilos de chat */
.mensaje-usuario {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 10px;
}

.mensaje-admin {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 10px;
}

.burbuja-usuario {
  background-color: #e88861; /* Color naranja para burbujas del usuario */
  color: white;
  border-radius: 15px;
  padding: 10px 15px;
  max-width: 70%;
  word-wrap: break-word;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.burbuja-admin {
  background-color: #e8e8e8; /* Color gris para burbujas del admin */
  color: #333;
  border-radius: 15px;
  padding: 10px 15px;
  max-width: 70%;
  word-wrap: break-word;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.hora-mensaje {
  font-size: 11px;
  color: rgba(255,255,255,0.8);
  text-align: right;
  margin-top: 4px;
}

.burbuja-admin .hora-mensaje {
  color: #888;
}

.imagen-mensaje {
  max-width: 100%;
  border-radius: 8px;
  cursor: pointer;
}
</style>

<div class="container mt-4">
    <!-- Panel informativo -->
    <div class="chat-intro">
        <h5><i class="fas fa-paw pata-icon"></i> ¿Perdiste o encontraste una mascota?</h5>
        <p>Contanos todo: si la mascota se perdió o la encontraste, cómo es (raza, color, tamaño), en qué zona fue, y cualquier seña particular que nos ayude a identificarla.</p>
        <p>Las fotos suman un montón. Nuestro equipo está atento para responder lo antes posible ¡Gracias por confiar en nosotros!</p>
    </div>

    <!-- Chat -->
    <div class="card chat-card">
        <div class="card-header">
            <h5>Comunicate con Adoptame Saladillo</h5>
        </div>
        <div class="card-body chat-messages" id="chat-body">
            <div id="chat-messages">
                <!-- mensajes -->
            </div>
        </div>
        <div class="card-footer">
            <form id="chat-form">
                <div class="input-group">
                    <input type="file" id="image-input" accept="image/*" style="display: none">
                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('image-input').click()">
                        <i class="fas fa-image"></i>
                    </button>
                    <input type="text" class="form-control" id="message-input" placeholder="Escribí tu mensaje...">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Añadir Socket.io cliente -->
<script src="https://cdn.socket.io/4.4.1/socket.io.min.js"></script>

<script>
// Configuración
const idChat = <?php echo $idChat; ?>;
const idUsuario = <?php echo $_SESSION['usuario_id']; ?>;
const nombreUsuario = "<?php echo $_SESSION['usuario_nombre']; ?>";
let idUltimoMensaje = 0;
let socket;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    // Cargar mensajes inicialmente
    cargarMensajes();
    
    // Ajustar altura del chat
    document.getElementById('chat-body').style.height = '400px';
    
    // Inicializar Socket.io
    iniciarSocket();
    
    // Configurar actualización periódica
    iniciarActualizacionAutomatica();
});

// Función para manejar la actualización automática
function iniciarActualizacionAutomatica() {
    console.log("Iniciando actualización automática");
    
    // Actualizar cada 3 segundos
    setInterval(function() {
        console.log("Verificando nuevos mensajes...");
        verificarNuevosMensajes();
    }, 3000);
}

// Verificar si hay mensajes nuevos sin recargar todos
function verificarNuevosMensajes() {
    // Usar timestamp para evitar caché del navegador
    const timestamp = new Date().getTime();
    fetch(`cargar_mensajes.php?id_chat=${idChat}&t=${timestamp}`)
        .then(respuesta => {
            if (!respuesta.ok) {
                throw new Error('Error en la respuesta: ' + respuesta.status);
            }
            return respuesta.json();
        })
        .then(datos => {
            console.log("Datos recibidos:", datos);
            if (!datos || datos.length === 0) return;
            
            // Verificar si hay mensajes nuevos
            const ultimoMensajeRecibido = Math.max(...datos.map(m => parseInt(m.id_mensaje)));
            
            if (ultimoMensajeRecibido > idUltimoMensaje) {
                console.log(`Hay mensajes nuevos. Último: ${ultimoMensajeRecibido}, Actual: ${idUltimoMensaje}`);
                // Hay mensajes nuevos, actualizar la vista
                mostrarMensajes(datos);
                // Actualizar ID del último mensaje
                idUltimoMensaje = ultimoMensajeRecibido;
            } else {
                console.log("No hay mensajes nuevos");
            }
        })
        .catch(error => {
            console.error('Error verificando mensajes:', error);
        });
}

// Configurar Socket.io
function iniciarSocket() {
    console.log("Iniciando conexión Socket.io");
    
    try {
        // Conectar al servidor Socket.io
        socket = io('http://192.168.18.24:3000');
        
        // Monitorear eventos de conexión
        socket.on('connect', function() {
            console.log('Conectado a Socket.io, ID:', socket.id);
            
            // Notificar conexión y unirse a sala
            socket.emit('usuario_conectado', {
                id_usuario: idUsuario,
                nombre_usuario: nombreUsuario
            });
            
            socket.emit('join_chat', idChat);
        });
        
        // Escuchar nuevos mensajes
        socket.on('receive_message', function(data) {
            console.log('Mensaje recibido (receive_message):', data);
            if (data.chatId == idChat) {
                verificarNuevosMensajes();
            }
        });
        
        // También escuchar este evento para mayor compatibilidad
        socket.on('mensaje_recibido', function(data) {
            console.log('Mensaje recibido (mensaje_recibido):', data);
            verificarNuevosMensajes();
        });
        
        // Manejar errores de conexión
        socket.on('connect_error', function(error) {
            console.error('Error de conexión Socket.io:', error);
        });
    } catch (error) {
        console.error('Error inicializando Socket.io:', error);
    }
}

// Cargar mensajes - función inicial
function cargarMensajes() {
    console.log("Cargando mensajes iniciales...");
    
    fetch(`cargar_mensajes.php?id_chat=${idChat}`)
        .then(respuesta => {
            if (!respuesta.ok) {
                throw new Error('Error en la respuesta: ' + respuesta.status);
            }
            return respuesta.json();
        })
        .then(datos => {
            console.log("Mensajes iniciales:", datos);
            if (!datos || datos.length === 0) return;
            
            // Mostrar mensajes
            mostrarMensajes(datos);
            
            // Actualizar ID del último mensaje
            if (datos.length > 0) {
                idUltimoMensaje = Math.max(...datos.map(m => parseInt(m.id_mensaje)));
                console.log("ID del último mensaje:", idUltimoMensaje);
            }
        })
        .catch(error => {
            console.error('Error cargando mensajes iniciales:', error);
        });
}

// Mostrar mensajes en la interfaz
function mostrarMensajes(mensajes) {
    // Obtener el contenedor de mensajes
    const contenedorMensajes = document.getElementById('chat-messages');
    
    // Limpiar contenedor
    contenedorMensajes.innerHTML = '';
    
    // Mostrar todos los mensajes
    mensajes.forEach(mensaje => {
        // IMPORTANTE: Respetar el tipo_emisor para las clases CSS
        const divMensaje = document.createElement('div');
        const esAdmin = mensaje.tipo_emisor === 'admin';
        divMensaje.className = esAdmin ? 'mensaje-admin' : 'mensaje-usuario';
        
        let contenido = `<div class="${esAdmin ? 'burbuja-admin' : 'burbuja-usuario'}">`;
        
        // Mostrar imagen o texto
        if (mensaje.imagen_url) {
            contenido += `<img src="../../${mensaje.imagen_url}" class="imagen-mensaje" onclick="window.open('../../${mensaje.imagen_url}', '_blank')">`;
        }
        
        if (mensaje.mensaje && mensaje.mensaje !== 'Imagen enviada') {
            contenido += `${mensaje.mensaje}`;
        }
        
        // Mostrar hora
        const hora = new Date(mensaje.fecha_envio).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        contenido += `<div class="hora-mensaje">${hora}</div>`;
        
        contenido += '</div>';
        
        divMensaje.innerHTML = contenido;
        contenedorMensajes.appendChild(divMensaje);
    });
    
    // Scroll al final
    const chatBox = document.getElementById('chat-body');
    chatBox.scrollTop = chatBox.scrollHeight;
}

// Mandar mensaje de texto
document.getElementById('chat-form').addEventListener('submit', function(evento) {
    evento.preventDefault();
    
    const campoMensaje = document.getElementById('message-input');
    const textoMensaje = campoMensaje.value.trim();
    
    if (textoMensaje) {
        const datosFormulario = new FormData();
        datosFormulario.append('id_chat', idChat);
        datosFormulario.append('mensaje', textoMensaje);
        
        const botonEnviar = document.querySelector('#chat-form button[type="submit"]');
        botonEnviar.disabled = true;
        
        fetch('guardar_mensaje.php', {
            method: 'POST',
            body: datosFormulario
        })
        .then(respuesta => respuesta.json())
        .then(datos => {
            if (datos.success) {
                campoMensaje.value = '';
                
                // Emitir mensaje a través de Socket.io
                if (socket && socket.connected) {
                    const mensajeSocket = {
                        id_usuario: idUsuario,
                        nombre_usuario: nombreUsuario,
                        mensaje: textoMensaje
                    };
                    console.log('Enviando mensaje a través de Socket.io:', mensajeSocket);
                    socket.emit('enviar_mensaje_usuario', mensajeSocket);
                }
                
                // Recargar mensajes inmediatamente
                verificarNuevosMensajes();
            }
            botonEnviar.disabled = false;
        })
        .catch(error => {
            console.error('Error enviando mensaje:', error);
            botonEnviar.disabled = false;
        });
    }
});

// Subir imagen
document.getElementById('image-input').addEventListener('change', function(evento) {
    const archivo = evento.target.files[0];
    if (archivo) {
        const datosImagen = new FormData();
        datosImagen.append('image', archivo);
        datosImagen.append('chat_id', idChat);
        
        fetch('subir_imagen.php', {
            method: 'POST',
            body: datosImagen
        })
        .then(respuesta => respuesta.json())
        .then(datos => {
            if (datos.success) {
                const datosMensaje = new FormData();
                datosMensaje.append('id_chat', idChat);
                datosMensaje.append('mensaje', '');
                datosMensaje.append('imagen_url', datos.url);
                
                return fetch('guardar_mensaje.php', {
                    method: 'POST',
                    body: datosMensaje
                });
            }
        })
        .then(respuesta => {
            if (respuesta) return respuesta.json();
        })
        .then(datos => {
            if (datos && datos.success) {
                // Notificar sobre la imagen a través de Socket.io
                if (socket && socket.connected) {
                    const mensajeSocket = {
                        id_usuario: idUsuario,
                        nombre_usuario: nombreUsuario,
                        mensaje: '[Imagen]'
                    };
                    console.log('Enviando notificación de imagen:', mensajeSocket);
                    socket.emit('enviar_mensaje_usuario', mensajeSocket);
                }
                
                // Recargar mensajes para mostrar la imagen
                verificarNuevosMensajes();
            }
            document.getElementById('image-input').value = '';
        })
        .catch(error => {
            console.error('Error subiendo imagen:', error);
            document.getElementById('image-input').value = '';
        });
    }
});
</script>

<?php 
include "../includes/sidebar.php";
?>