<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
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
    
    
    $idUtente = $_SESSION['user_id'];
    
    $db = new DBAccess();
    if ($db->openDBConnection()) {
        $successo = $db->eliminaUtente($idUtente);
        $db->closeConnection();
        
        if ($successo) {
            // Distruggi la sessione completamente
            $_SESSION = array();
            
            // Elimina anche il cookie di sessione
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 3600, '/');
            }
            
            session_unset();
            session_destroy();
            
            // Rigenera un nuovo ID di sessione per invalidare completamente la vecchia
            session_start();
            session_regenerate_id(true);
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
