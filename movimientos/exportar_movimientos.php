<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/conexion.php';
require_once '../auth/verificar_token.php';

// Validar que solo el Administrador pueda descargar el reporte completo del historial
if ($usuario_auth['rol'] !== 'administrador' && $usuario_auth['rol'] !== 'recursos') {
    http_response_code(403);
    echo json_encode(["error" => "No tienes permisos para exportar este reporte."]);
    exit();
}

try {
    // Consulta con LEFT JOIN para traer los nombres legibles de áreas, proveedores y productos
    $query = "SELECT m.id, p.nombre as producto, m.tipo, m.cantidad, 
                     COALESCE(a.nombre, 'N/A') as area, 
                     COALESCE(pr.empresa, 'N/A') as proveedor, 
                     m.persona_responsable, m.motivo, m.fecha
              FROM movimientos m
              INNER JOIN productos p ON m.producto_id = p.id
              LEFT JOIN areas a ON m.area_id = a.id
              LEFT JOIN proveedores pr ON m.proveedor_id = pr.id
              ORDER BY m.fecha DESC";

    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Configurar las cabeceras HTTP para forzar la descarga de un archivo CSV compatible con Excel
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=historial_movimientos_congreso.csv');

    // Abrir la salida estándar de PHP para escribir el archivo
    $output = fopen('php://output', 'w');

    // Añadir el BOM de UTF-8 para que Excel reconozca bien los acentos (ñ, tildes, etc.)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Escribir la fila de encabezados de las columnas
    fputcsv($output, ['ID Movimiento', 'Producto', 'Tipo de Movimiento', 'Cantidad', 'Área Destino', 'Proveedor', 'Persona Responsable (¿Quién vino?)', 'Motivo', 'Fecha y Hora']);

    // Escribir cada fila del historial obtenida de la base de datos
    foreach ($movimientos as $row) {
        fputcsv($output, [
            $row['id'],
            $row['producto'],
            strtoupper($row['tipo']),
            $row['cantidad'],
            $row['area'],
            $row['proveedor'],
            $row['persona_responsable'],
            $row['motivo'],
            $row['fecha']
        ]);
    }

    fclose($output);
    exit();

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al generar el reporte: " . $e->getMessage()]);
}
?>