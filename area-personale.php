<?php
session_start();
// Impedisce al browser di memorizzare la pagina
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;

// 1. CONTROLLO SICUREZZA: Se non � loggato, via al login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: area-login.html");
    exit;
}

// 2. CARICAMENTO TEMPLATE HTML
$paginaHTML = file_get_contents("area-personale.html");

// 3. RECUPERO DATI DAL DATABASE (Prenotazioni)
$db = new DBAccess();
$connessioneOk = $db->openDBConnection();
$htmlPrenotazioni = "";
$htmlPrenotazioniShort = ""; // ?? AGGIUNGI QUESTA RIGA (mancava l'inizializzazione)

if ($connessioneOk) {
    $prenotazioni = $db->getPrenotazioniUtente($_SESSION['user_id']);
    $prenotazioniShort = $db->getPrenotazioniUtentePreview($_SESSION['user_id']);
    
    // Array per la visualizzazione grafica (Abbreviati)
    $mesi_it = [
        "Jan" => "GEN", "Feb" => "FEB", "Mar" => "MAR", "Apr" => "APR",
        "May" => "MAG", "Jun" => "GIU", "Jul" => "LUG", "Aug" => "AGO",
        "Sep" => "SET", "Oct" => "OTT", "Nov" => "NOV", "Dec" => "DIC"
    ];

    // Array per NVDA (Nomi completi in minuscolo per evitare lo spelling)
    $mesi_estesi = [
        "Jan" => "gennaio", "Feb" => "febbraio", "Mar" => "marzo", "Apr" => "aprile",
        "May" => "maggio", "Jun" => "giugno", "Jul" => "luglio", "Aug" => "agosto",
        "Sep" => "settembre", "Oct" => "ottobre", "Nov" => "novembre", "Dec" => "dicembre"
    ];

    
    if ($prenotazioni && count($prenotazioni) > 0) {
        foreach ($prenotazioni as $p) {
            $timestamp = strtotime($p['data_appuntamento']);
            $mese_en = date("M", $timestamp);
            
            $mese_visuale = $mesi_it[$mese_en]; 
            $mese_parlato = $mesi_estesi[$mese_en]; 
            $giorno = date("d", $timestamp);
            $ora = date("H:i", strtotime($p['ora_appuntamento']));

            $htmlPrenotazioni .= '
            <li class="appuntamento-card">

                <div class="appuntamento-card-date" aria-hidden="true">
                    <span>' . $mese_visuale . '<br />' . $giorno . '</span>
                </div>
                
                <div class="appuntamento-card-details">
                    <div>
                        <h3>' . htmlspecialchars($p['nome_servizio']) . '</h3>
                        <p>Presso: ' . htmlspecialchars($p['nome_farmacia']) . '</p>
                    </div>
                    <div class="row">
                    
                        <a href="booking-details.php?id=' . $p['id'] . '" aria-label="Visualizza dettagli della prenotazione per ' . htmlspecialchars($p['nome_servizio']) . ' del ' . $giorno . ' ' . $mese_parlato . '">Dettagli</a>
                    
                    <form method="POST" action="process-cancellation.php">
                        <fieldset>
                                <legend class="sr-only">Disdici appuntamento per ' . htmlspecialchars($p['nome_servizio']) . ' del ' . $giorno . ' ' . $mese_parlato . '"</legend>
                                
                                <input type="hidden" name="prenotazione_id" value="' . $p['id'] . '">
                                
                                <button type="submit" class="btn-primary no-margin" onclick="return confirm(\'Sei sicuro di voler disdire questo appuntamento?\')">
                                    Disdici
                                </button>
                        </fieldset>
                    </form>
                    </div>
                </div>
            </li>';
        }
    } else {
        $htmlPrenotazioni = '<li class="appuntamento-card"><p>Nessuna prenotazione futura.</p></li>';
    }

    
    if ($prenotazioniShort && count($prenotazioniShort) > 0) {
        foreach ($prenotazioniShort as $p) {
            $timestamp = strtotime($p['data_appuntamento']);
            $mese_en = date("M", $timestamp);
            
            $mese_visuale = $mesi_it[$mese_en]; 
            $mese_parlato = $mesi_estesi[$mese_en]; 
            $giorno = date("d", $timestamp);
            $ora = date("H:i", strtotime($p['ora_appuntamento']));

            $htmlPrenotazioniShort .= '
            <li class="appuntamento-card">

                <div class="appuntamento-card-date" aria-hidden="true">
                    <span>' . $mese_visuale . '<br />' . $giorno . '</span>
                </div>
                
                <div class="appuntamento-card-details">
                    <div>
                        <h3>' . htmlspecialchars($p['nome_servizio']) . '</h3>
                        <p>Presso: ' . htmlspecialchars($p['nome_farmacia']) . '</p>
                    </div>
                    <div class="row">
                    
                        <a href="booking-details.php?id=' . $p['id'] . '" aria-label="Visualizza dettagli della prenotazione per ' . htmlspecialchars($p['nome_servizio']) . ' del ' . $giorno . ' ' . $mese_parlato . '">Dettagli</a>
                    
                    <form method="POST" action="process-cancellation.php">
                        <fieldset>
                                <legend class="sr-only">Disdici appuntamento per ' . htmlspecialchars($p['nome_servizio']) . ' del ' . $giorno . ' ' . $mese_parlato . '"</legend>
                                
                                <input type="hidden" name="prenotazione_id" value="' . $p['id'] . '">
                                
                                <button type="submit" class="btn-primary no-margin" onclick="return confirm(\'Sei sicuro di voler disdire questo appuntamento?\')">
                                    Disdici
                                </button>
                        </fieldset>
                    </form>
                    </div>
                </div>
            </li>';
        }
    } else {
        $htmlPrenotazioniShort = '<li class="appuntamento-card"><p>Nessuna prenotazione futura.</p></li>';
    }

    $db->closeConnection();
}

// 4. SOSTITUZIONE PLACEHOLDER (Dati Utente dalla Sessione)
$paginaHTML = str_replace('[nome_utente]', htmlspecialchars($_SESSION['nome']), $paginaHTML);
$paginaHTML = str_replace('[nome]', htmlspecialchars($_SESSION['nome']), $paginaHTML);
$paginaHTML = str_replace('[cognome]', htmlspecialchars($_SESSION['cognome']), $paginaHTML);
$paginaHTML = str_replace('[cognome_utente]', htmlspecialchars($_SESSION['cognome']), $paginaHTML);
$paginaHTML = str_replace('[username]', htmlspecialchars($_SESSION['username']), $paginaHTML);
$paginaHTML = str_replace('[email]', htmlspecialchars($_SESSION['email']), $paginaHTML);

// Formattazione data registrazione
$dataReg = isset($_SESSION['data_registrazione']) ? date("d/m/Y", strtotime($_SESSION['data_registrazione'])) : "N/D";
$paginaHTML = str_replace('[data_registrazione]', $dataReg, $paginaHTML);

// 5. SOSTITUZIONE PLACEHOLDER (Liste generate)
$paginaHTML = str_replace('[lista_prenotazioni]', $htmlPrenotazioniShort, $paginaHTML);
$paginaHTML = str_replace('[lista_prenotazioni_completa]', $htmlPrenotazioni, $paginaHTML);

// 6. STAMPA FINALE
echo $paginaHTML;

?>