<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php';

// Verificamos que solo el rol de 'recursos' pueda ver esta lista
if ($usuario_auth['rol'] !== 'recursos') {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para ver el panel de usuarios."]);
    exit();
}

try {
    // LA SOLUCIÓN: Agregamos "WHERE activo = 1" para que el servidor YA NO mande a los eliminados
    $query = "SELECT id, nombre, correo, rol FROM usuarios WHERE activo = 1 ORDER BY id DESC";
    $stmt = $conexion->prepare($query);
    $stmt->execute();

    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($usuarios);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener usuarios: " . $e->getMessage()]);
}
?>