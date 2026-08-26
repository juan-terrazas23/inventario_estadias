<?php
// 1. Cabeceras (CORS) - Aquí permitimos específicamente el método POST
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once 'conexion.php';
require_once 'verificar_token.php'; // <-- AQUÍ PONEMOS AL GUARDIA EN LA PUERTA

// ¡NUEVO NIVEL DE SEGURIDAD RBAC! 
// Si el usuario es legítimo pero es de almacén, lo rebotamos (403 = Prohibido)
if ($usuario_auth['rol'] !== 'recursos') {
    http_response_code(403); 
    echo json_encode(["error" => "No tienes permisos de Recursos para registrar proveedores."]);
    exit();
}

// 2. Verificamos que realmente nos estén enviando datos por POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 3. Atrapamos el paquete JSON que nos envía el frontend
    $datos_recibidos = file_get_contents("php://input");
    
    // Lo decodificamos para que PHP lo entienda
    $datos = json_decode($datos_recibidos);

    // 4. Verificamos que no nos envíen datos vacíos
    if (!empty($datos->nombre) && !empty($datos->precio) && !empty($datos->categoria_id)) {
        
        try {
            // 5. Preparamos la consulta (¡Usamos :etiquetas por seguridad contra hackers!)
            $query = "INSERT INTO productos (nombre, precio, stock, categoria_id) 
                      VALUES (:nombre, :precio, :stock, :categoria_id)";
            
            $stmt = $conexion->prepare($query);
            
            // Si no nos envían stock inicial, le ponemos 0 por defecto
            $stock_inicial = !empty($datos->stock) ? $datos->stock : 0;
            
            // 6. Vinculamos las variables de forma segura
            $stmt->bindParam(":nombre", $datos->nombre);
            $stmt->bindParam(":precio", $datos->precio);
            $stmt->bindParam(":stock", $stock_inicial);
            $stmt->bindParam(":categoria_id", $datos->categoria_id);
            
            // 7. ¡Ejecutamos el guardado!
            if($stmt->execute()) {
                // Le respondemos a tu compañera que todo salió bien (Código HTTP 201: Creado)
                http_response_code(201);
                echo json_encode(["mensaje" => "Producto creado exitosamente."]);
            }
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "No se pudo crear el producto: " . $e->getMessage()]);
        }
    } else {
        // Si falta algún dato importante (ej. olvidaron poner el precio)
        http_response_code(400); // 400 = Bad Request (Petición incorrecta)
        echo json_encode(["error" => "Faltan datos. Nombre, precio y categoria_id son obligatorios."]);
    }
} else {
    // Si alguien intenta abrir este archivo en el navegador directamente (GET)
    http_response_code(405); // 405 = Method Not Allowed
    echo json_encode(["error" => "Método no permitido. Solo se acepta POST."]);
}
?>