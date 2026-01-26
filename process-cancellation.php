<?php
session_start();
require_once "dbConnection.php";
use DB\DBAccess;

// Controllo sicurezza: solo utenti loggati
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: area-login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idPrenotazione = $_POST['prenotazione_id'] ?? null;
    $idUtente = $_SESSION['user_id'];
    
    // Validazione input
    if (!$idPrenotazione || !is_numeric($idPrenotazione)) {
        header("Location: area-personale.php?error=invalid_booking_id");
        exit;
    }
    
    $db = new DBAccess();
    if ($db->openDBConnection()) {
        $successo = $db->eliminaPrenotazione($idPrenotazione, $idUtente);
        $db->closeConnection();
        
        if ($successo) {
            header("Location: area-personale.php?success=booking_cancelled#prenotazioni");
        } else {
            header("Location: area-personale.php?error=cancellation_failed#prenotazioni");
        }
    } else {
        header("Location: area-personale.php?error=db_error#prenotazioni");
    }
} else {
    header("Location: area-personale.php");
}
exit;
?>