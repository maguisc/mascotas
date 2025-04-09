<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../../../config/database.php';

// Verificar si ya existe un administrador
$sql = "SELECT COUNT(*) as total FROM administrador";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if($row['total'] > 0) {
    // Si ya existe un administrador, redirigir al login
    header("Location: ../login/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="text-center mb-0">Registro de Administrador</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        if(isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                            unset($_SESSION['error']);
                        }
                        ?>
                        <form id="registroForm" action="procesar_registro.php" method="POST" onsubmit="return validarFormulario()">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div id="passwordHelp" class="form-text">
                                    La contraseña debe contener:
                                    <ul class="requirements-list">
                                        <li id="length" class="invalid">Mínimo 8 caracteres</li>
                                        <li id="uppercase" class="invalid">Una letra mayúscula</li>
                                        <li id="lowercase" class="invalid">Una letra minúscula</li>
                                        <li id="number" class="invalid">Un número</li>
                                        <li id="special" class="invalid">Un carácter especial</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Registrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function validarPassword(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        for (const [requirement, met] of Object.entries(requirements)) {
            const li = document.getElementById(requirement);
            if (met) {
                li.classList.remove('invalid');
                li.classList.add('valid');
            } else {
                li.classList.remove('valid');
                li.classList.add('invalid');
            }
        }

        return Object.values(requirements).every(Boolean);
    }

    function validarFormulario() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (!validarPassword(password)) {
            alert('La contraseña no cumple con todos los requisitos');
            return false;
        }

        if (password !== confirmPassword) {
            alert('Las contraseñas no coinciden');
            return false;
        }

        return true;
    }

    document.getElementById('password').addEventListener('input', function() {
        validarPassword(this.value);
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>