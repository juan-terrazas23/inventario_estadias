// --- LÓGICA DE INTERFAZ ---
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

// --- LÓGICA DE AUTENTICACIÓN ---
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