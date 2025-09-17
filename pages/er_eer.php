<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mermaid.php';

// Generar código Mermaid para ER y EER
$codeER = buildMermaidER($conn);  // Función para ER
$codeEER = buildMermaidEER($conn); // Función para EER
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Diagrama ER/EER</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="icon" href="../assets/img/logo_uni.png" type="image/png">
  <script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>
  <script>
    mermaid.initialize({ startOnLoad: true });
  </script>
  <style>
    body {
      background: linear-gradient(to right, #f8f9fa, #e9ecef);
    }
    .logo {
      max-width: 100px;
    }
    .diagram-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      padding: 20px;
      margin-bottom: 30px;
    }
    /* Ocultar bloques de texto plano Mermaid */
    pre.code {
      display: none !important;
    }
    footer {
      background: #343a40;
      color: white;
      padding: 10px 0;
    }
    .mermaid-container {
      overflow-x: auto;
      text-align: center;
      margin-top: 20px;
    }
  </style>
</head>
<body>

<!-- Encabezado -->
<header class="text-center py-4 bg-white shadow-sm">
  <img src="../assets/img/logo_uni.png" alt="Logo Universidad" class="logo mb-2">
  <h1 class="h4">Sistema de Administración de Bases de Datos</h1>
  <p class="text-muted mb-0">Diagrama ER/EER</p>
</header>

<div class="container my-5">
  <div class="mb-3">
    <a href="../index.php" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left-circle"></i> Volver al menú
    </a>
  </div>

  <!-- Diagrama ER -->
  <div class="diagram-card">
    <h2 class="h5 text-center mb-4"><i class="bi bi-diagram-3"></i> Diagrama Entidad-Relación (ER)</h2>
    <p class="text-muted text-center">
      Este diagrama representa las entidades y sus relaciones básicas de la base de datos seleccionada.
    </p>
    <hr>

    <?php if (!empty($codeER)): ?>
      <!-- Código Mermaid oculto -->
      <div class="mb-4">
        <h5 class="d-none"><i class="bi bi-code-slash"></i> Código Mermaid (solo lectura)</h5>
        <pre class="code"><?= htmlspecialchars($codeER) ?></pre>
      </div>

      <div class="mermaid-container">
        <div class="mermaid"><?= $codeER ?></div>
      </div>
    <?php else: ?>
      <div class="alert alert-warning text-center">
        <i class="bi bi-exclamation-triangle"></i> No se pudo generar el diagrama ER. Verifica la base de datos.
      </div>
    <?php endif; ?>
  </div>

  <!-- Diagrama EER -->
  <div class="diagram-card">
    <h2 class="h5 text-center mb-4"><i class="bi bi-diagram-3-fill"></i> Diagrama Entidad-Relación Extendido (EER)</h2>
    <p class="text-muted text-center">
      Este diagrama incluye entidades, relaciones y conceptos extendidos como jerarquías y especializaciones.
    </p>
    <hr>

    <?php if (!empty($codeEER)): ?>
      <!-- Código Mermaid oculto -->
      <div class="mb-4">
        <h5 class="d-none"><i class="bi bi-code-slash"></i> Código Mermaid EER (solo lectura)</h5>
        <pre class="code"><?= htmlspecialchars($codeEER) ?></pre>
      </div>

      <div class="mermaid-container">
        <div class="mermaid"><?= $codeEER ?></div>
      </div>
    <?php else: ?>
      <div class="alert alert-warning text-center">
        <i class="bi bi-exclamation-triangle"></i> No se pudo generar el diagrama EER. Verifica la base de datos.
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Footer -->
<footer class="text-center mt-5">
  <p class="mb-0">CEUTEC - Proyecto Final © <?= date('Y'); ?></p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

