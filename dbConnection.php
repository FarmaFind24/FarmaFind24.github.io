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
	
}

?>