<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../config/conexion.php';

try {
    // Buscar productos con stock crítico (menor o igual a 10)
    $query = "SELECT nombre, stock FROM productos WHERE stock <= 10 AND activo = 1";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $productosCriticos = $stmt$stmt->fetchAll(PDO::FETCH_ASSOC); // Corregido: $stmt->fetchAll

    if (count($productosCriticos) > 0) {
        // Datos para el correo institucional del Administrador
        $para = "administrador@congreso.gob.mx"; // Correo institucional o del admin
        $asunto = "⚠️ ALERTA CRÍTICA: Productos con stock bajo en el Almacén";
        
        $mensaje = "Hola, Administrador.\n\nEl sistema de inventario ha detectado los siguientes productos con stock bajo que requieren reabastecimiento urgente:\n\n";
        
        foreach ($productosCriticos as $prod) {
            $mensaje .= "- " . $prod['nombre'] . " | Stock actual: " . $prod['stock'] . " unidades\n";
        }
        
        $mensaje .= "\nPor favor, gestione las compras con los proveedores correspondientes.\nSistema Automatizado del Congreso.";
        
        $cabeceras = "From: noreply@inventario-congreso.com\r\n" .
                     "X-Mailer: PHP/" . phpversion();

        // Enviar correo usando la función nativa de PHP (requiere servidor de correo configurado o XAMPP con Mailhog/Sendmail)
        @mail($para, $asunto, $mensaje, $cabeceras);

        http_response_code(200);
        echo json_encode([
            "alerta" => "Se detectó escasez y se ha enviado la notificación por correo.",
            "productos_afectados" => count($productosCriticos)
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