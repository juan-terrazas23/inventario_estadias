<?php
require_once 'conexion.php';

$nombre = "Juan P"; 
$correo = "juan@gmail.com";
$password_plana = "123345";
$rol = "almacen"; // ¡Aquí definimos su rol!

// Encriptamos la contraseña para que el login funcione
$password_encriptada = password_hash($password_plana, PASSWORD_DEFAULT);

try {
    // Fíjate que no le mandamos ID, MySQL le pondrá el 2 automáticamente
    $query = "INSERT INTO usuarios (nombre, correo, password, rol) VALUES (:nombre, :correo, :password, :rol)";
    
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(":nombre", $nombre);
    $stmt->bindParam(":correo", $correo);
    $stmt->bindParam(":password", $password_encriptada);
    $stmt->bindParam(":rol", $rol);
    
    if($stmt->execute()) {
        echo "¡Usuario Juan P (Almacén) creado con éxito y encriptado!";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>