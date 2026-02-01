<?php
session_start();
require_once "session-helper.php";

$paginaHTML = file_get_contents("tips.html");

echo "<h3>Debug Info</h3>";
echo "<p>Logged in: " . (isLoggedIn() ? 'YES' : 'NO') . "</p>";
echo "<p>getAreaPersonaleHref(): " . getAreaPersonaleHref() . "</p>";
echo "<p>getAreaPersonaleText(): " . getAreaPersonaleText() . "</p>";
echo "<p>Original placeholder count: " . substr_count($paginaHTML, '[area_personale_href]') . "</p>";

// Gestione Area Personale nella navbar
$paginaHTML = str_replace('[area_personale_href]', getAreaPersonaleHref(), $paginaHTML);
$paginaHTML = str_replace('[area_personale_text]', getAreaPersonaleText(), $paginaHTML);

echo "<p>After replacement placeholder count: " . substr_count($paginaHTML, '[area_personale_href]') . "</p>";
echo "<hr>";

echo $paginaHTML;
?>
