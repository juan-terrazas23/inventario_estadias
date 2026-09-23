<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

// Rutas ajustadas para salir de la subcarpeta Estadisticas_mensajes
require_once '../config/conexion.php';
require_once '../authverificar_token.php'; 

try {
    // Usar la autenticación global del proyecto ($usuario_auth)
    if (!isset($usuario_auth) || $usuario_auth['rol'] !== 'recursos') {
        http_response_code(403);
        echo json_encode(["error" => "Acceso denegado. Se requiere rol de recursos."]);
        exit();
    }

    $estadisticas = [];

    // --- 1. TOP 5 PRODUCTOS MÁS CONSUMIDOS (Optimizado) ---
    $queryTopProductos = "SELECT p.nombre, SUM(m.cantidad) as total_salidas 
                          FROM movimientos m 
                          JOIN productos p ON m.producto_id = p.id 
                          WHERE m.tipo = 'salida' AND p.activo = 1
                          GROUP BY p.id, p.nombre 
                          ORDER BY total_salidas DESC 
                          LIMIT 5";
    $stmt1 = $conexion->prepare($queryTopProductos);
    $stmt1->execute();
    $estadisticas['top_productos'] = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // --- 2. TOP 5 ÁREAS QUE MÁS PIDEN MATERIAL (Optimizada) ---
    $queryTopAreas = "SELECT area_destino, COUNT(*) as total_pedidos 
                      FROM movimientos 
                      WHERE tipo = 'salida' AND area_destino IS NOT NULL AND TRIM(area_destino) != ''
                      GROUP BY area_destino 
                      ORDER BY total_pedidos DESC 
                      LIMIT 5";
    $stmt2 = $conexion->prepare($queryTopAreas);
    $stmt2->execute();
    $estadisticas['top_areas'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // --- 3. ALERTA DE STOCK BAJO ---
    $queryStockBajo = "SELECT nombre, stock, stock_minimo 
                       FROM productos 
                       WHERE stock <= stock_minimo AND activo = 1 
                       ORDER BY stock ASC 
                       LIMIT 10";
    $stmt3 = $conexion->prepare($queryStockBajo);
    $stmt3->execute();
    $estadisticas['stock_bajo'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // Enviar el arreglo al frontend
    http_response_code(200);
    echo json_encode($estadisticas);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener estadísticas: " . $e->getMessage()]);
}
?>