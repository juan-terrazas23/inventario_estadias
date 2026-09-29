<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/conexion.php';

try {
    // AHORA ES INTELIGENTE: Compara el stock contra el stock_minimo de cada producto
    $query = "SELECT nombre, stock, stock_minimo FROM productos WHERE stock <= stock_minimo AND activo = 1";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $productosCriticos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($productosCriticos) > 0) {
        
        // Obtener correos dinámicamente de los administradores
        $queryAdmins = "SELECT correo FROM usuarios WHERE rol = 'recursos' AND activo = 1";
        $stmtAdmins = $conexion->prepare($queryAdmins);
        $stmtAdmins->execute();
        $admins = $stmtAdmins->fetchAll(PDO::FETCH_ASSOC);

        $correos_destino = array_column($admins, 'correo');
        
        if (count($correos_destino) > 0) {
            $para = implode(", ", $correos_destino); 
        } else {
            $para = "solanohelena40@gmail.com";
        }

        $asunto = "⚠️ ALERTA CRÍTICA: Productos con stock bajo en el Almacén";
        
        $mensaje = "Hola, equipo de Recursos / Administración.\n\nEl sistema de inventario ha detectado los siguientes productos con stock bajo que requieren reabastecimiento urgente:\n\n";
        
        foreach ($productosCriticos as $prod) {
            // El correo ahora te dice cuál era el mínimo permitido
            $mensaje .= "- " . $prod['nombre'] . " | Stock actual: " . $prod['stock'] . " (Mínimo requerido: " . $prod['stock_minimo'] . ")\n";
        }
        
        $mensaje .= "\nPor favor, gestione las compras con los proveedores correspondientes.\nSistema Automatizado del Congreso.";
        
        $cabeceras = "From: noreply@inventario-congreso.com\r\n" .
                     "X-Mailer: PHP/" . phpversion();

        @mail($para, $asunto, $mensaje, $cabeceras);

        http_response_code(200);
        echo json_encode([
            "alerta" => "Se detectó escasez y se disparó la función de correo.",
            "destinatarios_encontrados" => $para, 
            "productos_afectados" => count($productosCriticos),
            "vista_previa_del_correo" => $mensaje
        ]);
    } else {
        http_response_code(200);
        echo json_encode(["mensaje" => "Los niveles de stock se encuentran estables. No hay alertas."]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al verificar alertas: " . $e->getMessage()]);
}
?>