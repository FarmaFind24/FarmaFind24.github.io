<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 
require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: area-login.php");
    exit;
}

if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    header("Location: area-personale.php");
    exit;
}

$paginaHTML = file_get_contents("area-admin.html");

$feedbackMessages = "";
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'farmacia_added':
            $feedbackMessagesSuccess = '<div class="success-message" role="status" aria-live="assertive" tabindex="-1"><p>Farmacia aggiunta con successo!</p></div>';
            break;
        case 'farmacia_deleted':
            $feedbackMessagesSuccess = '<div id="success-feedback" class="success-message" role="status" aria-live="polite" tabindex="-1"><p>Farmacia eliminata con successo!</p></div>';
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

$db = new DBAccess();
$connessioneOk = $db->openDBConnection();
$htmlPrenotazioni = "";
$htmlPrenotazioniShort = "";
$htmlListaFarmacie = "";
$htmlFormAggiungi = "";

if ($connessioneOk) {
    $prenotazioni = $db->getAllPrenotazioni();
    $prenotazioniShort = $db->getAllPrenotazioniPreview();

    $mesi_it = [
        "Jan" => "GEN", "Feb" => "FEB", "Mar" => "MAR", "Apr" => "APR",
        "May" => "MAG", "Jun" => "GIU", "Jul" => "LUG", "Aug" => "AGO",
        "Sep" => "SET", "Oct" => "OTT", "Nov" => "NOV", "Dec" => "DIC"
    ];

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
                        <p>Prenotato da: @' . htmlspecialchars($p['username']) . '</p>
                    
                    <form method="POST" action="process-cancellation.php">
                        <fieldset>
                                <legend class="sr-only">Cancella appuntamento per ' . htmlspecialchars($p['nome_servizio']) . ' del ' . $giorno . ' ' . $mese_parlato . '"</legend>
                                
                                <input type="hidden" name="prenotazione_id" value="' . $p['id'] . '">
                                
                                <button type="submit" class="btn-primary no-margin" onclick="return confirm(\'Sei sicuro di voler cancellare questo appuntamento?\')">
                                    Cancella
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
                    <form method="POST" action="process-cancellation.php">
                        <fieldset>
                                <legend class="sr-only">Cancella appuntamento per ' . htmlspecialchars($p['nome_servizio']) . ' del ' . $giorno . ' ' . $mese_parlato . '"</legend>
                                
                                <input type="hidden" name="prenotazione_id" value="' . $p['id'] . '">
                                
                                <button type="submit" class="btn-primary no-margin" onclick="return confirm(\'Sei sicuro di voler cancellare questo appuntamento?\')">
                                    Cancella
                                </button>
                        </fieldset>
                    </form>
                </div>
            </li>';
        }
    } else {
        $htmlPrenotazioniShort = '<li class="appuntamento-card"><p>Nessuna prenotazione futura.</p></li>';
    }

    $farmacie = $db->getAllFarmacie();
    
    if ($farmacie && count($farmacie) > 0) {
        $htmlListaFarmacie = '
        <p id="descr">Di seguito è riportato l\'elenco delle farmacie registrate nel sistema. I dettagli sono organizzati in colonne, comprende nome, indirizzo, città, telefono di ciascuna farmacia e il <span lang="en">link</span> per visualizzare i dettagli o il bottone per eliminarla definitivamente dal sistema. <strong>Attenzione:</strong> l\'eliminazione di una farmacia comporta la rimozione permanente di tutti i dati associati, incluse le prenotazioni effettuate dagli utenti.</p>
        
        <table class="data-table" aria-describedby="descr">
            <caption class="sr-only">Elenco delle farmacie registrate nel sistema.</caption>
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">Indirizzo</th>
                    <th scope="col">Città</th>
                    <th scope="col">Telefono</th>
                    <th scope="col">Dettagli</th>
                    <th scope="col">Elimina</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($farmacie as $farmacia) {
            $htmlListaFarmacie .= '<tr>
                <th data-title="Nome" scope="row">' . htmlspecialchars($farmacia['nome']) . '</th>
                <td data-title="Indirizzo">' . htmlspecialchars($farmacia['indirizzo']) . '</td>
                <td data-title="Città">' . htmlspecialchars($farmacia['citta']) . '</td>
                <td data-title="Telefono">' . htmlspecialchars($farmacia['telefono']) . '</td>
                <td>
                    <a href="info-farm.php?id=' . $farmacia['id'] . '" aria-label="Visualizza dettagli di ' . htmlspecialchars($farmacia['nome']) . '">Dettagli</a>
                </td>
                <td>
                    <form action="conferma_eliminazione_farmacia.php" method="POST" class="inline-form">
                    <fieldset>
                        <legend class="sr-only">Elimina farmacia ' . htmlspecialchars($farmacia['nome']) . '</legend>
                        <input type="hidden" name="tipo" value="farmacia">
                        <input type="hidden" name="citta" value="' . $farmacia['citta'] . '">
                        <input type="hidden" name="nome" value="' . htmlspecialchars($farmacia['nome']) . '">
                        <button type="submit" class="btn-delete">
                            Elimina
                        </button>
                    </fieldset>
                    </form>
                </td>
            </tr>';
        }
        
        $htmlListaFarmacie .= '</tbody></table>';
    } else {
        $htmlListaFarmacie = '<p class="no-data">Nessuna farmacia presente nel sistema.</p>';
    }

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

$paginaHTML = str_replace('[nome_utente]', htmlspecialchars($_SESSION['nome']), $paginaHTML);
$paginaHTML = str_replace('[nome]', htmlspecialchars($_SESSION['nome']), $paginaHTML);
$paginaHTML = str_replace('[cognome]', htmlspecialchars($_SESSION['cognome']), $paginaHTML);
$paginaHTML = str_replace('[cognome_utente]', htmlspecialchars($_SESSION['cognome']), $paginaHTML);
$paginaHTML = str_replace('[username]', htmlspecialchars($_SESSION['username']), $paginaHTML);
$paginaHTML = str_replace('[email]', htmlspecialchars($_SESSION['email']), $paginaHTML);
$paginaHTML = str_replace('[lista_prenotazioni]', $htmlPrenotazioniShort, $paginaHTML);
$paginaHTML = str_replace('[lista_prenotazioni_completa]', $htmlPrenotazioni, $paginaHTML);
$paginaHTML = str_replace('[feedback_messages]', $feedbackMessages, $paginaHTML);
$paginaHTML = str_replace('[feedback_messages_success]', $feedbackMessagesSuccess, $paginaHTML);
$paginaHTML = str_replace('[lista_farmacie_admin]', $htmlListaFarmacie, $paginaHTML);
$paginaHTML = str_replace('[comuni]', $htmlComuniOptions, $paginaHTML);

// 6. STAMPA FINALE
echo $paginaHTML;

?>