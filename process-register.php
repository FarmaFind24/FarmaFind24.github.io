<?php
require_once "dbConnection.php";
use DB\DBAccess;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['name'];
    $cognome = $_POST['surname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm-password'];

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