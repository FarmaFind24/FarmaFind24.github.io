<?php
session_start(); // Avvia la sessione
require_once "dbConnection.php";
use DB\DBAccess;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

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