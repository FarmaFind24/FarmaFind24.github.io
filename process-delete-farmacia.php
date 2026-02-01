<?php
session_start();
require_once "dbConnection.php";
use DB\DBAccess;

// 1. CONTROLLO SICUREZZA: Solo admin loggati possono accedere
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['ruolo'] !== 'admin') {
    header("Location: area-login.php");
    exit;
}

// 2. CONTROLLO METODO REQUEST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: area-admin.php");
    exit;
}

// 3. RECUPERO PARAMETRI
$farmaciaScelta = isset($_POST['nome_farmacia']) ? htmlspecialchars($_POST['nome_farmacia']) : '';
$cittaScelta = isset($_POST['citta_farmacia']) ? htmlspecialchars($_POST['citta_farmacia']) : '';

// 4. VALIDAZIONE
if (empty($farmaciaScelta) || empty($cittaScelta)) {
    header("Location: area-admin.php?error=invalid_id#gestione-farmacie");
    exit;
}

// 5. CONNESSIONE AL DATABASE
$db = new DBAccess();
if (!$db->openDBConnection()) {
    header("Location: area-admin.php?error=db_error#gestione-farmacie");
    exit;
}

// 6. VERIFICA CHE LA FARMACIA ESISTA
$farmacia = $db->getFarmaciaByNomeCitta($farmaciaScelta, $cittaScelta);
if (!$farmacia) {
    $db->closeConnection();
    header("Location: area-admin.php?error=farmacia_not_found#gestione-farmacie");
    exit;
}

// 7. ELIMINAZIONE FARMACIA (e dati associati)
$eliminazioneRiuscita = $db->eliminaFarmacia($farmaciaScelta, $cittaScelta);

if ($eliminazioneRiuscita) {
    $db->closeConnection();
    header("Location: area-admin.php?success=farmacia_deleted#gestione-farmacie");
    exit;
} else {
    $db->closeConnection();
    header("Location: area-admin.php?error=delete_failed#gestione-farmacie");
    exit;
}
?>
