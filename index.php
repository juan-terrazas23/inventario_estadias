<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>

<body class="login-body">

    <div class="login-card p-5 text-center">
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

            <button type="button" class="btn btn-custom w-100 py-2 mb-3" onclick="simularLogin()">
                Iniciar Sesión
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Conexión limpia a la lógica de login -->
    <script src="js/login.js?v=1"></script>
</body>

</html>