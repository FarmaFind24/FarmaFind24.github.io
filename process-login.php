<?php
session_start(); // Avvia la sessione
require_once "dbConnection.php";
use DB\DBAccess;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirectTo = $_POST['redirect_to'] ?? '';
    
    // VALIDAZIONE INPUT
    
    // 1. Controllo campi vuoti
    if (empty($username) || empty($password)) {
        $errorUrl = "area-login.php?error=empty_fields";
        if (!empty($redirectTo)) {
            $errorUrl .= "&redirect=" . urlencode($redirectTo);
        }
        header("Location: " . $errorUrl);
        exit;
    }
    
    // 2. Validazione lunghezza username (min 3, max 50 caratteri)
    if (strlen($username) < 3 || strlen($username) > 50) {
        $errorUrl = "area-login.php?error=invalid_username_length";
        if (!empty($redirectTo)) {
            $errorUrl .= "&redirect=" . urlencode($redirectTo);
        }
        header("Location: " . $errorUrl);
        exit;
    }
    
    // 3. Validazione caratteri username (alfanumerici, underscore, trattino)
    if (!preg_match("/^[a-zA-Z0-9_-]+$/", $username)) {
        $errorUrl = "area-login.php?error=invalid_username_format";
        if (!empty($redirectTo)) {
            $errorUrl .= "&redirect=" . urlencode($redirectTo);
        }
        header("Location: " . $errorUrl);
        exit;
    }
    
    // 4. Validazione lunghezza password (min 6 caratteri, eccezione per utenti legacy)
    $exemptUsers = ['user', 'admin'];
    $minPasswordLength = in_array($username, $exemptUsers) ? 4 : 6;
    
    if (strlen($password) < $minPasswordLength) {
        $errorUrl = "area-login.php?error=invalid_password_length";
        if (!empty($redirectTo)) {
            $errorUrl .= "&redirect=" . urlencode($redirectTo);
        }
        header("Location: " . $errorUrl);
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

            // Determina la destinazione dopo il login
            $destinazione = '';
            
            // 1. Controlla se c'è un redirect specifico dal campo nascosto del form
            if (isset($_POST['redirect_to']) && !empty($_POST['redirect_to'])) {
                // Pulizia di sicurezza: accetta solo nomi di file (non URL esterni)
                $redirectPulito = basename($_POST['redirect_to']);
                // Lista whitelist delle pagine ammesse per redirect
                $paginePernesse = ['book-app.php', 'farm-search.php', 'med-search.php', 'booking-details.php'];
                
                if (in_array($redirectPulito, $paginePernesse)) {
                    $destinazione = $redirectPulito;
                }
            }
            
            // 2. Se non c'è redirect valido, usa il comportamento di default basato sul ruolo
            if (empty($destinazione)) {
                if ($utente['ruolo'] === 'admin') {
                    $destinazione = 'area-admin.php';
                } else {
                    $destinazione = 'area-personale.php';
                }
            }

            header("Location: " . $destinazione);
            exit;

        } else {
            $errorUrl = "area-login.php?error=invalid_credentials";
            if (!empty($redirectTo)) {
                $errorUrl .= "&redirect=" . urlencode($redirectTo);
            }
            header("Location: " . $errorUrl);
        }
    } else {
        $errorUrl = "area-login.php?error=db_error";
        if (!empty($redirectTo)) {
            $errorUrl .= "&redirect=" . urlencode($redirectTo);
        }
        header("Location: " . $errorUrl);
    }
}
?>