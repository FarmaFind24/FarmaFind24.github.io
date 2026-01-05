<?php
namespace DB;

class DBAccess {

	private const HOST_DB = "localhost";
	private const DATABASE_NAME = "mrocco";
	private const USERNAME = "mrocco";
	private const PASSWORD = "Iegaemie1eiqueiz";

	private $connection;

	public function openDBConnection() {

		mysqli_report(MYSQLI_REPORT_ERROR);

		try {
			$this->connection = mysqli_connect(DBAccess::HOST_DB, DBAccess::USERNAME, DBAccess::PASSWORD, DBAccess::DATABASE_NAME);
			mysqli_set_charset($this->connection, "utf8mb4");
			return true;
		} catch (\mysqli_sql_exception $e) {
			return false;
		}
	}


	public function closeConnection() {
		mysqli_close($this->connection);
	}


	// 1. Funzione per il LOGIN (User e Admin)
    public function eseguiLogin($username, $password) {
        // Usiamo i ? per evitare che qualcuno entri con trucchi SQL
        $query = "SELECT * FROM utenti WHERE username = ? AND password = ?";
        
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $password); // "ss" significa due stringhe
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return $row; // Ritorna i dati dell'utente (id, ruolo, ecc.)
        } else {
            return false; // Login fallito
        }
    }

    // 2. Funzione per CERCARE FARMACI (quella che mostra compresse/sciroppo separati)
    public function cercaFarmaci($testoRicerca) {
        $query = "SELECT * FROM farmaci 
                  WHERE nome_commerciale LIKE ? 
                  OR principio_attivo LIKE ? 
                  ORDER BY nome_commerciale, forma_farmaceutica";
        
        $stmt = mysqli_prepare($this->connection, $query);
        
        $searchTerm = "%" . $testoRicerca . "%"; // Aggiungiamo i % per la ricerca parziale
        mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
        
        mysqli_stmt_execute($stmt);
        $queryResult = mysqli_stmt_get_result($stmt);

        $result = array();
        while ($row = mysqli_fetch_assoc($queryResult)) {
            array_push($result, $row);
        }
        return $result;
    }

    // 3. Funzione per ottenere la lista delle FARMACIE
    public function getListaFarmacie() {
        $query = "SELECT * FROM farmacie ORDER BY nome ASC";
        $queryResult = mysqli_query($this->connection, $query);
        
        if (!$queryResult || mysqli_num_rows($queryResult) == 0) {
            return null;
        } else {
            $result = array();
            while ($row = mysqli_fetch_assoc($queryResult)) {
                array_push($result, $row);
            }
            return $result;
        }
    }
    
    // 4. Funzione per PRENOTARE un servizio (Esempio di INSERT)
    public function prenotaServizio($idUtente, $idFarmaciaServizio, $dataOra, $note) {
        $query = "INSERT INTO prenotazioni (utente_id, farmacia_servizio_id, data_ora_appuntamento, note_cliente) VALUES (?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->connection, $query);
        // "iiss" sta per: intero, intero, stringa, stringa
        mysqli_stmt_bind_param($stmt, "iiss", $idUtente, $idFarmaciaServizio, $dataOra, $note);
        
        return mysqli_stmt_execute($stmt); // Ritorna true se andato a buon fine
    }

    // Aggiungi dentro class DBAccess in dbConnection.php
	
	public function cercaFarmacie($testoRicerca) {
		// Cerca per nome della farmacia O per città
		$query = "SELECT * FROM farmacie 
				  WHERE nome LIKE ? OR citta LIKE ? 
				  ORDER BY nome ASC";
		
		$stmt = mysqli_prepare($this->connection, $query);
		$searchTerm = "%" . $testoRicerca . "%";
		mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
		
		mysqli_stmt_execute($stmt);
		$queryResult = mysqli_stmt_get_result($stmt);
	
		$result = array();
		while ($row = mysqli_fetch_assoc($queryResult)) {
			array_push($result, $row);
		}
		return $result;
	}

    // 5. Funzione per cercare FARMACIE per nome o città (precedentemente duplicata)
    public function cercaFarmaciePerNomeOCitta($testoRicerca) {
        $query = "SELECT * FROM farmacie WHERE nome LIKE ? OR citta LIKE ? ORDER BY nome ASC";
        $stmt = mysqli_prepare($this->connection, $query);
        $searchTerm = "%" . $testoRicerca . "%";
        mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $queryResult = mysqli_stmt_get_result($stmt);
        return $queryResult->fetch_all(MYSQLI_ASSOC);
    }

    // 6. Funzione per ottenere la lista dei SERVIZI disponibili per la prenotazione
    public function getServiziDisponibili() {
        $query = "SELECT id, nome_servizio, descrizione, durata_media_minuti FROM servizi ORDER BY nome_servizio ASC";
        $queryResult = mysqli_query($this->connection, $query);
        if ($queryResult) {
            return $queryResult->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // Funzione per ottenere la lista dei SERVIZI disponibili per la farmacia
    public function getServiziFarmacia($idFarm) {
        $query = "SELECT servizi.id, servizi.nome_servizio, servizi.descrizione, servizi.durata_media_minuti 
                FROM servizi 
                INNER JOIN farmacia_servizi ON servizi.id = farmacia_servizi.servizio_id 
                WHERE farmacia_servizi.farmacia_id = ? 
                ORDER BY nome_servizio ASC";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $idFarm);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
    }

    // 7. Funzione per ottenere la lista di tutte le CITTÀ uniche
    public function getListaCitta() {
        $query = "SELECT DISTINCT citta FROM farmacie ORDER BY citta ASC";
        $queryResult = mysqli_query($this->connection, $query);
        if ($queryResult) {
            return $queryResult->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // 8. Funzione per trovare le farmacie che offrono un dato servizio in una data città
    public function getFarmaciePerServizioECitta($idServizio, $citta) {
        $query = "SELECT f.id, f.nome, f.indirizzo 
                  FROM farmacie f 
                  INNER JOIN farmacia_servizi fs ON f.id = fs.farmacia_id 
                  WHERE fs.servizio_id = ? AND f.citta = ? 
                  ORDER BY f.nome ASC";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "is", $idServizio, $citta);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
    }

    // 9. Funzione per trovare farmacie "vicine" (in altre città) che offrono un servizio
    public function getFarmacieVicinePerServizio($idServizio, $cittaEsclusa, $limit = 5) {
        // Step 1: Ottiene le coordinate medie (lat, lon) per la città selezionata, usandole come centro per la ricerca
        $coordQuery = "SELECT AVG(latitudine) as lat, AVG(longitudine) as lon FROM farmacie WHERE citta = ?";
        $coordStmt = mysqli_prepare($this->connection, $coordQuery);
        mysqli_stmt_bind_param($coordStmt, "s", $cittaEsclusa);
        mysqli_stmt_execute($coordStmt);
        $coordResult = mysqli_stmt_get_result($coordStmt);
        $coords = mysqli_fetch_assoc($coordResult);

        // Se non è possibile trovare le coordinate per la città (es. nessuna farmacia presente), non si può calcolare la distanza.
        if (!$coords || is_null($coords['lat']) || is_null($coords['lon'])) {
            return [];
        }

        $lat = $coords['lat'];
        $lon = $coords['lon'];
        $distanzaMassima = 20; // Distanza massima in KM. Puoi modificare questo valore.

        // Step 2: Trova le farmacie vicine usando la formula di Haversine per calcolare la distanza in km.
        // 6371 è il raggio della Terra in km.
        $query = "SELECT f.id, f.nome, f.indirizzo, f.citta,
                    ( 6371 * acos( cos( radians(?) ) * cos( radians( f.latitudine ) ) * cos( radians( f.longitudine ) - radians(?) ) + sin( radians(?) ) * sin( radians( f.latitudine ) ) ) ) AS distanza
                  FROM farmacie f 
                  INNER JOIN farmacia_servizi fs ON f.id = fs.farmacia_id 
                  WHERE fs.servizio_id = ? AND f.citta != ? 
                  HAVING distanza < ?
                  ORDER BY distanza ASC 
                  LIMIT ?";
                  
        $stmt = mysqli_prepare($this->connection, $query);
        // I parametri sono: lat, lon, lat, idServizio, cittaEsclusa, distanzaMassima, limit
        mysqli_stmt_bind_param($stmt, "dddisii", $lat, $lon, $lat, $idServizio, $cittaEsclusa, $distanzaMassima, $limit);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
    }
}

?>