<?php
session_start(); // Avvia la sessione
require_once "dbConnection.php";
use DB\DBAccess;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // VALIDAZIONE INPUT
    
    // 1. Controllo campi vuoti
    if (empty($username) || empty($password)) {
        header("Location: area-login.html?error=empty_fields");
        exit;
    }
    
    // 2. Validazione lunghezza username (min 3, max 50 caratteri)
    if (strlen($username) < 3 || strlen($username) > 50) {
        header("Location: area-login.html?error=invalid_username_length");
        exit;
    }
    
    // 3. Validazione caratteri username (alfanumerici, underscore, trattino)
    if (!preg_match("/^[a-zA-Z0-9_-]+$/", $username)) {
        header("Location: area-login.html?error=invalid_username_format");
        exit;
    }
    
    // 4. Validazione lunghezza password (min 6 caratteri, eccezione per utenti legacy)
    $exemptUsers = ['user', 'admin'];
    $minPasswordLength = in_array($username, $exemptUsers) ? 4 : 6;
    
    if (strlen($password) < $minPasswordLength) {
        header("Location: area-login.html?error=invalid_password_length");
        exit;
    }

    $db = new DBAccess();
    if ($db->openDBConnection()) {
        $utente = $db->eseguiLogin($username, $password);
        $db->closeConnection();

        if ($utente) {
            // Login riuscito: salvo i dati in sessione
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $utente['id'];
            $_SESSION['username'] = $utente['username'];
            $_SESSION['ruolo'] = $utente['ruolo'];
            $_SESSION['nome'] = $utente['nome'];
            $_SESSION['cognome'] = $utente['cognome'];
            $_SESSION['email'] = $utente['email'];
            $_SESSION['data_registrazione'] = $utente['data_registrazione'];

            // Redirect in base al ruolo o alla pagina personale
            header("Location: area-personale.php"); 
        } else {
            header("Location: area-login.html?error=invalid_credentials");
        }
    } else {
        header("Location: area-login.html?error=db_error");
    }
}
?>