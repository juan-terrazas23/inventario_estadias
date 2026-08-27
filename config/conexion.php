<?php
// Credenciales por defecto de XAMPP
$host = "localhost";
$dbname = "inventario_db"; 
$username = "root"; 
$password = ""; // En XAMPP la contraseña viene vacía por defecto

try {
    // Creamos la instancia de PDO (el puente)
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configuramos PDO para que nos avise claramente si hay un error
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Descomenta la línea de abajo quitando las dos diagonales (//) solo para hacer la prueba hoy
    // echo "¡Conexión exitosa a la base de datos!";
    
} catch(PDOException $e) {
    // Si algo sale mal, detenemos todo y mostramos el error
    die("Error de conexión: " . $e->getMessage());
}
?>