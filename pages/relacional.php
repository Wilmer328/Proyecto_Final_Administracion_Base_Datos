<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/metadata.php';

$tables = getTables($conn);
$columns = getColumns($conn);
$primaryKeys = getPrimaryKeys($conn);
$foreignKeys = getForeignKeys($conn);

$fkMap = [];
foreach ($foreignKeys as $fk) {
    $fkMap[$fk['TABLE_NAME']][$fk['COLUMN_NAME']] = $fk['REF_TABLE_NAME'];
}

// Calcular niveles jerárquicos
$levels = [];
function getLevel($table, $fkMap, &$levels, $visited) {
    if (isset($visited[$table])) return $levels[$table];
    $visited[$table] = true;
    if (!isset($fkMap[$table]) || count($fkMap[$table]) == 0) {
        $levels[$table] = 0;
        return 0;
    }
    $maxParentLevel = 0;
    foreach ($fkMap[$table] as $col => $parentTable) {
        $parentLevel = getLevel($parentTable, $fkMap, $levels, $visited) + 1;
        if ($parentLevel > $maxParentLevel) $maxParentLevel = $parentLevel;
    }
    $levels[$table] = $maxParentLevel;
    return $maxParentLevel;
}
foreach ($tables as $t) { getLevel($t, $fkMap, $levels, []); }
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Modelo Relacional Interactivo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background: linear-gradient(to right, #f8f9fa, #e9ecef); }
.logo { max-width: 100px; }
.table-card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 30px; }
.pk { color: #dc3545; font-weight: bold; }
.fk { color: #0d6efd; font-weight: bold; }
.pkfk { color: #6f42c1; font-weight: bold; }
.arrow { color: #198754; font-weight: bold; margin-left: 5px; }
.diagram-container { position: relative; margin-top: 40px; min-height: 600px; border:1px solid #ccc; }
.diagram-table { background: #fff; border: 2px solid #dee2e6; border-radius: 10px; padding: 10px 20px; min-width: 150px; box-shadow: 0 3px 8px rgba(0,0,0,0.1); position: absolute; cursor: grab; }
.diagram-table.dragging { opacity: 0.7; cursor: grabbing; }
.diagram-table .table-name { font-weight: bold; text-align: center; margin-bottom: 5px; }
.diagram-table .field { padding: 2px 0; }
.arrow-svg { position: absolute; top: 0; left: 0; pointer-events: none; width: 100%; height: 100%; }
</style>
</head>
<body>

<header class="text-center py-4 bg-white shadow-sm">
<img src="../assets/img/logo_uni.png" alt="Logo Universidad" class="logo mb-2">
<h1 class="h4">Sistema de Administración de Bases de Datos</h1>
<p class="text-muted mb-0">Modelo Relacional Interactivo</p>
</header>

<div class="container my-5">
<div class="mb-3">
<a href="../index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle"></i> Volver al menú</a>
</div>

<div class="table-card">
<h2 class="h5 text-center mb-4"><i class="bi bi-table"></i> Estructura Relacional</h2>
<p class="text-muted text-center">
Este modelo muestra las tablas, columnas y sus claves primarias <span class="pk">[PK]</span>, foráneas <span class="fk">[FK]</span> y relaciones PK/FK <span class="pkfk">[PK/FK]</span>.
</p>
<hr>

<?php if (!empty($tables)): ?>
<div class="table-responsive mb-5">
<table class="table table-hover">
<thead class="table-dark">
<tr><th>Tabla</th><th>Columnas</th></tr>
</thead>
<tbody>
<?php foreach ($tables as $table): ?>
<tr>
<td class="fw-bold"><?= htmlspecialchars($table) ?></td>
<td>
<?php
$colList = [];
foreach ($columns[$table] as $col) {
$colName = htmlspecialchars($col['name']);
$arrow = '';
if (isset($fkMap[$table][$col['name']])) $arrow = " <span class='arrow'>&#8594; {$fkMap[$table][$col['name']]}</span>";
if (in_array($col['name'], $primaryKeys[$table] ?? [])) {
if (isset($fkMap[$table][$col['name']])) $colName = "<span class='pkfk'>$colName [PK/FK]</span>" . $arrow;
else $colName = "<span class='pk'>$colName [PK]</span>" . $arrow;
} elseif (isset($fkMap[$table][$col['name']])) $colName = "<span class='fk'>$colName [FK]</span>" . $arrow;
$colList[] = $colName;
}
echo implode(", ", $colList);
?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="diagram-container" id="diagramContainer">
<?php foreach ($tables as $table): ?>
<div class="diagram-table" id="table_<?= $table ?>">
<div class="table-name"><?= $table ?></div>
<?php foreach ($columns[$table] as $col):
$colLabel = htmlspecialchars($col['name']);
$colId = "field_{$table}_{$col['name']}";
if (in_array($col['name'], $primaryKeys[$table] ?? []) && isset($fkMap[$table][$col['name']])) $colLabel .= " [PK/FK]";
elseif (in_array($col['name'], $primaryKeys[$table] ?? [])) $colLabel .= " [PK]";
elseif (isset($fkMap[$table][$col['name']])) $colLabel .= " [FK]";
?>
<div class="field" id="<?= $colId ?>"><?= $colLabel ?></div>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
<svg class="arrow-svg" id="arrowSVG">
<defs>
<marker id="arrowhead" markerWidth="10" markerHeight="7" refX="10" refY="3.5" orient="auto">
<polygon points="0 0,10 3.5,0 7" fill="#198754"></polygon>
</marker>
</defs>
</svg>
</div>

<script>
window.onload = function() {
const container = document.getElementById("diagramContainer");
const svg = document.getElementById("arrowSVG");
const fkMap = <?php echo json_encode($fkMap); ?>;
const levels = <?php echo json_encode($levels); ?>;
const ns = "http://www.w3.org/2000/svg";

// Organizar tablas por niveles y espacio
const levelMap = {};
const spacingX = 250, spacingY = 150;
for (const table in levels) {
const lvl = levels[table];
if (!levelMap[lvl]) levelMap[lvl] = [];
levelMap[lvl].push(table);
}
for (const lvl in levelMap) {
levelMap[lvl].forEach((table,i)=>{
const el = document.getElementById(`table_${table}`);
el.style.left = (i*spacingX + 20) + "px";
el.style.top = (lvl*spacingY + 20) + "px";
});
}

// Dibujar flechas curvas
function drawArrowPath(x1,y1,x2,y2){
const path = document.createElementNS(ns,"path");
const midX = x1 + (x2 - x1)/2;
path.setAttribute("d",`M${x1},${y1} C${midX},${y1} ${midX},${y2} ${x2},${y2}`);
path.setAttribute("stroke","#198754");
path.setAttribute("stroke-width","2");
path.setAttribute("fill","none");
path.setAttribute("marker-end","url(#arrowhead)");
svg.appendChild(path);
}

// Renderizar flechas
function renderArrows(){
Array.from(svg.querySelectorAll('path')).forEach(p=>svg.removeChild(p));
for (let childTable in fkMap){
for (let col in fkMap[childTable]){
const parentTable = fkMap[childTable][col];
const childField = document.getElementById(`field_${childTable}_${col}`);
const parentPK = Array.from(document.querySelectorAll(`#table_${parentTable} .field`))
.find(el=>el.textContent.includes('[PK]'));
if(childField && parentPK){
const rectChild = childField.getBoundingClientRect();
const rectParent = parentPK.getBoundingClientRect();
const rectContainer = container.getBoundingClientRect();
const x1 = rectChild.right - rectContainer.left;
const y1 = rectChild.top + rectChild.height/2 - rectContainer.top;
const x2 = rectParent.left - rectContainer.left;
const y2 = rectParent.top + parentPK.offsetHeight/2 - rectContainer.top;
drawArrowPath(x1,y1,x2,y2);
}
}
}
}

renderArrows();

// Tablas arrastrables
let dragTarget=null, offsetX=0, offsetY=0;
container.addEventListener('mousedown', e=>{
if(e.target.classList.contains('diagram-table') || e.target.closest('.diagram-table')){
dragTarget = e.target.closest('.diagram-table');
dragTarget.classList.add('dragging');
const rect = dragTarget.getBoundingClientRect();
offsetX = e.clientX - rect.left;
offsetY = e.clientY - rect.top;
}
});
window.addEventListener('mousemove', e=>{
if(dragTarget){
dragTarget.style.left = (e.clientX - offsetX - container.getBoundingClientRect().left) + "px";
dragTarget.style.top = (e.clientY - offsetY - container.getBoundingClientRect().top) + "px";
renderArrows();
}
});
window.addEventListener('mouseup', e=>{
if(dragTarget) dragTarget.classList.remove('dragging');
dragTarget=null;
});
};
</script>

<?php else: ?>
<div class="alert alert-warning text-center">
<i class="bi bi-exclamation-triangle"></i> No se encontraron tablas en la base de datos.
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
