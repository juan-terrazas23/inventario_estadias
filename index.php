<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Inventario</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Nuestros Estilos Personalizados -->
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body class="login-body">

    <div class="login-card p-5 text-center">
        <!-- Ícono principal -->
        <i class="bi bi-box-seam login-icon"></i>

        <h4 class="fw-bold mb-1">Sistema de Inventario</h4>
        <p class="text-muted mb-4 small">Ingresa tus credenciales para continuar</p>

        <form id="form-login">
            <div class="input-group mb-3">
                <span class="input-group-text bg-white" style="border-color: #dcd7d0;">
                    <i class="bi bi-person text-muted"></i>
                </span>
                <input type="text" class="form-control" id="usuario" placeholder="Usuario" required>
            </div>

            <div class="input-group mb-4">
                <span class="input-group-text bg-white" style="border-color: #dcd7d0;">
                    <i class="bi bi-lock text-muted"></i>
                </span>
                <input type="password" class="form-control" id="password" placeholder="Contraseña" required>
                <button class="btn btn-outline-secondary bg-white text-muted" type="button" onclick="togglePassword()"
                    style="border-color: #dcd7d0;">
                    <i class="bi bi-eye" id="icono-ojo"></i>
                </button>
            </div>

            <!-- Botón de inicio de sesión -->
            <button type="button" class="btn btn-custom w-100 py-2 mb-3" onclick="simularLogin()">
                Iniciar Sesión
            </button>

            <a href="#" class="text-muted small text-decoration-none">¿Olvidaste tu contraseña?</a>
        </form>
    </div>

    <!-- SweetAlert para alertas bonitas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script de Autenticación -->
    <script>
        function togglePassword() {
            const inputPassword = document.getElementById('password');
            const iconoOjo = document.getElementById('icono-ojo');

            if (inputPassword.type === 'password') {
                inputPassword.type = 'text';
                iconoOjo.classList.remove('bi-eye');
                iconoOjo.classList.add('bi-eye-slash');
            } else {
                inputPassword.type = 'password';
                iconoOjo.classList.remove('bi-eye-slash');
                iconoOjo.classList.add('bi-eye');
            }
        }

        async function simularLogin() {
            const nombreUsuario = document.getElementById('usuario').value.trim();
            const passwordUsuario = document.getElementById('password').value.trim();

            if (nombreUsuario === '' || passwordUsuario === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Por favor, ingresa tu usuario y contraseña.',
                    confirmButtonColor: '#8c837b'
                });
                return;
            }

            try {
                const respuesta = await fetch('backend/auth/login.php', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nombre: nombreUsuario,
                        password: passwordUsuario
                    })
                });

                const resultado = await respuesta.json();

                if (respuesta.ok) {
                    // Guardamos el token
                    localStorage.setItem('token', resultado.token);
                    const rol = resultado.usuario.rol;

                    Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido!',
                        text: resultado.mensaje,
                        confirmButtonColor: '#8c837b',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirección con .php
                        if (rol === 'recursos') {
                            window.location.href = 'compras.php';
                        } else if (rol === 'almacen') {
                            window.location.href = 'almacenista.php';
                        } else {
                            window.location.href = 'index.php';
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Acceso Denegado',
                        text: resultado.error || 'Credenciales incorrectas.',
                        confirmButtonColor: '#8c837b'
                    });
                }
            } catch (error) {
                console.error('Error de conexión:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo conectar con el servidor backend.',
                    confirmButtonColor: '#8c837b'
                });
            }
        }
    </script>
</body>

</html>