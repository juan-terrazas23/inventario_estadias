<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'conexion.php';

try {
    // Verificamos si tu compañera nos está enviando una palabra para buscar
    if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
        $busqueda = "%" . $_GET['buscar'] . "%"; // Los % son comodines de MySQL
        
        $query = "SELECT p.id, p.nombre, p.precio, p.stock, c.nombre as categoria 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  WHERE p.nombre LIKE :busqueda"; // Filtramos por nombre
                  
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(":busqueda", $busqueda);
    } else {
        // Si no busca nada, le mandamos todos los productos (como lo tenías antes)
        $query = "SELECT p.id, p.nombre, p.precio, p.stock, c.nombre as categoria 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id";
                  
        $stmt = $conexion->prepare($query);
    }
    
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($productos);

} catch(PDOException $e) {
    echo json_encode(["error" => "Hubo un problema: " . $e->getMessage()]);
}
?>