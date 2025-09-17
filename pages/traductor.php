<?php
// PROCESAMIENTO DE TRADUCCIONES
date_default_timezone_set('America/Tegucigalpa');
$resultado = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Si el usuario escribió algo en el campo de Álgebra Relacional
    if (!empty($_POST['inputAR'])) {
        $inputAR = trim($_POST['inputAR']);

        // Detectar proyección (π campos (tabla))
        if (preg_match('/π\s*(.+)\s*\((.+)\)/u', $inputAR, $matches)) {
            $campos = trim($matches[1]);
            $tabla = trim($matches[2]);
            $resultado = "SELECT $campos FROM $tabla;";
        }

        // Detectar selección (σ condición (tabla))
        elseif (preg_match('/σ\s*(.+)\s*\((.+)\)/u', $inputAR, $matches)) {
            $condicion = trim($matches[1]);
            $tabla = trim($matches[2]);
            $resultado = "SELECT * FROM $tabla WHERE $condicion;";
        }

        // Detectar JOIN (tabla1 ⋈ tabla2 ON condición)
        elseif (preg_match('/(.+)\s*⋈\s*(.+)\s*ON\s*(.+)/u', $inputAR, $matches)) {
            $tabla1 = trim($matches[1]);
            $tabla2 = trim($matches[2]);
            $condicion = trim($matches[3]);
            $resultado = "SELECT * FROM $tabla1 INNER JOIN $tabla2 ON $condicion;";
        }

        // Si no coincide con nada
        else {
            $resultado = "⚠ No se reconoce la expresión de Álgebra Relacional ingresada.";
        }
    }

    // Si el usuario escribió algo en el campo de SQL
    elseif (!empty($_POST['inputSQL'])) {
        $inputSQL = trim($_POST['inputSQL']);

        // Detectar SELECT con JOIN
        if (preg_match('/SELECT\s+(.+)\s+FROM\s+(\w+)\s+JOIN\s+(\w+)\s+ON\s+(.+)/i', $inputSQL, $matches)) {
            $campos = trim($matches[1]);
            $tabla1 = trim($matches[2]);
            $tabla2 = trim($matches[3]);
            $condicion = trim($matches[4]);
            $resultado = "$tabla1 ⋈ $tabla2 ON $condicion";
        }

        // Detectar SELECT con WHERE
        elseif (preg_match('/SELECT\s+(.+)\s+FROM\s+(\w+)\s+WHERE\s+(.+)/i', $inputSQL, $matches)) {
            $campos = trim($matches[1]);
            $tabla = trim($matches[2]);
            $condicion = trim($matches[3]);

            if ($campos === '*') {
                $resultado = "σ $condicion ($tabla)";
            } else {
                $resultado = "π $campos (σ $condicion ($tabla))";
            }
        }

        // Detectar SELECT simple sin WHERE
        elseif (preg_match('/SELECT\s+(.+)\s+FROM\s+(\w+)/i', $inputSQL, $matches)) {
            $campos = trim($matches[1]);
            $tabla = trim($matches[2]);

            if ($campos === '*') {
                $resultado = "$tabla";
            } else {
                $resultado = "π $campos ($tabla)";
            }
        }

        // Detectar DELETE
        elseif (preg_match('/DELETE\s+FROM\s+(\w+)\s+WHERE\s+(.+)/i', $inputSQL, $matches)) {
            $tabla = trim($matches[1]);
            $condicion = trim($matches[2]);
            $resultado = "δ $condicion ($tabla)"; // δ para eliminación
        }

        // Detectar UPDATE
        elseif (preg_match('/UPDATE\s+(\w+)\s+SET\s+(.+)\s+WHERE\s+(.+)/i', $inputSQL, $matches)) {
            $tabla = trim($matches[1]);
            $cambios = trim($matches[2]);
            $condicion = trim($matches[3]);
            $resultado = "µ $cambios σ $condicion ($tabla)"; // µ para actualización
        }

        // Detectar INSERT
        elseif (preg_match('/INSERT\s+INTO\s+(\w+)\s*\((.+)\)\s*VALUES\s*\((.+)\)/i', $inputSQL, $matches)) {
            $tabla = trim($matches[1]);
            $campos = trim($matches[2]);
            $valores = trim($matches[3]);
            $resultado = "ι ($tabla) [$campos ← $valores]"; // ι para inserción
        }

        // Si no coincide con nada
        else {
            $resultado = "⚠ No se reconoce la sentencia SQL ingresada.";
        }
    }

    // Si no ingresó nada en ninguno de los campos
    else {
        $resultado = "⚠ Por favor ingresa una sentencia para traducir.";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Traductor AR ↔ SQL</title>
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
    .table-card {
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
    textarea {
      resize: none;
    }
  </style>
</head>
<body>

<header class="text-center py-4 bg-white shadow-sm">
  <img src="../assets/img/logo_uni.png" alt="Logo Universidad" class="logo mb-2">
  <h1 class="h4">Sistema de Administración de Bases de Datos</h1>
  <p class="text-muted mb-0">Traductor Álgebra Relacional ↔ SQL</p>
</header>

<div class="container my-5">
  <div class="mb-3">
    <a href="../index.php" class="btn btn-outline-secondary">
      <i class="bi bi-arrow-left-circle"></i> Volver al menú
    </a>
  </div>

  <div class="table-card">
    <h2 class="h5 text-center mb-4"><i class="bi bi-shuffle"></i> Traductor AR ↔ SQL</h2>
    <p class="text-muted text-center">
      Ingresa una sentencia de <strong>Álgebra Relacional</strong> o una de <strong>SQL</strong> y el sistema la convertirá automáticamente.
    </p>
    <hr>

    <form method="post">
      <div class="row g-4">
        <div class="col-md-6">
          <label for="inputAR" class="form-label fw-bold">Álgebra Relacional</label>
          <textarea id="inputAR" name="inputAR" class="form-control" rows="5" placeholder="Ejemplo: σ edad > 18 (Empleados)"></textarea>
          <div class="form-text">Ejemplo: σ edad > 18 (Empleados)</div>
        </div>

        <div class="col-md-6">
          <label for="inputSQL" class="form-label fw-bold">Sentencia SQL</label>
          <textarea id="inputSQL" name="inputSQL" class="form-control" rows="5" placeholder="Ejemplo: SELECT * FROM Empleados WHERE edad > 18;"></textarea>
          <div class="form-text">Ejemplo: SELECT * FROM Empleados WHERE edad > 18;</div>
        </div>
      </div>

      <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary px-5">
          <i class="bi bi-arrow-repeat"></i> Traducir
        </button>
      </div>
    </form>

    <?php if (!empty($resultado)): ?>
      <div class="alert alert-info mt-4">
        <h5 class="alert-heading"><i class="bi bi-check-circle"></i> Resultado:</h5>
        <pre class="mb-0"><?= htmlspecialchars($resultado); ?></pre>
      </div>
    <?php endif; ?>
  </div>
</div>

<footer class="text-center mt-5">
  <p class="mb-0">CEUTEC - Proyecto Final © <?= date('Y'); ?></p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
