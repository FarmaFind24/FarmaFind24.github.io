<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;

$paginaHTML = file_get_contents("index.html");

// --- Inizializzazione dei segnaposto ---
$htmlCitta = "";
$htmlFarmacieDintorni = ""; 
$indexMessage = '<p class="instruction">Seleziona un comune per vedere le farmacie nei dintorni.</p>';
$selectedCity = $_POST['city'] ?? null; // Recupera la città dal POST (per fallback no-JS)

// --- Connessione al DB ---
$db = new DBAccess();
$connessioneOk = $db->openDBConnection();

if ($connessioneOk) {
    try {
        // Carica la lista delle città per la dropdown
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

        // Se una città è stata inviata tramite POST (fallback no-JS), cerca le farmacie
        if ($selectedCity) {
            $farmacie = $db->getFarmacieDintorni($selectedCity);

            if ($farmacie && count($farmacie) > 0) {
                $indexMessage = ''; // Pulisce il messaggio di istruzioni
                foreach ($farmacie as $farmacia) {
                    if (!empty($farmacia['immagine'])) {
                        $srcImmagine = "assets/" . htmlspecialchars($farmacia['immagine']);
                    } else {
                        $srcImmagine = "assets/immagine_farmacia.jpg"; 
                    }

                    $htmlFarmacieDintorni .= '<div class="farm-card-mini">';
                    $htmlFarmacieDintorni .=    '<div class="farm-img-container">';
                    $htmlFarmacieDintorni .=        '<img src="' . $srcImmagine . '" alt="Foto ' . htmlspecialchars($farmacia['nome']) . '">';

                    $idFarmacia = $farmacia['id'];
                    $isAperta = $db->isFarmaciaAperta($idFarmacia);
                    if ($isAperta) {
                        $htmlFarmacieDintorni .=        '<span class="farm-stato-open">Aperta</span>';
                    } else {
                        $htmlFarmacieDintorni .=        '<span class="farm-stato-closed">Chiusa</span>';
                    }
                    $htmlFarmacieDintorni .=    '</div>';
                    $htmlFarmacieDintorni .=    '<div class="farm-card-content">';
                    $htmlFarmacieDintorni .=        '<h3 class="title-card">' . htmlspecialchars($farmacia['nome']) . '</h3>';
                    $htmlFarmacieDintorni .=        '<p>' . htmlspecialchars($farmacia['indirizzo']) . ', ' . htmlspecialchars($farmacia['citta']) . '</p>';
                    $htmlFarmacieDintorni .=       '<div class="row-btn">';
                    $htmlFarmacieDintorni .=            '<button type="button" class="outlined-btn" aria-label="Contatta via Email">Email</button>';
                    $htmlFarmacieDintorni .=            '<button type="button" class="btn-primary">Dettagli</button>';
                    $htmlFarmacieDintorni .=        '</div>';
                    $htmlFarmacieDintorni .=    '</div>';
                    $htmlFarmacieDintorni .= '</div>';
                }
            } else {
                $indexMessage = '<p class="no-results">Nessuna farmacia trovata nei dintorni di ' . htmlspecialchars($selectedCity) . '.</p>';
            }
        }

    } catch (\mysqli_sql_exception $e) {
        $htmlCitta = '<option value="">Errore nel caricamento dati</option>';
        $indexMessage = '<p class="error">Si è verificato un errore. Riprova più tardi.</p>';
    } finally {
        $db->closeConnection();
    }
} else {
    // Gestione errore connessione DB
    $htmlCitta = '<option value="">Errore di connessione</option>';
    $indexMessage = '<p class="error">Impossibile caricare i dati. Riprova più tardi.</p>';
}

$paginaHTML = str_replace('[citta_options]', $htmlCitta, $paginaHTML);
$paginaHTML = str_replace('[farmacieDintorni]', $htmlFarmacieDintorni, $paginaHTML);
$paginaHTML = str_replace('[index_message]', $indexMessage, $paginaHTML);

// Gestione Area Personale nella navbar
$paginaHTML = str_replace('[area_personale_href]', getAreaPersonaleHref(), $paginaHTML);
$paginaHTML = str_replace('[area_personale_text]', getAreaPersonaleText(), $paginaHTML);

echo $paginaHTML;
?>