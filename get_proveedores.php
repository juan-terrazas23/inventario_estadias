<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'conexion.php';

try {
    $query = "SELECT id, empresa, contacto, telefono, correo, direccion 
              FROM proveedores 
              WHERE activo = 1 
              ORDER BY empresa ASC";
              
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($proveedores);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener proveedores: " . $e->getMessage()]);
}
?>