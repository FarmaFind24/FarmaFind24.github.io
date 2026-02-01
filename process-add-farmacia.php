<?php
session_start();
require_once "dbConnection.php";
use DB\DBAccess;

// 1. CONTROLLO SICUREZZA: Solo admin loggati possono accedere
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['ruolo'] !== 'admin') {
    header("Location: area-login.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: area-admin.php");
    exit;
}


$nome = trim($_POST['nome'] ?? '');
$indirizzo = trim($_POST['indirizzo'] ?? '');
$citta = trim($_POST['citta'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$immagine = trim($_POST['immagine'] ?? '');
$tipoOrario = $_POST['tipo_orario'] ?? '';




if (empty($nome) || empty($indirizzo) || empty($citta) || empty($telefono) || empty($tipoOrario)) {
    header("Location: area-admin.php?error=empty_fields#gestione-farmacie");
    exit;
}


if (!preg_match("/^[0-9 \-\+\(\)]+$/", $telefono)) {
    header("Location: area-admin.php?error=invalid_phone#gestione-farmacie");
    exit;
}


if (strlen($nome) < 3 || strlen($nome) > 100) {
    header("Location: area-admin.php?error=invalid_name_length#gestione-farmacie");
    exit;
}


$orariValidi = ['continuato', 'spezzato'];
if (!in_array($tipoOrario, $orariValidi)) {
    header("Location: area-admin.php?error=invalid_schedule#gestione-farmacie");
    exit;
}


$db = new DBAccess();
if (!$db->openDBConnection()) {
    header("Location: area-admin.php?error=db_error#gestione-farmacie");
    exit;
}


if ($db->verificaDuplicatoFarmacia($nome, $citta)) {
    $db->closeConnection();
    header("Location: area-admin.php?error=duplicate_farmacia#gestione-farmacie");
    exit;
}


$idFarmacia = $db->inserisciFarmacia($nome, $indirizzo, $citta, $telefono, $immagine);

if ($idFarmacia === false || $idFarmacia === 0) {
    $db->closeConnection();
    header("Location: area-admin.php?error=insert_failed#gestione-farmacie");
    exit;
}


$orariSalvati = $db->salvaOrariFarmacia($idFarmacia, $tipoOrario);

if (!$orariSalvati) {
    // Se il salvataggio degli orari fallisce, potremmo eliminare la farmacia appena inserita
    // Ma per semplicità manteniamo la farmacia e segnaliamo l'errore
    $db->closeConnection();
    header("Location: area-admin.php?error=schedule_save_failed#gestione-farmacie");
    exit;
}

// 10. SUCCESSO
$db->closeConnection();
header("Location: area-admin.php?success=farmacia_added#gestione-farmacie");
exit;
?>
