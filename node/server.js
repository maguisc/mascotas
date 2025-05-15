const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const mysql = require('mysql');
const cors = require('cors');

// Configuración de la base de datos
const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'mascotas'
});

// Conexión a la base de datos
db.connect(err => {
    if (err) {
        console.error('Error conectando a la base de datos:', err);
        return;
    }
    console.log('Conectado a la base de datos MySQL');
});

// Configuración de Express
const app = express();
app.use(cors());
app.use(express.json());

// Crear servidor HTTP
const server = http.createServer(app);

// Configuración de Socket.IO
const io = socketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

// Función auxiliar para emitir mensajes
function emitirMensaje(evento, sala, datos) {
    if (sala) {
        console.log(`Emitiendo ${evento} a sala ${sala}:`, datos);
        io.to(sala).emit(evento, datos);
    } else {
        console.log(`Emitiendo ${evento} a todos:`, datos);
        io.emit(evento, datos);
    }
}

// Manejar conexiones de Socket.IO
io.on('connection', (socket) => {
    console.log('Nuevo cliente conectado:', socket.id);

    // Log de eventos para depuración
    const originalOn = socket.on;
    socket.on = function(event, handler) {
        const wrappedHandler = function(...args) {
            console.log(`[EVENTO RECIBIDO] ${event}`, args);
            return handler.apply(this, args);
        };
        originalOn.call(this, event, wrappedHandler);
        return this;
    };

    // MENSAJES DE USUARIO
    socket.on('enviar_mensaje_usuario', (data) => {
        console.log('Mensaje recibido desde usuario:', data);

        // Validar datos
        if (!data.id_usuario || !data.mensaje) {
            console.error('Datos incompletos en mensaje de usuario');
            return;
        }

        // Buscar chat del usuario
        const buscarChatQuery = `SELECT * FROM chats WHERE id_usuario = ? LIMIT 1`;
        db.query(buscarChatQuery, [data.id_usuario], (err, result) => {
            if (err || result.length === 0) {
                console.error('Error buscando chat del usuario:', err || 'No encontrado');
                return;
            }

            const chat = result[0];
            // Insertar mensaje en la base de datos
            const mensajeInsertar = `
                INSERT INTO mensajes 
                (id_chat, id_emisor, tipo_emisor, mensaje, nombre_emisor, email_emisor) 
                VALUES (?, ?, ?, ?, ?, ?)
            `;

            db.query(
                mensajeInsertar,
                [
                    chat.id_chat,
                    data.id_usuario,
                    'usuario',
                    data.mensaje,
                    data.nombre_usuario || '',
                    ''
                ],
                (err, resultMsg) => {
                    if (err) {
                        console.error('Error guardando mensaje del usuario:', err);
                        return;
                    }

                    // Actualizar timestamp del chat
                    const actualizarChat = `
                        UPDATE chats SET ultimo_mensaje = ?, fecha_ultimo_mensaje = NOW() WHERE id_chat = ?
                    `;
                    db.query(actualizarChat, [data.mensaje, chat.id_chat]);

                    // Mensaje para web (salas específicas)
                    const mensajeWeb = {
                        chatId: chat.id_chat,
                        userId: data.id_usuario,
                        tipo_emisor: 'usuario',
                        mensaje: data.mensaje,
                        userName: data.nombre_usuario || '',
                        messageId: resultMsg.insertId,
                        timestamp: new Date()
                    };
                    
                    // Enviar a la sala específica (solo para web)
                    emitirMensaje('receive_message', chat.id_chat.toString(), mensajeWeb);
                    
                    // Mensaje para app móvil
                    const mensajeApp = {
                        id_usuario: data.id_usuario,
                        nombre_usuario: data.nombre_usuario || '',
                        mensaje: data.mensaje,
                        tipo_emisor: 'usuario',
                        fecha: new Date().toISOString()
                    };
                    
                    // IMPORTANTE: Evitamos duplicación en app móvil
                    // Solo emitimos mensaje_recibido, no recibe_mensaje
                    emitirMensaje('mensaje_recibido', null, mensajeApp);
                }
            );
        });
    });

    // MENSAJES DE ADMIN
    socket.on('send_message', async (messageData) => {
        console.log('Nuevo mensaje (admin):', messageData);
        
        // Validar datos
        if (!messageData.chatId || !(messageData.mensaje || messageData.message)) {
            console.error('Datos incompletos en mensaje de admin');
            return;
        }
        
        const mensajeTexto = messageData.message || messageData.mensaje;
        
        // Guardar mensaje en la base de datos
        const query = `
            INSERT INTO mensajes 
            (id_chat, id_emisor, tipo_emisor, mensaje, nombre_emisor, email_emisor) 
            VALUES (?, ?, ?, ?, ?, ?)
        `;

        db.query(
            query, 
            [
                messageData.chatId, 
                messageData.userId || 0,
                'admin',
                mensajeTexto,
                messageData.userName || 'Administrador',
                messageData.userEmail || ''
            ],
            (err, result) => {
                if (err) {
                    console.error('Error al guardar mensaje:', err);
                    return;
                }

                // Actualizar último mensaje del chat
                const updateQuery = `
                    UPDATE chats 
                    SET ultimo_mensaje = ?, 
                        fecha_ultimo_mensaje = NOW() 
                    WHERE id_chat = ?
                `;

                db.query(updateQuery, [mensajeTexto, messageData.chatId]);

                // Mensaje para web (salas específicas)
                const mensajeWeb = {
                    chatId: messageData.chatId,
                    userId: messageData.userId || 0,
                    tipo_emisor: 'admin',
                    mensaje: mensajeTexto,
                    userName: messageData.userName || 'Administrador',
                    messageId: result.insertId,
                    timestamp: new Date()
                };
                
                // Solo emitir a la sala específica
                emitirMensaje('receive_message', messageData.chatId.toString(), mensajeWeb);

                // Buscar id_usuario para esta sala para enviar mensaje_recibido
                db.query('SELECT id_usuario FROM chats WHERE id_chat = ?', [messageData.chatId], (err, results) => {
                    if (err || results.length === 0) {
                        console.error('Error obteniendo id_usuario:', err || 'No encontrado');
                        return;
                    }
                    
                    const id_usuario = results[0].id_usuario;
                    
                    // Mensaje para app móvil
                    const mensajeApp = {
                        id_usuario: id_usuario,
                        nombre_usuario: messageData.userName || 'Administrador',
                        mensaje: mensajeTexto,
                        tipo_emisor: 'admin',  // IMPORTANTE: marcar como admin
                        fecha: new Date().toISOString()
                    };
                    
                    // Emitir a todos los clientes
                    emitirMensaje('mensaje_recibido', null, mensajeApp);
                });
            }
        );
    });

    // Unirse a una sala de chat
    socket.on('join_chat', (chatId) => {
        if (!chatId) return;
        
        const chatIdStr = chatId.toString();
        socket.join(chatIdStr);
        console.log(`Usuario ${socket.id} se unió al chat ${chatIdStr}`);
    });

    // Eventos de conexión/desconexión
    socket.on('usuario_conectado', (userData) => {
        console.log('Usuario conectado a chat:', userData);
    });

    socket.on('usuario_desconectado', (userId) => {
        console.log('Usuario desconectado del chat:', userId);
    });

    socket.on('disconnect', () => {
        console.log('Usuario desconectado:', socket.id);
    });
});

const PORT = 3000;
server.listen(PORT, () => {
    console.log(`Servidor corriendo en puerto ${PORT}`);
});