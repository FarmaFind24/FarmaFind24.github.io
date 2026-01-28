<?php
session_start();
require_once "dbConnection.php";
use DB\DBAccess;

// Controllo sicurezza: solo utenti loggati
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: area-login.php");
    exit;
}

// Funzione helper per determinare la pagina di redirect corretta
function getAreaPage() {
    return (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') ? 'area-admin.php' : 'area-personale.php';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idPrenotazione = $_POST['prenotazione_id'] ?? null;
    $idUtente = $_SESSION['user_id'];
    
    // Validazione input
    if (!$idPrenotazione || !is_numeric($idPrenotazione)) {
        header("Location: " . getAreaPage() . "?error=invalid_booking_id");
        exit;
    }
    
    $db = new DBAccess();
    if ($db->openDBConnection()) {
        $successo = $db->eliminaPrenotazione($idPrenotazione, $idUtente);
        $db->closeConnection();
        
        if ($successo) {
            header("Location: " . getAreaPage() . "?success=booking_cancelled#prenotazioni");
        } else {
            header("Location: " . getAreaPage() . "?error=cancellation_failed#prenotazioni");
        }
    } else {
        header("Location: " . getAreaPage() . "?error=db_error#prenotazioni");
    }
} else {
    header("Location: " . getAreaPage());
}
exit;
?>