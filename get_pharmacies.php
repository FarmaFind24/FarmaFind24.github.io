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

// VALIDAZIONE INPUT

// 1. Validazione service_id (deve essere un intero positivo)
$service_id = filter_var($_GET['service_id'], FILTER_VALIDATE_INT);
if ($service_id === false || $service_id <= 0) {
    $response['html'] = '<p class="error">ID servizio non valido.</p>';
    echo json_encode($response);
    exit;
}

// 2. Validazione city (lunghezza e caratteri permessi)
$city = trim($_GET['city']);
if (empty($city) || strlen($city) > 100) {
    $response['html'] = '<p class="error">Nome città non valido.</p>';
    echo json_encode($response);
    exit;
}

// 3. Sanitizzazione city (solo lettere, spazi, apostrofi, trattini)
if (!preg_match("/^[A-Za-zÀ-ù\s'\-]{1,100}$/", $city)) {
    $response['html'] = '<p class="error">Nome città contiene caratteri non validi.</p>';
    echo json_encode($response);
    exit;
}

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
            $response['message'] = '<p class="error">Nessuna farmacia disponibile a ' . htmlspecialchars($city) . ' o nei comuni limitrofi per questo servizio. <strong>Seleziona un altro comune per continuare.</strong></p>'; 
            $response['html'] = '';
            $response['noPharmacies'] = true; // Flag per indicare nessuna farmacia disponibile
        }
    }
    $db->closeConnection();
} else {
    $response['html'] = '<p class="error">Errore di connessione al database.</p>';
}

echo json_encode($response);
?>