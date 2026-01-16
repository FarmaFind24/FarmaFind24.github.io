<?php
require_once "dbConnection.php";
use DB\DBAccess;

// Carica il template HTML
$paginaHTML = file_get_contents("book-app.html");

// --- LOGICA PER PROGRESSIVE ENHANCEMENT (NO-JS) ---

// Recupera i valori selezionati dal POST, se esistono.
$selectedServiceId = $_POST['service'] ?? null;
$selectedCity = $_POST['city'] ?? null;
// NUOVO: Recuperiamo anche Farmacia, Data e Ora
$selectedPharmacyId = $_POST['pharmacy-selection'] ?? null;
$selectedDate = $_POST['date-pick'] ?? null;
$selectedTime = $_POST['time-pick'] ?? null;

// Inizializza le variabili per l'HTML
$htmlFarmacie = '<p>Seleziona un servizio e un comune, poi clicca su "Trova Farmacie" per visualizzare le opzioni.</p>';
$htmlFarmacieMessage = '';
// NUOVO: Variabile per gli slot orari
$htmlTimeSlots = '<p>Seleziona una farmacia e una data per visualizzare gli orari disponibili.</p>';

$db = new DBAccess();
$connessioneOk = $db->openDBConnection();

if ($connessioneOk) {
    
    // 1. LOGICA FARMACIE (Tuo codice originale, con piccola aggiunta per mantenere la selezione "checked")
    if ($selectedServiceId && $selectedCity) {
        $farmacie = $db->getFarmaciePerServizioECitta($selectedServiceId, $selectedCity);
        $htmlFarmacie = ''; // Svuota il messaggio di default

        if ($farmacie && count($farmacie) > 0) {
            foreach ($farmacie as $farmacia) {
                // MODIFICA NECESSARIA: Aggiunto controllo per mantenere il radio button selezionato
                $checked = ($farmacia['id'] == $selectedPharmacyId) ? 'checked' : '';
                
                $id = 'farm-' . htmlspecialchars($farmacia['id']);
                $htmlFarmacie .= '<div class="farm-box">
                                    <input type="radio" id="' . $id . '" name="pharmacy-selection" value="' . htmlspecialchars($farmacia['id']) . '" required ' . $checked . '>
                                    <label for="' . $id . '" class="card-clickable-label">
                                        <span><span class="farm-name">' . htmlspecialchars($farmacia['nome']) . '</span><span class="farm-address">- ' . htmlspecialchars($farmacia['indirizzo']) . '</span></span>
                                    </label>
                                </div>';
            }
        } else {
            // Logica comuni limitrofi (Tuo codice originale)
            $htmlFarmacieMessage = '<p>Nessuna farmacia trovata a ' . htmlspecialchars($selectedCity) . ' per il servizio scelto. Ecco una lista di farmacie nei comuni limitrofi:</p>';
            
            // Nota: Assicurati che questo metodo esista nel tuo DBAccess, altrimenti commentalo
            $farmacieVicine = $db->getFarmacieVicinePerServizio($selectedServiceId, $selectedCity); 

            if ($farmacieVicine && count($farmacieVicine) > 0) {
                foreach ($farmacieVicine as $farmacia) {
                    $checked = ($farmacia['id'] == $selectedPharmacyId) ? 'checked' : '';
                    $id = 'farm-vicina-' . htmlspecialchars($farmacia['id']);
                    $htmlFarmacie .= '<div class="farm-box">
                                        <input type="radio" id="' . $id . '" name="pharmacy-selection" value="' . htmlspecialchars($farmacia['id']) . '" required ' . $checked . '>
                                        <label for="' . $id . '" class="card-clickable-label">
                                            <span><span class="farm-name">' . htmlspecialchars($farmacia['nome']) . '</span><span class="farm-address">- ' . htmlspecialchars($farmacia['indirizzo']) . ', ' . htmlspecialchars($farmacia['citta']) . '</span></span>
                                        </label>
                                    </div>';
                }
            } else {
                $htmlFarmacieMessage = ''; 
                $htmlFarmacie = '<p class="no-results">Nessuna farmacia trovata a ' . htmlspecialchars($selectedCity) . ' o nei comuni limitrofi.</p>';
            }
        }
    }

    // 2. NUOVA LOGICA: CALCOLO SLOT ORARI (Solo se ho Farmacia e Data)
    if ($selectedPharmacyId && $selectedDate) {
        // Calcola giorno settimana (0=Dom, 6=Sab)
        $giornoSettimana = date('w', strtotime($selectedDate));
        
        // Recupera gli orari dal DB usando la funzione che abbiamo creato prima
        // Assicurati di aver aggiunto getOrariFarmacia($id, $giorno) in DBAccess
        $fasceOrarie = $db->getOrariFarmacia($selectedPharmacyId, $giornoSettimana);
        
        $htmlTimeSlots = '';
        $oraAttuale = time();
        $isToday = ($selectedDate == date("Y-m-d"));

        if ($fasceOrarie) {
            foreach ($fasceOrarie as $fascia) {
                $start = strtotime($selectedDate . ' ' . $fascia['ora_apertura']);
                $end   = strtotime($selectedDate . ' ' . $fascia['ora_chiusura']);

                // Ciclo per creare slot da 1 ora
                while ($start < $end) {
                    // Filtro passato: se è oggi, non mostrare ore già passate
                    if (!$isToday || $start > $oraAttuale) {
                        
                        $oraFormat = date("H:i", $start);
                        // Mantieni la selezione se l'utente ricarica
                        $checked = ($oraFormat == $selectedTime) ? 'checked' : '';

                        $htmlTimeSlots .= '<div class="time-slot">
                                            <input type="radio" id="t-' . $oraFormat . '" name="time-pick" value="' . $oraFormat . '" required ' . $checked . '>
                                            <label for="t-' . $oraFormat . '">' . $oraFormat . '</label>
                                           </div>';
                    }
                    // Incremento di 1 ora (3600 secondi)
                    $start += 3600; 
                }
            }
            
            if ($htmlTimeSlots === '') {
                $htmlTimeSlots = '<p class="error">Nessun orario disponibile per la data selezionata.</p>';
            }
        } else {
            $htmlTimeSlots = '<p class="error">La farmacia è chiusa in questa data.</p>';
        }
    }

    // 3. LOGICA SERVIZI E CITTÀ (Tuo codice originale)
    $htmlServizi = "";
    $htmlCitta = "";

    // Carica servizi
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
        $htmlServizi = "<p>Nessun servizio disponibile.</p>";
    }

    // Carica città
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

// 4. SOSTITUZIONE SEGNAPOSTI (Originali + Nuovi)

// Nuovi placeholder per Data e Ora
$paginaHTML = str_replace('[min_date]', date("Y-m-d"), $paginaHTML);
$paginaHTML = str_replace('[valore_data]', htmlspecialchars($selectedDate ?? ''), $paginaHTML);
$paginaHTML = str_replace('[time-slots]', $htmlTimeSlots, $paginaHTML);

// Placeholder originali
$paginaHTML = str_replace('[servizi_grid]', $htmlServizi, $paginaHTML);
$paginaHTML = str_replace('[citta_options]', $htmlCitta, $paginaHTML);
$paginaHTML = str_replace('[farmacie_message]', $htmlFarmacieMessage, $paginaHTML);
$paginaHTML = str_replace('[farmacie_grid]', $htmlFarmacie, $paginaHTML);

// Stampa la pagina finale
echo $paginaHTML;
?>