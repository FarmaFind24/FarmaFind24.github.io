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
    $nome = trim($_POST['name'] ?? '');
    $cognome = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $idUtente = $_SESSION['user_id'];
    
    // 1. Controllo campi vuoti
    if (empty($nome) || empty($cognome) || empty($email)) {
        header("Location: area-personale.php?error=campi_vuoti");
        exit;
    }
    
    // 2. Validazione nome (solo lettere, spazi, apostrofi e caratteri accentati)
    if (!preg_match("/^[A-Za-zÀ-ù\s']{2,50}$/", $nome)) {
        header("Location: area-personale.php?error=nome_non_valido");
        exit;
    }
    
    // 3. Validazione cognome (solo lettere, spazi, apostrofi e caratteri accentati)
    if (!preg_match("/^[A-Za-zÀ-ù\s']{2,50}$/", $cognome)) {
        header("Location: area-personale.php?error=cognome_non_valido");
        exit;
    }
    
    // 4. Validazione email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: area-personale.php?error=email_non_valida");
        exit;
    }
    
    // 5. Validazione lunghezza email
    if (strlen($email) > 100) {
        header("Location: area-personale.php?error=email_troppo_lunga");
        exit;
    }
    
    $db = new DBAccess();
    if ($db->openDBConnection()) {
        $successo = $db->aggiornaProfiloUtente($idUtente, $nome, $cognome, $email);
        $db->closeConnection();
        
        if ($successo) {
            // Aggiorna i dati nella sessione
            $_SESSION['nome'] = $nome;
            $_SESSION['cognome'] = $cognome;
            $_SESSION['email'] = $email;
            
            header("Location: area-personale.php?success=profilo_aggiornato");
        } else {
            header("Location: area-personale.php?error=aggiornamento_fallito");
        }
    } else {
        header("Location: area-personale.php?error=db_error");
    }
} else {
    header("Location: area-personale.php");
}
?>
