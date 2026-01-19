<?php
session_start();
require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;

// 1. CONTROLLO SICUREZZA: Se non è loggato, via al login
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

if ($connessioneOk) {
    $prenotazioni = $db->getPrenotazioniUtente($_SESSION['user_id']);
    
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
                <span class="sr-only">Appuntamento per ' . htmlspecialchars($p['nome_servizio']) . ' il ' . $giorno . ' ' . $mese_parlato . ' alle ore ' . $ora . '.</span>

                <div class="appuntamento-card-date" aria-hidden="true">
                    <span>' . $mese_visuale . '<br />' . $giorno . '</span>
                </div>
                
                <div class="appuntamento-card-details">
                    <div>
                        <h3>' . htmlspecialchars($p['nome_servizio']) . '</h3>
                        <p>Presso: ' . htmlspecialchars($p['nome_farmacia']) . '</p>
                    </div>
                    <button type="button" class="btn-primary no-margin" 
                            aria-label="Disdici appuntamento per ' . htmlspecialchars($p['nome_servizio']) . ' del ' . $giorno . ' ' . $mese_parlato . '">
                        Disdici
                    </button>
                </div>
            </li>';
        }
    } else {
        $htmlPrenotazioni = '<li class="appuntamento-card"><p style="padding:1rem;">Nessuna prenotazione futura.</p></li>';
    }
    $db->closeConnection();
}

// 4. SOSTITUZIONE PLACEHOLDER (Dati Utente dalla Sessione)
// Questi dati li abbiamo salvati in $_SESSION durante il login (vedi process-login.php che abbiamo fatto prima)

$paginaHTML = str_replace('[nome_utente]', htmlspecialchars($_SESSION['nome']), $paginaHTML);
$paginaHTML = str_replace('[nome]', htmlspecialchars($_SESSION['nome']), $paginaHTML);
$paginaHTML = str_replace('[cognome]', htmlspecialchars($_SESSION['cognome']), $paginaHTML);
$paginaHTML = str_replace('[cognome_utente]', htmlspecialchars($_SESSION['cognome']), $paginaHTML);
$paginaHTML = str_replace('[username]', htmlspecialchars($_SESSION['username']), $paginaHTML);
$paginaHTML = str_replace('[email]', htmlspecialchars($_SESSION['email']), $paginaHTML);

// Formattazione data registrazione (se presente in sessione, altrimenti metti una data fissa o vuota)
$dataReg = isset($_SESSION['data_registrazione']) ? date("d/m/Y", strtotime($_SESSION['data_registrazione'])) : "N/D";
$paginaHTML = str_replace('[data_registrazione]', $dataReg, $paginaHTML);

// 5. SOSTITUZIONE PLACEHOLDER (Liste generate)
$paginaHTML = str_replace('[lista_prenotazioni]', $htmlPrenotazioni, $paginaHTML);
// Se vuoi usare la stessa lista anche nella sezione "Prenotazioni" in basso:
$paginaHTML = str_replace('[lista_prenotazioni_completa]', $htmlPrenotazioni, $paginaHTML);

// 6. STAMPA FINALE
echo $paginaHTML;
?>