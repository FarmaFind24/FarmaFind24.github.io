<?php
session_start();
require_once "dbConnection.php";
use DB\DBAccess;

// Controllo sicurezza: solo utenti loggati
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: area-login.html");
    exit;
}

// Funzione helper per determinare la pagina di redirect corretta
function getAreaPage() {
    return (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') ? 'area-admin.php' : 'area-personale.php';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica che l'utente abbia confermato
    if (!isset($_POST['confirm_deletion']) || $_POST['confirm_deletion'] !== 'on') {
        header("Location: conferma_eliminazione.html?error=conferma_mancante");
        exit;
    }
    
    $idUtente = $_SESSION['user_id'];
    
    $db = new DBAccess();
    if ($db->openDBConnection()) {
        $successo = $db->eliminaUtente($idUtente);
        $db->closeConnection();
        
        if ($successo) {
            // Distruggi la sessione
            session_unset();
            session_destroy();
            
            // Redirect alla home con messaggio di conferma
            header("Location: index.php?success=account_eliminato");
        } else {
            header("Location: " . getAreaPage() . "?error=eliminazione_fallita");
        }
    } else {
        header("Location: " . getAreaPage() . "?error=db_error");
    }
} else {
    header("Location: " . getAreaPage());
}
?>
