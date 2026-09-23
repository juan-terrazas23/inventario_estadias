<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

// Rutas ajustadas para salir de la subcarpeta Estadisticas_mensajes
require_once '../config/conexion.php';
require_once '../auth/verificar_token.php'; 

try {
    // Usar la autenticación global del proyecto ($usuario_auth)
    if (!isset($usuario_auth) || $usuario_auth['rol'] !== 'recursos') {
        http_response_code(403);
        echo json_encode(["error" => "Acceso denegado. Se requiere rol de recursos."]);
        exit();
    }

    $analisis_precios = []; 

    // 2. Consulta Base de Rotación e Historial de Salidas (Optimizada)
    $queryRotacion = "SELECT p.id, p.nombre, p.stock, 
                             COALESCE(SUM(CASE WHEN m.tipo = 'salida' THEN m.cantidad ELSE 0 END), 0) as total_salidas,
                             MIN(CASE WHEN m.tipo = 'salida' THEN m.fecha END) as primera_salida
                      FROM productos p
                      LEFT JOIN movimientos m ON p.id = m.producto_id
                      WHERE p.activo = 1
                      GROUP BY p.id, p.nombre, p.stock";
                      
    $stmtR = $conexion->prepare($queryRotacion);
    $stmtR->execute();
    $resultados = $stmtR->fetchAll(PDO::FETCH_ASSOC);

    // Procesar los datos matemáticos
    $rotacion_productos = [];
    $hoy = new DateTime(); 

    foreach($resultados as $row) {
        $total_salidas = (float)$row['total_salidas'];
        $stock = (int)$row['stock'];
        
        $consumo_diario = 0;
        $dias_estimados = "Sin datos (0 salidas)"; 

        if ($total_salidas > 0 && !empty($row['primera_salida'])) {
            $fecha_primera = new DateTime($row['primera_salida']);
            $dias_transcurridos = $fecha_primera->diff($hoy)->days;
            
            if ($dias_transcurridos === 0) {
                $dias_transcurridos = 1; // Prevenir división por cero
            }

            $consumo_diario = $total_salidas / $dias_transcurridos;
            
            if ($consumo_diario > 0) {
                $estimacion = round($stock / $consumo_diario);
                $dias_estimados = $estimacion . " días";
            }
        }

        // Empaquetar con las variables exactas para el Frontend
        $rotacion_productos[] = [
            "nombre" => $row['nombre'],
            "stock_actual" => $stock,
            "total_salidas" => $total_salidas,
            "consumo_promedio_diario" => round($consumo_diario, 2),
            "dias_estimados_restantes" => $dias_estimados
        ];
    }

    // Respuesta final estructurada para el frontend
    http_response_code(200);
    echo json_encode([
        "analisis_precios_proveedores" => $analisis_precios, 
        "rotacion_inventario" => $rotacion_productos
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error en el análisis gerencial: " . $e->getMessage()]);
}
?>