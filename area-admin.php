<?php
session_start();
// Impedisce al browser di memorizzare la pagina
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;

// 1. CONTROLLO SICUREZZA: Se non è loggato, via al login
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: area-login.html");
    exit;
}

// 2. CONTROLLO RUOLO: Solo admin possono accedere
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: area-personale.php");
    exit;
}

// 3. CARICAMENTO TEMPLATE HTML
$paginaHTML = file_get_contents("area-admin.html");

// 4. GESTIONE MESSAGGI DI FEEDBACK
$feedbackMessages = "";
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'farmacia_added':
            $feedbackMessages = '<div class="message success" role="alert"><p>Farmacia aggiunta con successo!</p></div>';
            break;
        case 'farmacia_deleted':
            $feedbackMessages = '<div class="message success" role="alert"><p>Farmacia eliminata con successo!</p></div>';
            break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'db_error':
            $feedbackMessages = '<div class="message error" role="alert"><p>Errore di connessione al database.</p></div>';
            break;
        case 'empty_fields':
            $feedbackMessages = '<div class="message error" role="alert"><p>Compila tutti i campi obbligatori.</p></div>';
            break;
        case 'invalid_phone':
            $feedbackMessages = '<div class="message error" role="alert"><p>Formato telefono non valido.</p></div>';
            break;
        case 'duplicate_farmacia':
            $feedbackMessages = '<div class="message error" role="alert"><p>Esiste già una farmacia con questo nome nella stessa città.</p></div>';
            break;
        case 'insert_failed':
            $feedbackMessages = '<div class="message error" role="alert"><p>Errore durante l\'inserimento della farmacia.</p></div>';
            break;
        case 'delete_failed':
            $feedbackMessages = '<div class="message error" role="alert"><p>Errore durante l\'eliminazione della farmacia.</p></div>';
            break;
    }
}

// 5. RECUPERO DATI DAL DATABASE (Prenotazioni)
$db = new DBAccess();
$connessioneOk = $db->openDBConnection();
$htmlPrenotazioni = "";
$htmlPrenotazioniShort = "";
$htmlListaFarmacie = "";
$htmlFormAggiungi = "";

