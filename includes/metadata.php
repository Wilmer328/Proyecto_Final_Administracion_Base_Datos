<?php
// includes/metadata.php
// Funciones para obtener metadatos de la base de datos SQL Server

// Verificar que $conn exista
if (!isset($conn)) {
    die("Error: no se encontró la conexión a la base de datos.");
}

/**
 * Obtener todas las tablas de la base de datos
 */
function getTables($conn) {
    $sql = "SELECT TABLE_NAME 
            FROM INFORMATION_SCHEMA.TABLES 
            WHERE TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME";

    $res = sqlsrv_query($conn, $sql);
    if ($res === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $tables = [];
    while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        $tables[] = $row['TABLE_NAME'];
    }

    sqlsrv_free_stmt($res);
    return $tables;
}

/**
 * Obtener las columnas de cada tabla
 */
function getColumns($conn) {
    $sql = "SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            ORDER BY TABLE_NAME, ORDINAL_POSITION";

    $res = sqlsrv_query($conn, $sql);
    if ($res === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $columns = [];
    while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        $columns[$row['TABLE_NAME']][] = [
            'name' => $row['COLUMN_NAME'],
            'type' => $row['DATA_TYPE'],
            'nullable' => $row['IS_NULLABLE']
        ];
    }

    sqlsrv_free_stmt($res);
    return $columns;
}

/**
 * Obtener claves primarias
 */
function getPrimaryKeys($conn) {
    $sql = "SELECT KU.TABLE_NAME, KU.COLUMN_NAME
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS TC
            JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS KU
                 ON TC.CONSTRAINT_NAME = KU.CONSTRAINT_NAME
            WHERE TC.CONSTRAINT_TYPE = 'PRIMARY KEY'
            ORDER BY KU.ORDINAL_POSITION";

    $res = sqlsrv_query($conn, $sql);
    if ($res === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $primaryKeys = [];
    while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        $primaryKeys[$row['TABLE_NAME']][] = $row['COLUMN_NAME'];
    }

    sqlsrv_free_stmt($res);
    return $primaryKeys;
}

/**
 * Obtener claves foráneas
 */
function getForeignKeys($conn) {
    $sql = "SELECT 
                fk.name AS FK_NAME,
                tp.name AS TABLE_NAME,
                cp.name AS COLUMN_NAME,
                tr.name AS REF_TABLE_NAME,
                cr.name AS REF_COLUMN_NAME
            FROM sys.foreign_keys fk
            JOIN sys.foreign_key_columns fkc ON fk.object_id = fkc.constraint_object_id
            JOIN sys.tables tp ON fkc.parent_object_id = tp.object_id
            JOIN sys.columns cp ON fkc.parent_object_id = cp.object_id AND fkc.parent_column_id = cp.column_id
            JOIN sys.tables tr ON fkc.referenced_object_id = tr.object_id
            JOIN sys.columns cr ON fkc.referenced_object_id = cr.object_id AND fkc.referenced_column_id = cr.column_id
            ORDER BY tp.name, fk.name, fkc.constraint_column_id";

    $res = sqlsrv_query($conn, $sql);
    if ($res === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $foreignKeys = [];
    while ($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)) {
        $foreignKeys[] = [
            'FK_NAME' => $row['FK_NAME'],
            'TABLE_NAME' => $row['TABLE_NAME'],
            'COLUMN_NAME' => $row['COLUMN_NAME'],
            'REF_TABLE_NAME' => $row['REF_TABLE_NAME'],
            'REF_COLUMN_NAME' => $row['REF_COLUMN_NAME']
        ];
    }

    sqlsrv_free_stmt($res);
    return $foreignKeys;
}
