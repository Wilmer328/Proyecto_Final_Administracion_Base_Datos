<?php
// includes/db.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Servidor fijo, como en conexión original
$serverName = "HDVLAP-SOPORTE2";

// Validar si ya se seleccionó una base de datos
if (!isset($_SESSION['selected_db'])) {
    header("Location: ../login.php");
    exit;
}

// Información de conexión dinámica según la base seleccionada
$connectionInfo = array(
    "Database" => $_SESSION['selected_db'],
    "CharacterSet" => "UTF-8"
);

// Intentar conexión
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    echo "Error de conexión a la base de datos<br>";
    die(print_r(sqlsrv_errors(), true));
}
?>
