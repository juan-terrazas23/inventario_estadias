<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php';

// Solo administradores pueden ver métricas gerenciales
if ($usuario_auth['rol'] !== 'recursos') {
    http_response_code(403);
    echo json_encode(["error" => "Acceso restringido al panel de administración."]);
    exit();
}

try {
    // 1. Top productos más solicitados en salidas
    $queryProductos = "SELECT p.nombre, SUM(m.cantidad) as total_pedido 
                       FROM movimientos m 
                       INNER JOIN productos p ON m.producto_id = p.id 
                       WHERE m.tipo = 'salida' 
                       GROUP BY m.producto_id 
                       ORDER BY total_pedido DESC 
                       LIMIT 5";
    $stmtP = $conexion->prepare($queryProductos);
    $stmtP->execute();
    $top_productos = $stmtP->fetchAll(PDO::FETCH_ASSOC);

    // 2. Áreas que consumen más material
    $queryAreas = "SELECT a.nombre as area, SUM(m.cantidad) as total_consumido 
                   FROM movimientos m 
                   INNER JOIN areas a ON m.area_id = a.id 
                   WHERE m.tipo = 'salida' 
                   GROUP BY m.area_id 
                   ORDER BY total_consumido DESC 
                   LIMIT 5";
    $stmtA = $conexion->prepare($queryAreas);
    $stmtA->execute();
    $top_areas = $stmtA->fetchAll(PDO::FETCH_ASSOC);

    // 3. Productos con stock bajo (ej. stock menor o igual a 10)
    // --- 3. PRODUCTOS CON STOCK BAJO (ACTUALIZADO A STOCK DINÁMICO) ---
    $queryStockBajo = "SELECT nombre, stock, stock_minimo 
                       FROM productos 
                       WHERE stock <= stock_minimo AND activo = 1 
                       ORDER BY stock ASC 
                       LIMIT 10";
    $stmtStockBajo = $conexion->prepare($queryStockBajo);
    $stmtStockBajo->execute();
    $estadisticas['stock_bajo'] = $stmtStockBajo->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener estadísticas: " . $e->getMessage()]);
}
?>