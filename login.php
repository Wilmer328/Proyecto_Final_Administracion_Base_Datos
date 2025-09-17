<?php
session_start();

$error = "";
$databases = [];
$serverName = "";

// Si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serverName = trim($_POST['server']);

    // Conexión usando Windows Authentication
    $connectionInfo = array("CharacterSet" => "UTF-8");
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    if ($conn === false) {
        $error = "Error al conectar con el servidor: " . print_r(sqlsrv_errors(), true);
    } else {
        // Obtener bases de datos disponibles
        $sql = "SELECT name FROM sys.databases WHERE database_id > 4 ORDER BY name";
        $stmt = sqlsrv_query($conn, $sql);

        if ($stmt === false) {
            $error = "No se pudieron obtener las bases de datos: " . print_r(sqlsrv_errors(), true);
        } else {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $databases[] = $row['name'];
            }
        }

        // Si se selecciona una base de datos
        if (!empty($_POST['database'])) {
            $_SESSION['selected_db'] = $_POST['database'];
            $_SESSION['server_name'] = $serverName;
            header("Location: index.php");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Seleccionar Base de Datos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="icon" href="assets/img/logo_uni.png" type="image/png">
<style>
body {
    background: linear-gradient(to right, #f8f9fa, #e9ecef);
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
}
.login-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    width: 420px;
}
</style>
</head>
<body>

<div class="login-card">
  <div class="text-center mb-4">
    <img src="assets/img/logo_uni.png" alt="Logo Universidad" class="mb-3" style="max-width: 80px;">
    <h2 class="h5">Conectar a SQL Server</h2>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label for="server" class="form-label">Nombre del Servidor</label>
      <input type="text" id="server" name="server" class="form-control" placeholder="Ej: HDVLAP-SOPORTE2" value="<?= htmlspecialchars($serverName) ?>" required>
    </div>

    <?php if (!empty($databases)): ?>
      <div class="mb-3">
        <label for="database" class="form-label">Seleccionar Base de Datos</label>
        <select id="database" name="database" class="form-select" required>
          <option value="">-- Selecciona una base --</option>
          <?php foreach ($databases as $db): ?>
            <option value="<?= htmlspecialchars($db) ?>"><?= htmlspecialchars($db) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary w-100">
      <i class="bi bi-box-arrow-in-right"></i> Conectar
    </button>
  </form>
</div>

</body>
</html>