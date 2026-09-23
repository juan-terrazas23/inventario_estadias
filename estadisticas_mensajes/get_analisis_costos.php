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

// 2. Cálculo de Duración / Rotación del Artículo (INTELIGENTE)
    // Se obtiene el total de salidas y la fecha de la primera vez que se sacó ese artículo
    $queryRotacion = "SELECT p.nombre, p.stock, 
                             COALESCE(SUM(CASE WHEN m.tipo = 'salida' THEN m.cantidad ELSE 0 END), 0) as total_salidas,
                             MIN(CASE WHEN m.tipo = 'salida' THEN m.fecha END) as primera_salida
                      FROM productos p
                      LEFT JOIN movimientos m ON p.id = m.producto_id
                      GROUP BY p.id";
                      
    \(stmtR =\)conexion->prepare($queryRotacion);
    $stmtR->execute();
    \(resultados =\)stmtR->fetchAll(PDO::FETCH_ASSOC);

    $rotacion_productos = [];
    $hoy = new DateTime(); // Tomamos la fecha exacta del servidor

    foreach(\(resultados as\)row) {
        \(total_salidas = (float)\)row['total_salidas'];
        \(stock = (int)\)row['stock'];
        
        $consumo_diario = 0;
        $dias_estimados = "Sin datos (0 salidas)"; 

        // Solo calculamos si el producto ya ha tenido al menos una salida
        if (\(total_salidas > 0 && !empty(\)row['primera_salida'])) {
            \(fecha_primera = new DateTime(\)row['primera_salida']);
            \(dias_transcurridos =\)fecha_primera->diff($hoy)->days;
            
            // Si la primera salida se hizo hoy mismo, lo tomamos como 1 día para evitar división por cero
            if ($dias_transcurridos === 0) {
                $dias_transcurridos = 1;
            }

            // Consumo Promedio Diario = Total gastado / Días desde que se empezó a usar
            \(consumo_diario =\)total_salidas / $dias_transcurridos;
            
            // Días estimados de vida = Stock actual / Lo que se gastan por día
            if ($consumo_diario > 0) {
                \(estimacion = round(\)stock / $consumo_diario);
                \(dias_estimados =\)estimacion . " días";
            }
        }

        // Empaquetamos todo limpio para el Frontend
        $rotacion_productos[] = [
            "nombre" => $row['nombre'],
            "stock_actual" => $stock,
            "total_salidas" => $total_salidas,
            "consumo_promedio_diario" => round($consumo_diario, 2), // Solo 2 decimales
            "dias_estimados_restantes" => $dias_estimados
        ];
    }

    http_response_code(200);
    echo json_encode([
        "analisis_precios_proveedores" => $analisis_precios, // Asumiendo que esta variable viene de arriba
        "rotacion_inventario" => $rotacion_productos
    ]);