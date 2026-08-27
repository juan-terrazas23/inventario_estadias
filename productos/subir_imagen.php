<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/conexion.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['imagen']) && isset($_POST['producto_id'])) {
        $producto_id = $_POST['producto_id'];
        $archivo = $_FILES['imagen'];

        // Validar extensiones permitidas
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionesPermitidas)) {
            http_response_code(400);
            echo json_encode(["error" => "Formato no válido. Solo JPG, PNG o WEBP."]);
            exit();
        }

        // Generar un nombre único para evitar que fotos con el mismo nombre se sobrescriban
        $nombreFinal = "prod_" . $producto_id . "_" . time() . "." . $extension;
        $rutaDestino = "../uploads/" . $nombreFinal;

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            // Guardar la referencia en la base de datos
            $query = "UPDATE productos SET imagen = :imagen WHERE id = :id";
            $stmt = $conexion->prepare($query);
            $stmt->bindParam(":imagen", $nombreFinal);
            $stmt->bindParam(":id", $producto_id);
            $stmt->execute();

            http_response_code(200);
            echo json_encode([
                "mensaje" => "Imagen subida con éxito.",
                "imagen_url" => $nombreFinal
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Error al guardar el archivo físico en el servidor."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Falta el archivo de imagen o el producto_id."]);
    }
}
?>