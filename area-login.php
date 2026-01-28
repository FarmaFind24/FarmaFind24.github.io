<?php
require_once "session-helper.php";
initSession();

// Se l'utente è già loggato, reindirizza alla sua area
if (isLoggedIn()) {
   $dashboard = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') ? 'area-admin.php' : 'area-personale.php';
   header("Location: " . $dashboard);
   exit;
}

// Carica il template HTML
echo file_get_contents("area-login.html");
?>