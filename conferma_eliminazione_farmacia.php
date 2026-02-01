<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once "session-helper.php";

// Controllo autenticazione e ruolo admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: area-login.php");
    exit;
}

if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: area-personale.php");
    exit;
}

// Recupero parametri da POST
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$nome = isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : '';
$citta = isset($_POST['citta']) ? htmlspecialchars($_POST['citta']) : '';

if (empty($tipo) || empty($nome) || empty($citta)) {
    header("Location: area-admin.php?error=invalid_params");
    exit;
}

// Caricamento template HTML appropriato
if ($tipo === 'farmacia') {
    $paginaHTML = file_get_contents("conferma_eliminazione_farmacia.html");
    $paginaHTML = str_replace('[nome_farmacia]', $nome, $paginaHTML);
    $paginaHTML = str_replace('[citta_farmacia]', $citta, $paginaHTML);
} else {
    // Altri tipi se necessario in futuro
    header("Location: area-admin.php?error=invalid_type");
    exit;
}

echo $paginaHTML;
?>
