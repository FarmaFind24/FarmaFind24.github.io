<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;

$paginaHTML = file_get_contents("info-farm.html");

// Verifica che sia stato passato l'ID della farmacia
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: farm-search.php?error=missing_pharmacy_id");
    exit;
}

$idFarmacia = (int)$_GET['id'];

$db = new DBAccess();
$connessioneOk = $db->openDBConnection();

if (!$connessioneOk) {
    // Gestione errore connessione
    $paginaHTML = str_replace('[nomeFarmacia]', 'Errore', $paginaHTML);
    $paginaHTML = str_replace('[statusFarmacia]', '', $paginaHTML);
    $paginaHTML = str_replace('[coverImage]', 'src="assets/immagine_farmacia.webp"', $paginaHTML);
    $paginaHTML = str_replace('[infoGenerali]', '<p class="error">Errore di connessione al database.</p>', $paginaHTML);
    $paginaHTML = str_replace('[orariApertura]', '', $paginaHTML);
    $paginaHTML = str_replace('[serviziOfferti]', '', $paginaHTML);
    echo $paginaHTML;
    exit;
}

try {
    // Recupera i dati della farmacia
    $farmacia = $db->getFarmaciaById($idFarmacia);
    
    if (!$farmacia) {
        $db->closeConnection();
        header("Location: farm-search.php?error=pharmacy_not_found");
        exit;
    }
    
    // Nome farmacia
    $nomeFarmacia = htmlspecialchars($farmacia['nome']);
    
    // Status (Aperto/Chiuso)
    $isAperta = $db->isFarmaciaAperta($idFarmacia);
    $statusHTML = '<div class="status-container ' . ($isAperta ? 'status-aperto' : 'status-chiuso') . '">' .
              '<span class="' . ($isAperta ? 'aperto' : 'chiuso') . '">' . 
              ($isAperta ? 'Aperto' : 'Chiuso') . '</span>' .
              '</div>';
    // Immagine
    $immagine = !empty($farmacia['immagine']) 
                ? 'assets/farmCovers/' . htmlspecialchars($farmacia['immagine']) 
                : 'assets/immagine_farmacia.webp';
    $altImmagine = 'Facciata della ' . $nomeFarmacia;
    
    // Informazioni generali
    $infoGeneraliHTML = '<p>Indirizzo: ' . htmlspecialchars($farmacia['indirizzo']) . 
                       ', ' . htmlspecialchars($farmacia['citta']) . '</p>';
    $telefonoPulito = str_replace(' ', '', $farmacia['telefono']);
    $infoGeneraliHTML .= '<p>Telefono: <a href="tel:' . htmlspecialchars($telefonoPulito) . '" class="phone-link">' . htmlspecialchars($farmacia['telefono']) . '</a></p>';
    
    if (!empty($farmacia['email'])) {
        $infoGeneraliHTML .= '<p>Email: ' . htmlspecialchars($farmacia['email']) . '</p>';
    }
    
    // Orari di apertura - usa il metodo esistente getOrariFarmacia
    $giorniSettimana = ['Domenica', 'Luned&igrave', 'Marted&igrave', 'Mercoled&igrave', 'Gioved&igrave', 'Venerd&igrave', 'Sabato'];
    $orariHTML = '';
    
    // Lunedì a Sabato
    for ($i = 1; $i <= 6; $i++) {
        $orari = $db->getOrariFarmacia($idFarmacia, $i);
        
        $orariHTML .= '<div class="OrarioGiorno">';
        
        if (empty($orari)) {
            $orariHTML .= '<p>' . $giorniSettimana[$i] . ': Chiuso</p>';
        } else {
            $fasce = [];
            $fasceAccessibili = [];
            
            foreach ($orari as $orario) {
                $apertura = substr($orario['ora_apertura'], 0, 5);
                $chiusura = substr($orario['ora_chiusura'], 0, 5);
                $fasce[] = $apertura . ' - ' . $chiusura;
                
                // Versione accessibile per screen reader
                $oraAperturaReadable = str_replace(':', ' e ', $apertura);
                $oraChiusuraReadable = str_replace(':', ' e ', $chiusura);
                $fasceAccessibili[] = 'dalle ' . $oraAperturaReadable . ' alle ' . $oraChiusuraReadable;
            }
            
            $testoVisivo = $giorniSettimana[$i] . ': ' . implode(', ', $fasce);
            $testoAccessibile = $giorniSettimana[$i] . ' ' . implode('. ' . $giorniSettimana[$i] . ' ', $fasceAccessibili) . '.';
            
            $orariHTML .= '<p aria-hidden="true">' . $testoVisivo . '</p>';
            $orariHTML .= '<p class="sr-only">' . $testoAccessibile . '</p>';
        }
        
        $orariHTML .= '</div>';
    }
    
    // Domenica separata
    $orariDomenica = $db->getOrariFarmacia($idFarmacia, 0);
    $orariHTML .= '<div class="OrarioGiorno' . (empty($orariDomenica) ? ' chiuso' : '') . '">';
    if (empty($orariDomenica)) {
        $orariHTML .= '<p>Domenica: Chiuso</p>';
    } else {
        $fasce = [];
        foreach ($orariDomenica as $orario) {
            $fasce[] = substr($orario['ora_apertura'], 0, 5) . ' - ' . substr($orario['ora_chiusura'], 0, 5);
        }
        $orariHTML .= '<p>Domenica: ' . implode(', ', $fasce) . '</p>';
    }
    $orariHTML .= '</div>';
    
    // Servizi offerti - usa il metodo esistente getServiziFarmacia
    $servizi = $db->getServiziFarmacia($idFarmacia);
    $serviziHTML = '';

    if ($servizi && count($servizi) > 0) {
        $serviziHTML = '<ul class="lista-servizi" aria-label="lista servizi">';
        foreach ($servizi as $servizio) {
            $serviziHTML .= '<li>';
            $serviziHTML .= '<p><strong>' . htmlspecialchars($servizio['nome_servizio']) . '</strong></p>';
            if (!empty($servizio['descrizione'])) {
                $serviziHTML .= '<p class="descrizione-servizio">' . htmlspecialchars($servizio['descrizione']) . '</p>';
            }
            $serviziHTML .= '</li>';
        }
        $serviziHTML .= '</ul>';
    } else {
        $serviziHTML = '<p>Nessun servizio disponibile al momento.</p>';
    }
    
    // Sostituzioni nel template HTML
    $paginaHTML = str_replace('[nomeFarmacia]', $nomeFarmacia, $paginaHTML);
    $paginaHTML = str_replace('[statusFarmacia]', $statusHTML, $paginaHTML);
    $paginaHTML = str_replace('[coverImage]', $immagine, $paginaHTML);
    $paginaHTML = str_replace('[coverAlt]', $altImmagine, $paginaHTML);
    $paginaHTML = str_replace('[infoGenerali]', $infoGeneraliHTML, $paginaHTML);
    $paginaHTML = str_replace('[orariApertura]', $orariHTML, $paginaHTML);
    $paginaHTML = str_replace('[serviziOfferti]', $serviziHTML, $paginaHTML);
    // Gestione Area Personale nella navbar
    $paginaHTML = str_replace('[area_personale_href]', getAreaPersonaleHref(), $paginaHTML);
    $paginaHTML = str_replace('[area_personale_text]', getAreaPersonaleText(), $paginaHTML);
    $db->closeConnection();
    
} catch (\mysqli_sql_exception $e) {
    error_log($e->getMessage());
    $paginaHTML = str_replace('[nomeFarmacia]', 'Errore', $paginaHTML);
    $paginaHTML = str_replace('[statusFarmacia]', '', $paginaHTML);
    $paginaHTML = str_replace('[infoGenerali]', '<p class="error">Si � verificato un errore. Riprova pi� tardi.</p>', $paginaHTML);
    $paginaHTML = str_replace('[orariApertura]', '', $paginaHTML);
    $paginaHTML = str_replace('[serviziOfferti]', '', $paginaHTML);
}

echo $paginaHTML;
?>