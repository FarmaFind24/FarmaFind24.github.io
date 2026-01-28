<?php
// DEBUG TEMPORANEO
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;

// Funzione helper per determinare la pagina di redirect corretta
function getAreaPage() {
    return (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') ? 'area-admin.php' : 'area-personale.php';
}

echo "<!-- DEBUG: Script iniziato -->\n";
echo "<!-- SESSION: " . print_r($_SESSION, true) . " -->\n";
echo "<!-- GET: " . print_r($_GET, true) . " -->\n";

// Controllo autenticazione
if (!isLoggedIn()) {
    echo "<!-- DEBUG: Utente NON loggato -->\n";
    header("Location: area-login.html?error=authentication_required");
    exit();
}

// Recupera l'ID della prenotazione dalla sessione o da GET
$idPrenotazione = $_SESSION['last_booking_id'] ?? $_GET['id'] ?? null;

echo "<!-- DEBUG: ID Prenotazione = " . ($idPrenotazione ?? 'NULL') . " -->\n";

if (!$idPrenotazione) {
    echo "<!-- DEBUG: Nessun ID prenotazione trovato, redirect -->\n";
    // Nessuna prenotazione da visualizzare
    header("Location: " . getAreaPage());
    exit();
}

// Connessione al database
$db = new DBAccess();
$connessioneOk = $db->openDBConnection();

echo "<!-- DEBUG: Connessione DB = " . ($connessioneOk ? 'OK' : 'FAIL') . " -->\n";

if (!$connessioneOk) {
    die("Errore di connessione al database.");
}

// Recupera i dettagli della prenotazione
// Passa anche l'ID utente per sicurezza (solo l'utente che ha fatto la prenotazione può vederla)
echo "<!-- DEBUG: Cerco prenotazione ID=" . $idPrenotazione . " per utente ID=" . $_SESSION['user_id'] . " -->\n";

$dettagli = $db->getDettagliPrenotazione($idPrenotazione, $_SESSION['user_id']);

echo "<!-- DEBUG: Dettagli recuperati: " . ($dettagli ? 'SI' : 'NO') . " -->\n";
if ($dettagli) {
    echo "<!-- DEBUG DATI: " . print_r($dettagli, true) . " -->\n";
}

$db->closeConnection();

if (!$dettagli) {
    echo "<!-- DEBUG: Prenotazione non trovata, redirect -->\n";
    // Prenotazione non trovata o non appartiene all'utente
    header("Location: " . getAreaPage() . "?error=booking_not_found");
    exit();
}

// Formattazione data in formato italiano (es: "24/01/2026")
$dataObj = DateTime::createFromFormat('Y-m-d', $dettagli['data_appuntamento']);
$dataFormattata = $dataObj->format('d/m/Y');

echo "<!-- DEBUG: Data formattata = " . $dataFormattata . " -->\n";

// Formattazione ora (es: "10:30")
$oraFormattata = substr($dettagli['ora_appuntamento'], 0, 5);

echo "<!-- DEBUG: Ora formattata = " . $oraFormattata . " -->\n";

// Carica il template HTML
$paginaHTML = file_get_contents("booking-details.html");

echo "<!-- DEBUG: Template HTML caricato, lunghezza = " . strlen($paginaHTML) . " -->\n";

// Sostituzione placeholder con dati reali
$paginaHTML = str_replace('[numero_prenotazione]', htmlspecialchars($dettagli['id']), $paginaHTML);
$paginaHTML = str_replace('[servizio]', htmlspecialchars($dettagli['servizio_nome']), $paginaHTML);
$paginaHTML = str_replace('[durata_servizio]', htmlspecialchars($dettagli['servizio_durata']), $paginaHTML);
$paginaHTML = str_replace('[data]', htmlspecialchars($dataFormattata), $paginaHTML);
$paginaHTML = str_replace('[orario]', htmlspecialchars($oraFormattata), $paginaHTML);
$paginaHTML = str_replace('[nome_farmacia]', htmlspecialchars($dettagli['farmacia_nome']), $paginaHTML);
$paginaHTML = str_replace('[indirizzo_farmacia]', htmlspecialchars($dettagli['farmacia_indirizzo']), $paginaHTML);
$paginaHTML = str_replace('[citta_farmacia]', htmlspecialchars($dettagli['farmacia_citta']), $paginaHTML);
$paginaHTML = str_replace('[telefono_farmacia]', htmlspecialchars($dettagli['farmacia_telefono']), $paginaHTML);
$paginaHTML = str_replace('[nome_paziente]', htmlspecialchars($dettagli['nome']), $paginaHTML);
$paginaHTML = str_replace('[cognome_paziente]', htmlspecialchars($dettagli['cognome']), $paginaHTML);
$paginaHTML = str_replace('[email_paziente]', htmlspecialchars($dettagli['utente_email']), $paginaHTML);

// Gestione Area Personale nella navbar
$paginaHTML = str_replace('[area_personale_href]', getAreaPersonaleHref(), $paginaHTML);
$paginaHTML = str_replace('[area_personale_text]', getAreaPersonaleText(), $paginaHTML);

echo "<!-- DEBUG: Tutte le sostituzioni completate -->\n";

// Stampa la pagina finale
echo $paginaHTML;

// Pulisci la sessione dopo aver visualizzato i dettagli (opzionale)
if (isset($_SESSION['last_booking_id'])) {
    unset($_SESSION['last_booking_id']);
}
?>
