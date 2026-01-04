<?php
require_once "dbConnection.php";
use DB\DBAccess;

// Carica il template HTML
$paginaHTML = file_get_contents("book-app.html");

// --- LOGICA PER PROGRESSIVE ENHANCEMENT (NO-JS) ---

// Recupera i valori selezionati dal POST, se esistono.
$selectedServiceId = $_POST['service'] ?? null;
$selectedCity = $_POST['city'] ?? null;

// Inizializza le variabili per l'HTML delle farmacie.
$htmlFarmacie = '<p>Seleziona un servizio e un comune, poi clicca su "Trova Farmacie" per visualizzare le opzioni.</p>';
$htmlFarmacieMessage = '';

// Se il servizio e la città sono stati inviati (tramite il pulsante "Trova Farmacie"), esegui la ricerca.
if ($selectedServiceId && $selectedCity) {
    $db = new DBAccess();
    if ($db->openDBConnection()) {
        $farmacie = $db->getFarmaciePerServizioECitta($selectedServiceId, $selectedCity);
        $htmlFarmacie = ''; // Svuota il messaggio di default

        if ($farmacie && count($farmacie) > 0) {
            foreach ($farmacie as $farmacia) {
                $id = 'farm-' . htmlspecialchars($farmacia['id']);
                $htmlFarmacie .= '<div class="farm-box">
                                    <input type="radio" id="' . $id . '" name="pharmacy-selection" value="' . htmlspecialchars($farmacia['id']) . '" required>
                                    <label for="' . $id . '" class="card-clickable-label">
                                        <span><span class="farm-name">' . htmlspecialchars($farmacia['nome']) . '</span><span class="farm-address">- ' . htmlspecialchars($farmacia['indirizzo']) . '</span></span>
                                    </label>
                                </div>';
            }
        } else {
            // Se non trovi farmacie, cerca nei comuni limitrofi
            $htmlFarmacieMessage = '<p>Nessuna farmacia trovata a ' . htmlspecialchars($selectedCity) . ' per il servizio scelto. Ecco una lista di farmacie nei comuni limitrofi:</p>';
            $farmacieVicine = $db->getFarmacieVicinePerServizio($selectedServiceId, $selectedCity);

            if ($farmacieVicine && count($farmacieVicine) > 0) {
                foreach ($farmacieVicine as $farmacia) {
                    $id = 'farm-vicina-' . htmlspecialchars($farmacia['id']);
                    $htmlFarmacie .= '<div class="farm-box">
                                        <input type="radio" id="' . $id . '" name="pharmacy-selection" value="' . htmlspecialchars($farmacia['id']) . '" required>
                                        <label for="' . $id . '" class="card-clickable-label">
                                            <span><span class="farm-name">' . htmlspecialchars($farmacia['nome']) . '</span><span class="farm-address">- ' . htmlspecialchars($farmacia['indirizzo']) . ', ' . htmlspecialchars($farmacia['citta']) . '</span></span>
                                        </label>
                                    </div>';
                }
            } else {
                $htmlFarmacieMessage = ''; // Svuota il messaggio
                $htmlFarmacie = '<p class="no-results">Nessuna farmacia trovata a ' . htmlspecialchars($selectedCity) . ' o nei comuni limitrofi che offra il servizio selezionato.</p>';
            }
        }
        $db->closeConnection();
    } else {
        $htmlFarmacie = '<p class="error">Errore di connessione al database.</p>';
    }
}

$db = new DBAccess();
$connessioneOk = $db->openDBConnection();

$htmlServizi = "";
$htmlCitta = "";

if ($connessioneOk) {
    // Carica servizi e preseleziona quello scelto
    $servizi = $db->getServiziDisponibili();
    if ($servizi && count($servizi) > 0) {
        foreach ($servizi as $servizio) {
            $checkedAttr = ($servizio['id'] == $selectedServiceId) ? ' checked' : '';
            $htmlServizi .= '<div class="service-box">
                                <input type="radio" name="service" id="visita' . htmlspecialchars($servizio['id']) . '" value="' . htmlspecialchars($servizio['id']) . '" required' . $checkedAttr . '>
                                <label for="visita' . htmlspecialchars($servizio['id']) . '"> <strong>' . htmlspecialchars($servizio['nome_servizio']) . '</strong>' . htmlspecialchars($servizio['descrizione']) . '</label>
                            </div>';
        }
    } else {
        $htmlServizi = "<p>Nessun servizio di prenotazione disponibile al momento.</p>";
    }

    // Carica città e preseleziona quella scelta
    $citta = $db->getListaCitta();
    if ($citta && count($citta) > 0) {
        $disabledSelected = !$selectedCity ? ' selected' : '';
        $htmlCitta = '<option value="" disabled' . $disabledSelected . '>Seleziona un comune</option>';
        foreach ($citta as $c) {
            $selectedAttr = ($c['citta'] == $selectedCity) ? ' selected' : '';
            $htmlCitta .= '<option value="' . htmlspecialchars($c['citta']) . '"' . $selectedAttr . '>' . htmlspecialchars($c['citta']) . '</option>';
        }
    } else {
        $htmlCitta = '<option value="">Nessun comune disponibile</option>';
    }

    $db->closeConnection();
} else {
    $htmlServizi = "<p class='error'>Errore di connessione al database.</p>";
    $htmlCitta = '<option value="">Errore di connessione</option>';
}

// Sostituisci i segnaposto con l'HTML generato
$paginaHTML = str_replace('[servizi_grid]', $htmlServizi, $paginaHTML);
$paginaHTML = str_replace('[citta_options]', $htmlCitta, $paginaHTML);
$paginaHTML = str_replace('[farmacie_message]', $htmlFarmacieMessage, $paginaHTML);
$paginaHTML = str_replace('[farmacie_grid]', $htmlFarmacie, $paginaHTML);

// Stampa la pagina finale
echo $paginaHTML;