<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once 'conexion.php';

try {
    $dashboard = [];

    // --- ESTADÍSTICA 1: Total de productos (Solo activos) ---
    $stmt1 = $conexion->query("SELECT COUNT(*) as total_productos FROM productos WHERE activo = 1");
    $dashboard['total_productos'] = $stmt1->fetch(PDO::FETCH_ASSOC)['total_productos'];

    // --- ESTADÍSTICA 2: Alertas de Bajo Stock (10 piezas o menos, solo activos) ---
    $stmt2 = $conexion->query("SELECT id, nombre, stock FROM productos WHERE stock <= 10 AND activo = 1 ORDER BY stock ASC");
    $dashboard['alertas_stock'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // --- ESTADÍSTICA 3: Los últimos 5 movimientos (Aquí sí mostramos el historial de todos) ---
    $stmt3 = $conexion->query("SELECT p.nombre as producto, m.tipo, m.cantidad, m.fecha 
                               FROM movimientos m 
                               INNER JOIN productos p ON m.producto_id = p.id 
                               ORDER BY m.fecha DESC LIMIT 5");
    $dashboard['movimientos_recientes'] = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // Devolvemos el paquete completo
    http_response_code(200);
    echo json_encode($dashboard);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al generar el dashboard: " . $e->getMessage()]);
}
?>