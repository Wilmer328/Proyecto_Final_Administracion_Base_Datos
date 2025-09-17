<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// Si se quiere desconectar
if (isset($_GET['logout'])) {
    unset($_SESSION['selected_db']);
    header("Location: index.php");
    exit;
}

// Si ya se seleccionó una base de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['database'])) {
    $_SESSION['selected_db'] = $_POST['database'];
    header("Location: index.php");
    exit;
}

// Si no hay base seleccionada, obtener la lista de bases de datos
if (!isset($_SESSION['selected_db'])) {
    $databases = [];

    $query = "SELECT name FROM sys.databases WHERE database_id > 4 ORDER BY name";
    $result = sqlsrv_query($conn, $query);

    if ($result) {
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $databases[] = $row['name'];
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Proyecto Administración de BD</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="assets/img/logo_uni.png" type="image/png">
  <style>
    body {
      background: linear-gradient(to right, #f8f9fa, #e9ecef);
    }
    .logo {
      max-width: 120px;
    }
    .card-option {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
    }
    .card-option:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    footer {
      background: #343a40;
      color: white;
      padding: 15px 0;
    }
  </style>
</head>
<body>

<!-- Encabezado -->
<header class="text-center py-4 bg-white shadow-sm">
  <img src="assets/img/logo_uni.png" alt="Logo Universidad" class="logo mb-2">
  <h1 class="h3">Sistema de Administración de Bases de Datos</h1>
  <p class="text-muted">Proyecto Final - Administración de Base de Datos I</p>
</header>

<div class="container mt-5">
  <?php if (!isset($_SESSION['selected_db'])): ?>
    <!-- FORMULARIO PARA SELECCIONAR BASE DE DATOS -->
    <div class="card shadow p-4">
      <h4 class="mb-3 text-center">Seleccionar Base de Datos</h4>
      <?php if (empty($databases)): ?>
        <div class="alert alert-danger">No se encontraron bases de datos en el servidor.</div>
      <?php else: ?>
        <form method="POST">
          <div class="mb-3">
            <label for="database" class="form-label">Bases disponibles:</label>
            <select name="database" id="database" class="form-select" required>
              <option value="">-- Selecciona una base de datos --</option>
              <?php foreach ($databases as $db): ?>
                <option value="<?php echo htmlspecialchars($db); ?>"><?php echo htmlspecialchars($db); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary w-100">Conectar</button>
        </form>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <!-- MENÚ PRINCIPAL -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4>Base seleccionada: <span class="text-primary"><?php echo htmlspecialchars($_SESSION['selected_db']); ?></span></h4>
      <a href="logout.php" class="btn btn-danger">
    <i class="bi bi-box-arrow-right"></i> Desconectar
</a>
    </div>

    <div class="row justify-content-center g-4">
      <!-- Tarjeta ER/EER -->
      <div class="col-md-4">
        <div class="card card-option shadow-sm h-100">
          <div class="card-body text-center">
            <h5 class="card-title text-primary">Modelo ER/EER</h5>
            <p class="card-text">Visualiza el diagrama Entidad-Relación completo basado en la base de datos.</p>
            <a href="pages/er_eer.php" class="btn btn-primary w-100">
              <i class="bi bi-diagram-3"></i> Ver ER/EER
            </a>
          </div>
        </div>
      </div>

      <!-- Tarjeta Modelo Relacional -->
      <div class="col-md-4">
        <div class="card card-option shadow-sm h-100">
          <div class="card-body text-center">
            <h5 class="card-title text-success">Modelo Relacional</h5>
            <p class="card-text">Consulta la estructura relacional generada automáticamente desde SQL Server.</p>
            <a href="pages/relacional.php" class="btn btn-success w-100">
              <i class="bi bi-table"></i> Ver Relacional
            </a>
          </div>
        </div>
      </div>

      <!-- Tarjeta Traductor AR ↔ SQL -->
      <div class="col-md-4">
        <div class="card card-option shadow-sm h-100">
          <div class="card-body text-center">
            <h5 class="card-title text-warning">Traductor AR ↔ SQL</h5>
            <p class="card-text">Convierte expresiones de álgebra relacional a SQL y viceversa fácilmente.</p>
            <a href="pages/traductor.php" class="btn btn-warning w-100 text-dark">
              <i class="bi bi-arrow-left-right"></i> Traducir
            </a>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Botón de información --> 
 <div class="text-center mt-5"> 
  <a href="pages/info.php" class="btn btn-outline-secondary"> 
    <i class="bi bi-info-circle"></i> Información del Proyecto </a> </div> </div>

<!-- Footer -->
<footer class="mt-5 text-center">
  <p class="mb-0">CEUTEC - Proyecto Final © <?php echo date('Y'); ?></p>
</footer>

<!-- Bootstrap JS y íconos -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>
