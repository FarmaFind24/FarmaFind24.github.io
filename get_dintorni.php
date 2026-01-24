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

// VALIDAZIONE INPUT

// 1. Validazione city (lunghezza e caratteri permessi)
$city = trim($_GET['city']);
if (empty($city) || strlen($city) > 100) {
    $response['html'] = '<p class="error">Nome città non valido.</p>';
    echo json_encode($response);
    exit;
}

// 2. Sanitizzazione city (solo lettere, spazi, apostrofi, trattini)
if (!preg_match("/^[A-Za-zÀ-ù\s'\-]{1,100}$/", $city)) {
    $response['html'] = '<p class="error">Nome città contiene caratteri non validi.</p>';
    echo json_encode($response);
    exit;
}

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

            // Verifica se la farmacia è aperta in base agli orari
            $idFarmacia = $farmacia['id'];
            $isAperta = $db->isFarmaciaAperta($idFarmacia);
            $statoClass = $isAperta ? 'farm-stato-open' : 'farm-stato-closed';
            $statoText = $isAperta ? 'Aperta' : 'Chiusa';

            $response['html'] .= '<div class="farm-card-mini">
                                    <div class="farm-img-container">
                                        <img src="' . $srcImmagine . '" alt="Foto ' . htmlspecialchars($farmacia['nome']) . '">
                                        <span class="' . $statoClass . '">' . $statoText . '</span>
                                    </div>
                                    <div class="farm-card-content">
                                        <h3 class="title-card">' . htmlspecialchars($farmacia['nome']) . '</h3>
                                        <p>' . htmlspecialchars($farmacia['indirizzo']) . ', ' . htmlspecialchars($farmacia['citta']) . '</p>
                                        <div class="row-btn">
                                            <a href="mailto:farmafind24@gmail.com" class="btn-like outlined" aria-label="Contatta via Email">Email</a>
                                            <a href="info-farm.php?id=' . $farmacia['id'] . '" class="btn-like primary">Dettagli</a>
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