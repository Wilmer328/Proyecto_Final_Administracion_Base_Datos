<?php
require_once __DIR__ . '/metadata.php';

function cleanName($name) {
    return preg_replace('/[^A-Za-z0-9_]/', '_', $name);
}

function escapeLabel($label) {
    $label = trim($label);
    $label = str_replace('"', '', $label);
    return $label;
}

function sanitizeFlowLabel($label) {
    $label = escapeLabel($label);
    $label = preg_replace('/\s+/', ' ', $label);
    $label = preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $label);
    return $label;
}

/* ===========================================================
   FUNCIÓN: buildMermaidER
   Diagrama ER con simbología estándar
   =========================================================== */
function buildMermaidER($conn) {
    $tables = getTables($conn);
    $columns = getColumns($conn);
    $primaryKeys = getPrimaryKeys($conn);
    $foreignKeys = getForeignKeys($conn);

    $diagram = "flowchart TB\n";
    $diagram .= "%% ER clásico: Rect=Entidad, Óvalo=Atributo, Rombos=Relaciones\n\n";

    // Entidades y atributos
    foreach ($tables as $table) {
        $tableId = 'E_' . cleanName($table);
        $tableLabel = sanitizeFlowLabel($table);
        $diagram .= "  {$tableId}[\"{$tableLabel}\"]\n";

        $cols = $columns[$table] ?? [];
        foreach ($cols as $col) {
            $colId = $tableId . '_A_' . cleanName($col['name']);
            $colLabel = sanitizeFlowLabel($col['name']);

            if (in_array($col['name'], $primaryKeys[$table] ?? [])) {
                $colLabel .= " (PK)";
                $diagram .= "  {$colId}([\"{$colLabel}\"])\n";
                $diagram .= "  class {$colId} pkClass;\n";
            } elseif (in_array($col['name'], array_column($foreignKeys, 'COLUMN_NAME'))) {
                $colLabel .= " (FK)";
                $diagram .= "  {$colId}([\"{$colLabel}\"])\n";
                $diagram .= "  class {$colId} fkClass;\n";
            } elseif (strpos(strtolower($col['name']), 'deriv') !== false) {
                $diagram .= "  {$colId}([\"{$colLabel}\"]):::derived\n";
            } else {
                $diagram .= "  {$colId}([\"{$colLabel}\"])\n";
            }

            $diagram .= "  {$tableId} --- {$colId}\n";
        }
        $diagram .= "\n";
    }

    // Relaciones
    foreach ($foreignKeys as $fk) {
        $parentId = 'E_' . cleanName($fk['REF_TABLE_NAME']);
        $childId  = 'E_' . cleanName($fk['TABLE_NAME']);
        $relId    = 'R_' . cleanName($fk['FK_NAME'] ?? ($fk['TABLE_NAME'] . '_to_' . $fk['REF_TABLE_NAME']));
        $relLabel = sanitizeFlowLabel($fk['FK_NAME'] ?? ($fk['TABLE_NAME'] . '_to_' . $fk['REF_TABLE_NAME']));

        // Rombos
        $diagram .= "  {$relId}{" . $relLabel . "}\n";
        $diagram .= "  {$parentId} ---|\"1\"| {$relId}\n";
        $diagram .= "  {$relId} -.->|\"N\"| {$childId}\n";
        $diagram .= "  class {$relId} relClass;\n\n";
    }

    // Estilos
    $diagram .= "classDef pkClass fill:#a2f5a2,stroke:#000,stroke-width:1px;\n";
    $diagram .= "classDef fkClass fill:#f5d08a,stroke:#000,stroke-width:1px;\n";
    $diagram .= "classDef relClass fill:#8acaf5,stroke:#000,stroke-width:1px;\n";
    $diagram .= "classDef derived fill:#ffffff,stroke:#000,stroke-width:1px,stroke-dasharray:5 5;\n";

    return $diagram;
}

/* ===========================================================
   FUNCIÓN: buildMermaidEER
   Diagrama EER extendido con herencia, categorías y atributos derivados
   =========================================================== */
