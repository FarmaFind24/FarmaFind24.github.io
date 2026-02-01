<?php
require_once "dbConnection.php";
use DB\DBAccess;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recupera e pulisce input
    $nome = trim($_POST['name'] ?? '');
    $cognome = trim($_POST['surname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm-password'] ?? '';
    
    // VALIDAZIONE INPUT
    
    // 1. Controllo campi vuoti
    if (empty($nome) || empty($cognome) || empty($username) || empty($email) || empty($password) || empty($confirm)) {
        header("Location: area-register.php?error=empty_fields");
        exit;
    }
    
    // 2. Validazione nome (solo lettere, spazi, apostrofi e caratteri accentati)
    if (!preg_match("/^[A-Za-zÀ-ù\s']{2,50}$/", $nome)) {
        header("Location: area-register.php?error=invalid_name");
        exit;
    }
    
    // 3. Validazione cognome (solo lettere, spazi, apostrofi e caratteri accentati)
    if (!preg_match("/^[A-Za-zÀ-ù\s']{2,50}$/", $cognome)) {
        header("Location: area-register.php?error=invalid_surname");
        exit;
    }
    
    // 4. Validazione username (alfanumerici, underscore, trattino - min 3, max 50)
    if (!preg_match("/^[a-zA-Z0-9_-]{3,50}$/", $username)) {
        header("Location: area-register.php?error=invalid_username");
        exit;
    }
    
    // 5. Validazione email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: area-register.php?error=invalid_email");
        exit;
    }
    
    // 6. Validazione lunghezza email
    if (strlen($email) > 100) {
        header("Location: area-register.php?error=email_too_long");
        exit;
    }
    
    // 7. Validazione password - minimo 8 caratteri, almeno una lettera e un numero
    if (strlen($password) < 8) {
        header("Location: area-register.php?error=password_too_short");
        exit;
    }
    
    if (!preg_match("/[a-zA-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
        header("Location: area-register.php?error=password_weak");
        exit;
    }
    
    // 8. Controllo corrispondenza password
    if ($password !== $confirm) {
        header("Location: area-register.php?error=passwords_mismatch");
        exit;
    }

    $db = new DBAccess();
    if ($db->openDBConnection()) {
        $successo = $db->registraUtente($nome, $cognome, $username, $email, $password);
        $db->closeConnection();

        if ($successo) {
            header("Location: area-login.html?success=registered");
        } else {
            header("Location: area-register.php?error=username_taken");
        }
    } else {
        header("Location: area-register.php?error=db_connection");
    }
}
?>