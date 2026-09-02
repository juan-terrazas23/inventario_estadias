<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'conexion.php';

try {
    // 1. Recibir parámetros por URL (Por defecto: página 1, 10 registros por página)
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
    
    // Calcular desde dónde empezar a cortar los datos (Offset)
    $offset = ($pagina - 1) * $limite;

    // 2. Averiguar cuántos registros existen en total en la tabla
    $queryTotal = "SELECT COUNT(*) as total FROM movimientos";
    $stmtTotal = $conexion->prepare($queryTotal);
    $stmtTotal->execute();
    $filaTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
    $totalRegistros = $filaTotal['total'];
    
    // Calcular el total de páginas (redondeando hacia arriba)
    $totalPaginas = ceil($totalRegistros / $limite);

    // 3. Traer SOLO los registros de la página solicitada
    $query = "SELECT m.id, p.nombre as producto, m.tipo, m.cantidad, m.fecha, m.motivo
              FROM movimientos m 
              INNER JOIN productos p ON m.producto_id = p.id 
              ORDER BY m.fecha DESC"; // Los más nuevos primero
              
    $stmt = $conexion->prepare($query);
    
    // NOTA: Para LIMIT y OFFSET es obligatorio usar bindParam con PDO::PARAM_INT
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($movimientos);

} catch(PDOException $e) {
    echo json_encode(["error" => "Hubo un problema al obtener el historial: " . $e->getMessage()]);
}
?>