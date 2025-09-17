<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Información del Proyecto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" href="../assets/img/logo_uni.png" type="image/png">
  <style>
    body {
      background: linear-gradient(to right, #f8f9fa, #e9ecef);
    }
    .logo {
      max-width: 100px;
    }
    .info-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 20px;
    }
    footer {
      background: #343a40;
      color: white;
      padding: 10px 0;
    }
  </style>
</head>
<body>

<!-- Encabezado -->
<header class="text-center py-4 bg-white shadow-sm">
  <img src="../assets/img/logo_uni.png" alt="Logo Universidad" class="logo mb-2">
  <h1 class="h4">Sistema de Administración de Bases de Datos</h1>
  <p class="text-muted mb-0">Información del Proyecto</p>
</header>

<!-- Contenido -->
<div class="container my-5">
  <div class="info-card mx-auto" style="max-width: 700px;">
    <h2 class="text-center mb-4"><i class="bi bi-info-circle"></i> Información del Proyecto</h2>

    <h5>Datos Generales</h5>
    <p><strong>Grupo:</strong> #2</p>
    <p><strong>Clase:</strong> Administración de Base de Datos I</p>
    <p><strong>Sección:</strong> 308</p>
    <p><strong>Periodo:</strong> III</p>

    <hr>

    <h5>Integrantes</h5>
    <ul>
      <li>Abraham David Escobar Gómez</li>
      <li>Jasson Enrique Reyes Lemus</li>
      <li>José Roberto Figueroa Hernández</li>
      <li>Wilmer Josué Sánchez Gómez</li>
    </ul>

    <hr>

    <h5>Fecha y Hora Actual</h5>
    <p id="fechaHora" class="fw-bold text-success"></p>

    <div class="mt-4 text-center">
      <a href="../index.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left-circle"></i> Volver al Menú
      </a>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="text-center mt-5">
  <p class="mb-0">CEUTEC - Proyecto Final © <?= date('Y'); ?></p>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Mostrar hora y fecha en tiempo real
  function actualizarFechaHora() {
    const ahora = new Date();
    const opciones = { dateStyle: 'full', timeStyle: 'medium' };
    document.getElementById('fechaHora').innerText = ahora.toLocaleString('es-ES', opciones);
  }
  setInterval(actualizarFechaHora, 1000);
  actualizarFechaHora();
</script>
</body>
</html>

