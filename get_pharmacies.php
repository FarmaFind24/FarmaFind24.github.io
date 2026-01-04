<?php
header('Content-Type: application/json');
require_once "dbConnection.php";
use DB\DBAccess;

$response = [
    'message' => '',
    'html' => ''
];

if (!isset($_GET['service_id']) || !isset($_GET['city'])) {
    $response['html'] = '<p class="error">Dati mancanti per la ricerca.</p>';
    echo json_encode($response);
    exit;
}

$service_id = $_GET['service_id'];
$city = $_GET['city'];

$db = new DBAccess();
$connessioneOk = $db->openDBConnection();

if ($connessioneOk) {
    $farmacie = $db->getFarmaciePerServizioECitta($service_id, $city);

    if ($farmacie && count($farmacie) > 0) {
        // Farmacie trovate
        foreach ($farmacie as $farmacia) {
            $id = 'farm-' . htmlspecialchars($farmacia['id']);
            $response['html'] .= '<div class="farm-box">
                                <input type="radio" id="' . $id . '" name="pharmacy-selection" value="' . htmlspecialchars($farmacia['id']) . '" required>
                                <label for="' . $id . '" class="card-clickable-label">
                                    <span>
                                        <span class="farm-name">' . htmlspecialchars($farmacia['nome']) . '</span>
                                        <span class="farm-address">- ' . htmlspecialchars($farmacia['indirizzo']) . '</span>
                                    </span>
                                </label>
                            </div>';
        }
    } else {
        // Nessuna farmacia trovata, cerco nei dintorni
        $response['message'] = '<p>Nessuna farmacia trovata a ' . htmlspecialchars($city) . ' per il servizio scelto. Ecco una lista di farmacie nei comuni limitrofi:</p>';
        $farmacieVicine = $db->getFarmacieVicinePerServizio($service_id, $city);

        if ($farmacieVicine && count($farmacieVicine) > 0) {
            foreach ($farmacieVicine as $farmacia) {
                $id = 'farm-vicina-' . htmlspecialchars($farmacia['id']);
                $response['html'] .= '<div class="farm-box">
                                    <input type="radio" id="' . $id . '" name="pharmacy-selection" value="' . htmlspecialchars($farmacia['id']) . '" required>
                                    <label for="' . $id . '" class="card-clickable-label">
                                        <span>
                                            <span class="farm-name">' . htmlspecialchars($farmacia['nome']) . '</span>
                                            <span class="farm-address">- ' . htmlspecialchars($farmacia['indirizzo']) . ', ' . htmlspecialchars($farmacia['citta']) . '</span>
                                        </span>
                                    </label>
                                </div>';
            }
        } else {
            $response['message'] = ''; // Svuota il messaggio precedente
            $response['html'] = '<p class="no-results">Nessuna farmacia trovata a ' . htmlspecialchars($city) . ' o nei comuni limitrofi che offra il servizio selezionato.</p>';
        }
    }
    $db->closeConnection();
} else {
    $response['html'] = '<p class="error">Errore di connessione al database.</p>';
}

echo json_encode($response);
?>