<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'conexion.php';

try {
    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        $busqueda = "%" . $_GET['buscar'] . "%";
        
        // Agregamos la condición p.activo = 1
        $query = "SELECT p.id, p.nombre, p.precio, p.stock, c.nombre as categoria 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  WHERE p.nombre LIKE :busqueda AND p.activo = 1";
                  
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(":busqueda", $busqueda);
    } else {
        // Agregamos la condición p.activo = 1
        $query = "SELECT p.id, p.nombre, p.precio, p.stock, c.nombre as categoria 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  WHERE p.activo = 1";
                  
        $stmt = $conexion->prepare($query);
    }
    
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($productos);

} catch(PDOException $e) {
    echo json_encode(["error" => "Hubo un problema: " . $e->getMessage()]);
}
?>