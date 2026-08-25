<?php
require_once 'conexion.php';

$nombre = "Administrador Principal";
$correo = "admin@empresa.com";
$password_plana = "admin123"; // Esta es la que usarás para entrar

// ¡La función mágica de seguridad de PHP!
$password_encriptada = password_hash($password_plana, PASSWORD_DEFAULT);

try {
    $query = "INSERT INTO usuarios (nombre, correo, password) VALUES (:nombre, :correo, :password)";
    $stmt = $conexion->prepare($query);
    $stmt->bindParam(":nombre", $nombre);
    $stmt->bindParam(":correo", $correo);
    $stmt->bindParam(":password", $password_encriptada);
    
    if($stmt->execute()) {
        echo "Usuario administrador creado con éxito. Ya puedes borrar este archivo.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>