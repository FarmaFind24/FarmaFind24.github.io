<?php
require_once "dbConnection.php";
use DB\DBAccess;

$paginaHTML = file_get_contents("farm_search.html");

$testoCercato = "";
$htmlRisultati = ""; 

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    
    $testoCercato = trim($_GET['q']);
    
    $db = new DBAccess();
    $connessioneOk = $db->openDBConnection();

    if ($connessioneOk) {
        $risultati = $db->cercaFarmacie($testoCercato);
        $db->closeConnection();
        
        if ($risultati && count($risultati) > 0) {
            
            foreach ($risultati as $row) {
                
                if (!empty($row['immagine'])) {
                    $srcImmagine = "assets/" . htmlspecialchars($row['immagine']);
                } else {
                    $srcImmagine = "assets/immagine_farmacia.jpg"; 
                }
                
                $htmlRisultati .= '<div class="farm-card">';
                $htmlRisultati .= '<img src="' . $srcImmagine . '" alt="Foto ' . htmlspecialchars($row['nome']) . '">';
                
                $htmlRisultati .= '<div class="farm-card-content">';
                $htmlRisultati .= '<h3>' . htmlspecialchars($row['nome']) . '</h3>';
                $htmlRisultati .= '<p>' . htmlspecialchars($row['indirizzo']) . ', ' . htmlspecialchars($row['citta']) . '</p>';
                $htmlRisultati .= '<span class="farm-orario">Tel: ' . htmlspecialchars($row['telefono']) . '</span>';
                $htmlRisultati .= '</div></div>';
            }
            
        } else {
            $htmlRisultati = '<p class="no-results">Nessun risultato trovato per "<strong>' . htmlspecialchars($testoCercato) . '</strong>".</p>';
        }
        
    } else {
        $htmlRisultati = '<p class="error">Errore di connessione al database.</p>';
    }
} 

$paginaHTML = str_replace('[listaFarmacie]', $htmlRisultati, $paginaHTML);

$paginaHTML = str_replace('[valore_ricerca]', htmlspecialchars($testoCercato), $paginaHTML);

echo $paginaHTML;
?>