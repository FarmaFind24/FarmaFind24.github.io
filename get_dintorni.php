<?php
header('Content-Type: application/json');
require_once "dbConnection.php";
use DB\DBAccess;

$response = [
    'message' => '',
    'html' => ''
];

if (!isset($_GET['city'])) {
    $response['html'] = '<p class="error">Dati mancanti per la ricerca.</p>';
    echo json_encode($response);
    exit;
}

$city = $_GET['city'];

$db = new DBAccess();
$connessioneOk = $db->openDBConnection();

if ($connessioneOk) {
    $farmacie = $db->getFarmacieDintorni($city);

    if ($farmacie && count($farmacie) > 0) {
        foreach ($farmacie as $farmacia) {
            if (!empty($farmacia['immagine'])) {
                $srcImmagine = "assets/" . htmlspecialchars($farmacia['immagine']);
            } else {
                $srcImmagine = "assets/immagine_farmacia.jpg"; 
            }

            // Nota: lo stato "Aperta/Chiusa" è hardcoded per ora.
            // Per renderlo dinamico servirebbe una logica sugli orari.
            $response['html'] .= '<div class="farm-card-mini">
                                    <div class="farm-img-container">
                                        <img src="' . $srcImmagine . '" alt="Foto ' . htmlspecialchars($farmacia['nome']) . '">
                                        <span class="farm-stato-open">Aperta</span>
                                    </div>
                                    <div class="farm-card-content">
                                        <h3 class="title-card">' . htmlspecialchars($farmacia['nome']) . '</h3>
                                        <p>' . htmlspecialchars($farmacia['indirizzo']) . ', ' . htmlspecialchars($farmacia['citta']) . '</p>
                                        <div class="row-btn">
                                            <button type="button" class="outlined-btn" aria-label="Contatta via Email">Email</button>
                                            <button type="button" class="btn-primary">Dettagli</button>
                                        </div>
                                    </div>
                                  </div>';
        }
    } else {
        $response['message'] = '<p class="no-results">Nessuna farmacia trovata nei dintorni di ' . htmlspecialchars($city) . '.</p>';
    }
    $db->closeConnection();
} else {
    $response['html'] = '<p class="error">Errore di connessione al database.</p>';
}

echo json_encode($response);
?>