<?php
session_start();
session_unset();
session_destroy();

// Redirigir al login usando ruta absoluta
header("Location: " . dirname($_SERVER['PHP_SELF']) . "/login.php");
exit;
?>
