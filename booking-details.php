<?php
/*  
    DOCUMENTO: PHP per pagina di dettagli post prenotazione
    DESCRIZIONE: Recupera i dati dal DB e popola il template HTML dei dettagli.
*/
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;

function getAreaPage() {
    return (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') ? 'area-admin.php' : 'area-personale.php';
}

echo "<!-- DEBUG: Script iniziato -->\n";
echo "<!-- SESSION: " . print_r($_SESSION, true) . " -->\n";
echo "<!-- GET: " . print_r($_GET, true) . " -->\n";

if (!isLoggedIn()) {
    echo "<!-- DEBUG: Utente NON loggato -->\n";
    header("Location: area-login.html?error=authentication_required");
    exit();
}

$idPrenotazione = $_SESSION['last_booking_id'] ?? $_GET['id'] ?? null;

echo "<!-- DEBUG: ID Prenotazione = " . ($idPrenotazione ?? 'NULL') . " -->\n";

if (!$idPrenotazione) {
    echo "<!-- DEBUG: Nessun ID prenotazione trovato, redirect -->\n";
    header("Location: " . getAreaPage());
    exit();
}

$db = new DBAccess();
$connessioneOk = $db->openDBConnection();

echo "<!-- DEBUG: Connessione DB = " . ($connessioneOk ? 'OK' : 'FAIL') . " -->\n";

if (!$connessioneOk) {
    die("Errore di connessione al database.");
}

echo "<!-- DEBUG: Cerco prenotazione ID=" . $idPrenotazione . " per utente ID=" . $_SESSION['user_id'] . " -->\n";

$dettagli = $db->getDettagliPrenotazione($idPrenotazione, $_SESSION['user_id']);

echo "<!-- DEBUG: Dettagli recuperati: " . ($dettagli ? 'SI' : 'NO') . " -->\n";
if ($dettagli) {
    echo "<!-- DEBUG DATI: " . print_r($dettagli, true) . " -->\n";
}

$db->closeConnection();

if (!$dettagli) {
    echo "<!-- DEBUG: Prenotazione non trovata, redirect -->\n";
    header("Location: " . getAreaPage() . "?error=booking_not_found");
    exit();
}

$dataObj = DateTime::createFromFormat('Y-m-d', $dettagli['data_appuntamento']);
$dataFormattata = $dataObj->format('d/m/Y');

echo "<!-- DEBUG: Data formattata = " . $dataFormattata . " -->\n";

$oraFormattata = substr($dettagli['ora_appuntamento'], 0, 5);

echo "<!-- DEBUG: Ora formattata = " . $oraFormattata . " -->\n";

$paginaHTML = file_get_contents("booking-details.html");

echo "<!-- DEBUG: Template HTML caricato, lunghezza = " . strlen($paginaHTML) . " -->\n";

$paginaHTML = str_replace('[numero_prenotazione]', htmlspecialchars($dettagli['id']), $paginaHTML);
$paginaHTML = str_replace('[servizio]', htmlspecialchars($dettagli['servizio_nome']), $paginaHTML);
$paginaHTML = str_replace('[durata_servizio]', htmlspecialchars($dettagli['servizio_durata']), $paginaHTML);
$paginaHTML = str_replace('[data]', htmlspecialchars($dataFormattata), $paginaHTML);
$paginaHTML = str_replace('[orario]', htmlspecialchars($oraFormattata), $paginaHTML);
$paginaHTML = str_replace('[nome_farmacia]', htmlspecialchars($dettagli['farmacia_nome']), $paginaHTML);
$paginaHTML = str_replace('[indirizzo_farmacia]', htmlspecialchars($dettagli['farmacia_indirizzo']), $paginaHTML);
$paginaHTML = str_replace('[citta_farmacia]', htmlspecialchars($dettagli['farmacia_citta']), $paginaHTML);


$telefonoClean = str_replace(' ', '', $dettagli['farmacia_telefono']);
$paginaHTML = str_replace('[tel_link]', htmlspecialchars($telefonoClean), $paginaHTML);
$paginaHTML = str_replace('[tel_visual]', htmlspecialchars($dettagli['farmacia_telefono']), $paginaHTML);
$paginaHTML = str_replace('[note_aggiuntive]', htmlspecialchars($dettagli['note_aggiuntive']), $paginaHTML);

$paginaHTML = str_replace('[area_personale_href]', getAreaPersonaleHref(), $paginaHTML);
$paginaHTML = str_replace('[area_personale_text]', getAreaPersonaleText(), $paginaHTML);

echo "<!-- DEBUG: Tutte le sostituzioni completate -->\n";

echo $paginaHTML;
if (isset($_SESSION['last_booking_id'])) {
    unset($_SESSION['last_booking_id']);
}
?>
