<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/conexion.php'; 

try {
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
    
    $offset = ($pagina - 1) * $limite;

    $queryTotal = "SELECT COUNT(*) as total FROM movimientos";
    $stmtTotal = $conexion->prepare($queryTotal);
    $stmtTotal->execute();
    $filaTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
    $totalRegistros = $filaTotal['total'];
    
    // Calcular el total de páginas (redondeando hacia arriba)
    $totalPaginas = ceil($totalRegistros / $limite);

    // 3. --- EL CAMBIO MAESTRO: LEFT JOIN PARA ÁREAS Y PROVEEDORES ---
    $query = "SELECT m.id, p.nombre as producto, a.nombre as area, pr.empresa as proveedor, m.tipo, m.cantidad, m.fecha, m.motivo
              FROM movimientos m 
              INNER JOIN productos p ON m.producto_id = p.id 
              LEFT JOIN areas a ON m.area_id = a.id
              LEFT JOIN proveedores pr ON m.proveedor_id = pr.id
              ORDER BY m.fecha DESC 
              LIMIT :limite OFFSET :offset";
              
    $stmt = $conexion->prepare($query);
    
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Empaquetar la respuesta: Mandamos los datos Y la información de las páginas
    http_response_code(200);
    echo json_encode([
        "datos" => $movimientos,
        "paginacion" => [
            "pagina_actual" => $pagina,
            "limite_por_pagina" => $limite,
            "total_registros" => $totalRegistros,
            "total_paginas" => $totalPaginas
        ]
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Hubo un problema al obtener el historial: " . $e->getMessage()]);
}
?>

