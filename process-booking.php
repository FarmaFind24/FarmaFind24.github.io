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

// Validazione input
if (!$idServizio || !$idFarmacia || !$dataPrenotazione || !$oraPrenotazione) {
    header("Location: book-app.php?error=missing_fields");
    exit();
}

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

// Verifica disponibilità dello slot (opzionale, se vuoi evitare doppie prenotazioni)
$slotDisponibile = $db->verificaDisponibilitaSlot($idFarmacia, $dataPrenotazione, $oraPrenotazione);

if (!$slotDisponibile) {
    $db->closeConnection();
    header("Location: book-app.php?error=slot_unavailable");
    exit();
}

// Inserisci la prenotazione nel database
$risultato = $db->creaPrenotazione($idUtente, $idFarmacia, $idServizio, $dataPrenotazione, $oraPrenotazione);

$db->closeConnection();

if ($risultato) {
    // Salva l'ID della prenotazione in sessione per la pagina di dettaglio
    $_SESSION['last_booking_id'] = $risultato;
    header("Location: booking-details.html?success=1");
} else {
    header("Location: book-app.php?error=booking_failed");
}
exit();
?>