function buildMermaidEER($conn) {
    $tables = getTables($conn);
    $columns = getColumns($conn);
    $primaryKeys = getPrimaryKeys($conn);
    $foreignKeys = getForeignKeys($conn);

    $diagram = "flowchart TB\n";
    $diagram .= "%% EER: Rect=Entidad, Óvalo=Atributo, Rombos=Relaciones, Herencia, Categorías\n\n";

    // Entidades y atributos (mismo estilo que ER)
    foreach ($tables as $table) {
        $tableId = 'E_' . cleanName($table);
        $tableLabel = sanitizeFlowLabel($table);
        $diagram .= "  {$tableId}[\"{$tableLabel}\"]\n";

        $cols = $columns[$table] ?? [];
        foreach ($cols as $col) {
            $colId = $tableId . '_A_' . cleanName($col['name']);
            $colLabel = sanitizeFlowLabel($col['name']);

            if (in_array($col['name'], $primaryKeys[$table] ?? [])) {
                $colLabel .= " (PK)";
                $diagram .= "  {$colId}([\"{$colLabel}\"])\n"; // óvalo
                $diagram .= "  class {$colId} pkClass;\n";
            } elseif (in_array($col['name'], array_column($foreignKeys, 'COLUMN_NAME'))) {
                $colLabel .= " (FK)";
                $diagram .= "  {$colId}([\"{$colLabel}\"])\n"; // óvalo
                $diagram .= "  class {$colId} fkClass;\n";
            } elseif (strpos(strtolower($col['name']), 'deriv') !== false) {
                $diagram .= "  {$colId}([\"{$colLabel}\"]):::derived\n";
            } else {
                $diagram .= "  {$colId}([\"{$colLabel}\"])\n"; // óvalo normal
            }

            $diagram .= "  {$tableId} --- {$colId}\n";
        }
        $diagram .= "\n";
    }

    // Relaciones como rombos (igual que ER)
    foreach ($foreignKeys as $fk) {
        $parentId = 'E_' . cleanName($fk['REF_TABLE_NAME']);
        $childId  = 'E_' . cleanName($fk['TABLE_NAME']);
        $relId    = 'R_' . cleanName($fk['FK_NAME'] ?? ($fk['TABLE_NAME'] . '_to_' . $fk['REF_TABLE_NAME']));
        $relLabel = sanitizeFlowLabel($fk['FK_NAME'] ?? ($fk['TABLE_NAME'] . '_to_' . $fk['REF_TABLE_NAME']));

        $diagram .= "  {$relId}{" . $relLabel . "}\n";
        $diagram .= "  {$parentId} ---|\"1\"| {$relId}\n";
        $diagram .= "  {$relId} -.->|\"N\"| {$childId}\n";
        $diagram .= "  class {$relId} relClass;\n\n";
    }

    // Herencia: FK que también es PK
    foreach ($tables as $child) {
        $fksTo = array_filter($foreignKeys, fn($fk) => $fk['TABLE_NAME'] === $child);
        if (count($fksTo) === 1) {
            $fk = array_values($fksTo)[0];
            $parent = $fk['REF_TABLE_NAME'];
            $fk_col = $fk['COLUMN_NAME'] ?? null;

            if ($fk_col && in_array($fk_col, $primaryKeys[$child] ?? []) && in_array($fk_col, $primaryKeys[$parent] ?? [])) {
                $diagram .= "  E_" . cleanName($parent) . " --|> E_" . cleanName($child) . " : \"Herencia\"\n\n";
            }
        }
    }

    // Categorías: tablas referenciadas por varias FKs (rombo)
    $tableFkCount = [];
    foreach ($foreignKeys as $fk) {
        $ref = $fk['REF_TABLE_NAME'];
        $tableFkCount[$ref] = ($tableFkCount[$ref] ?? 0) + 1;
    }
    foreach ($tableFkCount as $table => $count) {
        if ($count > 1) {
            $categoryId = 'CAT_' . cleanName($table);
            $categoryLabel = sanitizeFlowLabel("Categoría_" . $table);
            $diagram .= "  {$categoryId}{" . $categoryLabel . "}\n";
            $diagram .= "  {$categoryId} --- E_" . cleanName($table) . "\n\n";
        }
    }

    // Estilos (mantener simples para compatibilidad)
    $diagram .= "classDef pkClass fill:#a2f5a2,stroke:#000,stroke-width:1px;\n";
    $diagram .= "classDef fkClass fill:#f5d08a,stroke:#000,stroke-width:1px;\n";
    $diagram .= "classDef relClass fill:#8acaf5,stroke:#000,stroke-width:1px;\n";
    $diagram .= "classDef derived fill:#ffffff,stroke:#000,stroke-width:1px,stroke-dasharray:5 5;\n";

    return $diagram;
}
