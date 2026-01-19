<?php
session_start();
require_once "session-helper.php";

$paginaHTML = file_get_contents("about.html");
// Gestione Area Personale nella navbar
$paginaHTML = str_replace('[area_personale_href]', getAreaPersonaleHref(), $paginaHTML);
$paginaHTML = str_replace('[area_personale_text]', getAreaPersonaleText(), $paginaHTML);

echo $paginaHTML;
?>