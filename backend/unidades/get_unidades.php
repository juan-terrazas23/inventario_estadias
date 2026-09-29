<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/conexion.php';

try {
    // Traemos las unidades de medida con su abreviatura
    $query = "SELECT id, nombre, abreviatura FROM unidades_medida ORDER BY id ASC";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    
    $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    http_response_code(200);
    echo json_encode($unidades);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener unidades de medida: " . $e->getMessage()]);
}
?>