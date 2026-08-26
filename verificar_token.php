<?php
// 1. Obtenemos las cabeceras (Headers) que envía el frontend o Postman
$headers = apache_request_headers();

// 2. Revisamos si enviaron la cabecera de "Authorization"
if (!isset($headers['Authorization'])) {
    http_response_code(401); // 401 = No Autorizado
    echo json_encode(["error" => "Acceso denegado. No se detectó un token de seguridad."]);
    exit(); // El exit() es la magia: detiene el código por completo aquí mismo.
}

// 3. Limpiamos el texto. Normalmente el token llega diciendo "Bearer asdf123..."
$token = str_replace("Bearer ", "", $headers['Authorization']);

// 4. Vamos a la base de datos a ver si ese token existe y de quién es
$query = "SELECT id, nombre, rol FROM usuarios WHERE token = :token";
$stmt = $conexion->prepare($query);
$stmt->bindParam(":token", $token);
$stmt->execute();
$usuario_auth = $stmt->fetch(PDO::FETCH_ASSOC);

// 5. Si no se encontró el token, es un intruso o el token ya caducó
if (!$usuario_auth) {
    http_response_code(401);
    echo json_encode(["error" => "Token inválido o expirado. Por favor inicia sesión nuevamente."]);
    exit();
}

// Si el código llega hasta esta línea, significa que el token es válido.
// Además, la variable $usuario_auth queda disponible para saber quién hizo la acción.
?>