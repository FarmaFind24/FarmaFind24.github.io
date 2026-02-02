<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once "session-helper.php";

// Controllo autenticazione e ruolo admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: area-login.php");
    exit;
}


$paginaHTML = file_get_contents("conferma_eliminazione.html");
echo $paginaHTML;
?>