if ($connessioneOk) {
    $prenotazioni = $db->getAllPrenotazioni();
    $prenotazioniShort = $db->getAllPrenotazioniPreview();
    
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
                    <div>
                        <h3>Prenotato da: @' . htmlspecialchars($p['username']) . '</h3>
                    </div>
                    <form method="POST" action="process-cancellation.php" style="margin: 0;">
                        <input type="hidden" name="prenotazione_id" value="' . $p['id'] . '">
                        <button type="submit" class="btn-primary no-margin" 
                                aria-label="Cancella appuntamento per ' . htmlspecialchars($p['nome_servizio']) . ' del ' . $giorno . ' ' . $mese_parlato . '"
                                onclick="return confirm(\'Sei sicuro di voler cancellare questo appuntamento?\')">
                            Cancella
                        </button>
                    </form>
                </div>
            </li>';
        }
    } else {
        $htmlPrenotazioni = '<li class="appuntamento-card"><p style="padding:1rem;">Nessuna prenotazione futura.</p></li>';
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
                    <form method="POST" action="process-cancellation.php" style="margin: 0;">
                        <input type="hidden" name="prenotazione_id" value="' . $p['id'] . '">
                        <button type="submit" class="btn-primary no-margin" 
                                aria-label="Disdici appuntamento per ' . htmlspecialchars($p['nome_servizio']) . ' del ' . $giorno . ' ' . $mese_parlato . '"
                                onclick="return confirm(\'Sei sicuro di voler disdire questo appuntamento?\')">
                            Disdici
                        </button>
                    </form>
                </div>
            </li>';
        }
    } else {
        $htmlPrenotazioniShort = '<li class="appuntamento-card"><p style="padding:1rem;">Nessuna prenotazione futura.</p></li>';
    }

    // === SEZIONE GESTIONE FARMACIE ===
    $farmacie = $db->getAllFarmacie();
    
    if ($farmacie && count($farmacie) > 0) {
        $htmlListaFarmacie = '<table class="data-table" aria-describedby="farmacie-table-description">
            <caption class="sr-only" id="farmacie-table-description">Elenco delle farmacie registrate nel sistema con le opzioni di eliminazione.</caption>
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">Indirizzo</th>
                    <th scope="col">Città</th>
                    <th scope="col">Telefono</th>
                    <th scope="col">Azioni</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($farmacie as $farmacia) {
            $htmlListaFarmacie .= '<tr>
                <td>' . htmlspecialchars($farmacia['nome']) . '</td>
                <td>' . htmlspecialchars($farmacia['indirizzo']) . '</td>
                <td>' . htmlspecialchars($farmacia['citta']) . '</td>
                <td>' . htmlspecialchars($farmacia['telefono']) . '</td>
                <td>
                    <form action="process-delete-farmacia.php" method="POST" class="inline-form" onsubmit="return confirm(\'Sei sicuro di voler eliminare la farmacia ' . htmlspecialchars($farmacia['nome'], ENT_QUOTES) . '? Questa azione eliminerà anche tutti i dati associati (prenotazioni, servizi, orari).\');">
                        <input type="hidden" name="id_farmacia" value="' . $farmacia['id'] . '">
                        <button type="submit" class="btn-delete" aria-label="Elimina farmacia ' . htmlspecialchars($farmacia['nome']) . '">
                            <span class="material-symbols-outlined" aria-hidden="true">delete</span>
                            Elimina
                        </button>
                    </form>
                </td>
            </tr>';
        }
        
        $htmlListaFarmacie .= '</tbody></table>';
    } else {
        $htmlListaFarmacie = '<p class="no-data">Nessuna farmacia presente nel sistema.</p>';
    }

    // === FORM AGGIUNGI FARMACIA ===
    $comuni = $db->getComuniProvinciaPadova();
    $htmlComuniOptions = '<option value="" disabled selected>Seleziona un comune</option>';
    foreach ($comuni as $comune) {
        $htmlComuniOptions .= '<option value="' . htmlspecialchars($comune) . '">' . htmlspecialchars($comune) . '</option>';
    }

    $db->closeConnection();
} else {
    $htmlPrenotazioni = '<li><p class="error">Errore di connessione al database.</p></li>';
    $htmlPrenotazioniShort = '<li><p class="error">Errore di connessione al database.</p></li>';
    $htmlListaFarmacie = '<p class="error">Errore di connessione al database.</p>';
    $htmlFormAggiungi = '<p class="error">Impossibile caricare il form. Errore di connessione al database.</p>';
}

// 4. SOSTITUZIONE PLACEHOLDER (Dati Utente dalla Sessione)
$paginaHTML = str_replace('[nome_utente]', htmlspecialchars($_SESSION['nome']), $paginaHTML);
$paginaHTML = str_replace('[nome]', htmlspecialchars($_SESSION['nome']), $paginaHTML);
$paginaHTML = str_replace('[cognome]', htmlspecialchars($_SESSION['cognome']), $paginaHTML);
$paginaHTML = str_replace('[cognome_utente]', htmlspecialchars($_SESSION['cognome']), $paginaHTML);
$paginaHTML = str_replace('[username]', htmlspecialchars($_SESSION['username']), $paginaHTML);
$paginaHTML = str_replace('[email]', htmlspecialchars($_SESSION['email']), $paginaHTML);



// 5. SOSTITUZIONE PLACEHOLDER (Liste generate)
$paginaHTML = str_replace('[lista_prenotazioni]', $htmlPrenotazioniShort, $paginaHTML);
$paginaHTML = str_replace('[lista_prenotazioni_completa]', $htmlPrenotazioni, $paginaHTML);
$paginaHTML = str_replace('[feedback_messages]', $feedbackMessages, $paginaHTML);
$paginaHTML = str_replace('[lista_farmacie_admin]', $htmlListaFarmacie, $paginaHTML);
$paginaHTML = str_replace('[comuni]', $htmlComuniOptions, $paginaHTML);

// 6. STAMPA FINALE
echo $paginaHTML;

?>