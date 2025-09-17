<?php
function arToSql($input) {
$s = trim($input);
$s = str_replace(['U', 'INTERSECT', 'EXCEPT'], ['∪', '∩', '−'], $s);


if (preg_match('/^σ\s*(.+)\s*\((\w+)\)$/u', $s, $m)) {
$cond = $m[1]; $rel = $m[2];
return "SELECT * FROM $rel WHERE $cond;";
}


if (preg_match('/^π\s*([\w,\s\.]+)\s*\((\w+)\)$/u', $s, $m)) {
$cols = preg_replace('/\s+/', '', $m[1]);
$rel = $m[2];
return "SELECT $cols FROM $rel;";
}


if (preg_match('/^π\s*([\w,\s\.]+)\s*\(\s*σ\s*(.+)\s*\((\w+)\)\s*\)$/u', $s, $m)) {
$cols = preg_replace('/\s+/', '', $m[1]);