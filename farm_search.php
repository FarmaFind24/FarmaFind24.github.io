<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        try {
            $risultati = $db->cercaFarmacie($testoCercato);
            
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
                    $htmlRisultati .= '<h3 class="title-card">' . htmlspecialchars($row['nome']) . '</h3>';
                    $htmlRisultati .= '<p>' . htmlspecialchars($row['indirizzo']) . ', ' . htmlspecialchars($row['citta']) . '</p>';
                    $htmlRisultati .= '<span class="farm-orario">Tel: ' . htmlspecialchars($row['telefono']) . '</span>';

                    $idFarm = $row['id'];
                    $servizi = $db->getServiziFarmacia($idFarm);
                    if ($servizi && count($servizi) > 0) {
                        $htmlRisultati .= '<span>Servizi</span><ul aria-label="Servizi disponibili">';
                        foreach ($servizi as $servizio) {
                            $htmlRisultati .= '<li>' . htmlspecialchars($servizio['nome_servizio']) . '</li>';
                        }
                        $htmlRisultati .= '</ul>';
                    }
                    $htmlRisultati .= '<div class="row-btn">
                                        <button type="button" class="outlined-btn" aria-label="Contatta via Email">Email</button>
                                        <button type="button" class="btn-primary">Dettagli</button>
                                        </div>';
                    $htmlRisultati .= '</div></div>';
                }
            } else {
                $htmlRisultati = '<p class="no-results">Nessuna farmacia o città trovata per "<strong>' . htmlspecialchars($testoCercato) . '</strong>".</p>';
            }
        } catch (\mysqli_sql_exception $e) {
            // Per il debug, puoi registrare l'errore: error_log($e->getMessage());
            $htmlRisultati = '<p class="error">Si è verificato un errore durante la ricerca. Riprova più tardi.</p>';
        } finally {
            $db->closeConnection();
        }
    } else {
        $htmlRisultati = '<p class="error">Errore di connessione al database.</p>';
    }
} 

$paginaHTML = str_replace('[listaFarmacie]', $htmlRisultati, $paginaHTML);
$paginaHTML = str_replace('[valore_ricerca]', htmlspecialchars($testoCercato), $paginaHTML);

echo $paginaHTML;
?>