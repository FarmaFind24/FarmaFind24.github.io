<?php
// FILE: get_slots.php
require_once "dbConnection.php";
use DB\DBAccess;

// Se vogliamo restituire HTML puro per l'iniezione diretta
header('Content-Type: text/html; charset=utf-8');

$farmaciaId = $_GET['id'] ?? null;
$date = $_GET['date'] ?? null;

// Controllo input minimi
if (!$farmaciaId || !$date) {
    echo '<p class="error">Seleziona una farmacia e una data.</p>';
    exit;
}

// Validazione farmaciaId (deve essere un intero positivo)
$farmaciaId = filter_var($farmaciaId, FILTER_VALIDATE_INT);
if ($farmaciaId === false || $farmaciaId <= 0) {
    echo '<p class="error">ID farmacia non valido.</p>';
    exit;
}

// Validazione formato data (YYYY-MM-DD)
$dateValidated = DateTime::createFromFormat('Y-m-d', $date);
if (!$dateValidated || $dateValidated->format('Y-m-d') !== $date) {
    echo '<p class="error">Formato data non valido.</p>';
    exit;
}

$db = new DBAccess();
$connessioneOk = $db->openDBConnection();

if (!$connessioneOk) {
    echo '<p class="error">Errore di connessione al database.</p>';
    exit;
}

// 1. Calcola giorno settimana (0=Dom, 6=Sab)
$giornoSettimana = date('w', strtotime($date));

// 2. Recupera fasce orarie
$fasceOrarie = $db->getOrariFarmacia($farmaciaId, $giornoSettimana);

$htmlOutput = '';
$oraAttuale = time();
$isToday = ($date == date("Y-m-d"));

if ($fasceOrarie) {
    foreach ($fasceOrarie as $fascia) {
        $start = strtotime($date . ' ' . $fascia['ora_apertura']);
        $end   = strtotime($date . ' ' . $fascia['ora_chiusura']);

        // Ciclo per creare slot da 1 ora
        while ($start < $end) {
            // Filtro passato: se è oggi, non mostrare ore già passate
            if (!$isToday || $start > $oraAttuale) {
                
                $oraFormat = date("H:i", $start);
                
                // Nota: l'ID deve essere univoco
                $htmlOutput .= '<div class="time-slot">
                                    <input type="radio" id="t-' . $oraFormat . '" name="time-pick" value="' . $oraFormat . '" required>
                                    <label for="t-' . $oraFormat . '">' . $oraFormat . '</label>
                                </div>';
            }
            $start += 3600; // +1 ora
        }
    }
    
    if ($htmlOutput === '') {
        $htmlOutput = '<p class="error">Nessun orario disponibile (turno terminato o passato).</p>';
    }
} else {
    $htmlOutput = '<p class="error">La farmacia è chiusa in questa data.</p>';
}

$db->closeConnection();
echo $htmlOutput;
?>