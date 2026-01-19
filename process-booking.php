<?php
session_start();
require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;


// Controllo autenticazione - FONDAMENTALE
if (!isLoggedIn()) {
    // Reindirizzamento al login se si tenta di accedere direttamente senza essere loggati
    header("Location: area-login.html?error=authentication_required");
    exit();
}

// Recupera i dati inviati dal form
$idUtente = $_SESSION['user_id'];
$idServizio = $_POST['service'] ?? null;
$idFarmacia = $_POST['pharmacy-selection'] ?? null;
$dataPrenotazione = $_POST['date-pick'] ?? null;
$oraPrenotazione = $_POST['time-pick'] ?? null;
$nome = $_POST['fname'] ?? null;
$cognome = $_POST['fsurname'] ?? null;
$codiceFiscale = $_POST['fcode'] ?? null;

// Validazione input
if (!$idServizio || !$idFarmacia || !$dataPrenotazione || !$oraPrenotazione || !$nome || !$cognome || !$codiceFiscale) {
    header("Location: book-app.php?error=missing_fields");
    exit();
}

// Validazione nome e cognome (solo lettere, spazi, apostrofi e caratteri accentati)
if (!preg_match("/^[A-Za-zÀ-ù\s']+$/", $nome) || !preg_match("/^[A-Za-zÀ-ù\s']+$/", $cognome)) {
    header("Location: book-app.php?error=invalid_name");
    exit();
}

// Validazione codice fiscale (16 caratteri: 6 lettere, 2 numeri, 1 lettera, 2 numeri, 1 lettera, 3 numeri, 1 lettera)
if (!preg_match("/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/i", $codiceFiscale) || strlen($codiceFiscale) !== 16) {
    header("Location: book-app.php?error=invalid_fiscal_code");
    exit();
}

// Normalizza il codice fiscale in maiuscolo
$codiceFiscale = strtoupper($codiceFiscale);

// Validazione formato data (YYYY-MM-DD)
$dataValidata = DateTime::createFromFormat('Y-m-d', $dataPrenotazione);
if (!$dataValidata || $dataValidata->format('Y-m-d') !== $dataPrenotazione) {
    header("Location: book-app.php?error=invalid_date");
    exit();
}

// Validazione formato ora (HH:MM)
$oraValidata = DateTime::createFromFormat('H:i', $oraPrenotazione);
if (!$oraValidata || $oraValidata->format('H:i') !== $oraPrenotazione) {
    header("Location: book-app.php?error=invalid_time");
    exit();
}

// Verifica che la data non sia nel passato
$oggi = new DateTime();
$dataScelta = new DateTime($dataPrenotazione . ' ' . $oraPrenotazione);
if ($dataScelta < $oggi) {
    header("Location: book-app.php?error=past_date");
    exit();
}

// Connessione al database
$db = new DBAccess();
$connessioneOk = $db->openDBConnection();

if (!$connessioneOk) {
    header("Location: book-app.php?error=db_connection");
    exit();
}

// Ottieni l'ID della combinazione farmacia-servizio
$idFarmaciaServizio = $db->getFarmaciaServizioId($idFarmacia, $idServizio);

if (!$idFarmaciaServizio) {
    $db->closeConnection();
    header("Location: book-app.php?error=service_not_available");
    exit();
}

// Verifica disponibilità dello slot (opzionale, se vuoi evitare doppie prenotazioni)
$slotDisponibile = $db->verificaDisponibilitaSlot($idFarmaciaServizio, $dataPrenotazione, $oraPrenotazione);

if (!$slotDisponibile) {
    $db->closeConnection();
    header("Location: book-app.php?error=slot_unavailable");
    exit();
}

// Inserisci la prenotazione nel database
$risultato = $db->creaPrenotazione($idUtente, $idFarmaciaServizio, $dataPrenotazione, $oraPrenotazione, $nome, $cognome, $codiceFiscale);

$db->closeConnection();

if ($risultato) {
    // Salva l'ID della prenotazione in sessione per la pagina di dettaglio
    $_SESSION['last_booking_id'] = $risultato;
    header("Location: booking-details.php");
} else {
    header("Location: book-app.php?error=booking_failed");
}
exit();
?>
