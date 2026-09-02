<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Asegúrate de que la ruta a tu conexión sea la correcta según tu estructura
require_once '../config/conexion.php'; 

try {
    // 1. Parámetros de paginación por URL (Por defecto: página 1, 10 registros)
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;
    
    // Calcular el Offset (salto de registros)
    $offset = ($pagina - 1) * $limite;

    // 2. Contar el total de productos registrados en el catálogo
    $queryTotal = "SELECT COUNT(*) as total FROM productos";
    $stmtTotal = $conexion->prepare($queryTotal);
    $stmtTotal->execute();
    $filaTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
    $totalRegistros = $filaTotal['total'];
    
    // Calcular total de páginas
    $totalPaginas = ceil($totalRegistros / $limite);

    // 3. Traer SOLO los productos correspondientes a la página actual
    $query = "SELECT * FROM productos ORDER BY id DESC LIMIT :limite OFFSET :offset";
              
    $stmt = $conexion->prepare($query);
    
    // Se requiere PDO::PARAM_INT para que el LIMIT y OFFSET funcionen seguro
    $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Empaquetar la respuesta JSON (Datos + Paginación)
    http_response_code(200);
    echo json_encode([
        "datos" => $productos,
        "paginacion" => [
            "pagina_actual" => $pagina,
            "limite_por_pagina" => $limite,
            "total_registros" => $totalRegistros,
            "total_paginas" => $totalPaginas
        ]
    ]);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener productos: " . $e->getMessage()]);
}
?>