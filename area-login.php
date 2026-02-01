<?php
require_once "session-helper.php";
initSession();

// Header per prevenire il caching della pagina
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Se l'utente è già loggato, reindirizza alla sua area
if (isLoggedIn()) {
   $dashboard = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') ? 'area-admin.php' : 'area-personale.php';
   header("Location: " . $dashboard);
   exit;
}

// Carica il template HTML
echo file_get_contents("area-login.html");
?>