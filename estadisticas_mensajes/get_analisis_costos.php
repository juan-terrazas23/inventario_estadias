<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php';

if ($usuario_auth['rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(["error" => "Acceso restringido."]);
    exit();
}

try {
    // 1. Análisis de Precios por Proveedor (asumiendo que las entradas registran qué proveedor surtió)
    $queryProveedores = "SELECT pr.empresa, p.nombre as producto, p.precio 
                         FROM movimientos m
                         INNER JOIN proveedores pr ON m.proveedor_id = pr.id
                         INNER JOIN productos p ON m.producto_id = p.id
                         WHERE m.tipo = 'entrada'
                         GROUP BY pr.id, p.id
                         ORDER BY p.precio ASC";
    $stmtPr = $conexion->prepare($queryProveedores);
    $stmtPr->execute();
    $analisis_precios = $stmtPr->fetchAll(PDO::FETCH_ASSOC);

    // 2. Cálculo de Duración / Rotación del Artículo
    // Esto calcula cuántas unidades se consumen en promedio por día para estimar "cuánto dura" el stock actual
    $queryRotacion = "SELECT p.nombre, p.stock, 
                             COALESCE(SUM(CASE WHEN m.tipo = 'salida' THEN m.cantidad ELSE 0 END), 0) as total_salidas
                      FROM productos p
                      LEFT JOIN movimientos m ON p.id = m.producto_id
                      GROUP BY p.id";
    $stmtR = $conexion->prepare($queryRotacion);
    $stmtR->execute();
    $rotacion_productos = $stmtR->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        "analisis_precios_proveedores" => $analisis_precios,
        "rotacion_inventario" => $rotacion_productos
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error en el análisis gerencial: " . $e->getMessage()]);
}
?>