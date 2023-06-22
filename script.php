<?php

	session_start();
	
	$host = 'localhost';
	$dbName = 'faja';
	$username = 'denniz03';
	$password = 'gxd7Hv';
	$tableName = 'jobs';
	
	//CONNECT
    $conn = new mysqli($host, $username, $password, $dbName);
	if (!$conn) {
		$_SESSION['error'] = mysqli_connect_error();
		$_SESSION['timeout'] = time() + 5;
	};
	$conn -> set_charset("utf8mb4");
	$query = "SET lc_time_names = 'nl_NL';"; // Taalinstelling aanpassen
	mysqli_query($conn, $query); // Uitvoeren van de taalinstelling
	
    // BASICS
    date_default_timezone_set('Europe/Amsterdam');
    $title = 'FAJA';
    $company = 'Denniz03';
    $version = 'Versie 1.0';
    $day = time() + (86400 * 30);
	
	// Troubleshooting
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	// Maak een array met de namen van de maanden
	$maanden = array(
		"Overzicht",
		"Januari",
		"Februari",
		"Maart",
		"April",
		"Mei",
		"Juni",
		"Juli",
		"Augustus",
		"September",
		"Oktober",
		"November",
		"December"
	);
	$maandenKort = array(
		"Overzicht",
		"Jan",
		"Feb",
		"Mrt",
		"Apr",
		"Mei",
		"Jun",
		"Jul",
		"Aug",
		"Sep",
		"Okt",
		"Nov",
		"Dec"
	);

	// Verwerk het ingediende formulier als er gegevens zijn ingevuld
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['data'])) {
		$data = $_POST['data'];

		// Verwerk de ingediende gegevens en voeg ze toe aan de database
		if (!empty($data)) {
			$rows = explode(PHP_EOL, $data); // Scheid de regels op basis van nieuwe regels (enter)
			foreach ($rows as $row) {
				$columns = explode("\t", $row); // Scheid de kolommen op basis van tabs
				$gegevens = array(
					'datum' => $columns[0] ?? '',
					'id' => $columns[1] ?? '',
					'type' => $columns[2] ?? '',
					'details' => $columns[3] ?? '',
					'ordernummer' => $columns[4] ?? '',
					'oplossing' => $columns[5] ?? ''
				);

				$id = $gegevens['id'];
				$datum = $gegevens['datum'];
				$day = explode('-', $datum)[0];
				$formattedDate = date('Y-m-d', strtotime($datum)); // Omzetten naar juist datumformaat (YYYY-MM-DD)

				// Controleer of het ID al bestaat in de database
				$query = "SELECT COUNT(*) AS aantal FROM $tableName WHERE id = '$id'";
				$result = mysqli_query($conn, $query);
				$row = mysqli_fetch_assoc($result);
				$aantalInDatabase = $row['aantal'];

				// Voeg de gegevens alleen toe als het ID nog niet bestaat in de database
				if ($aantalInDatabase == 0) {
					// Escapen van speciale karakters voordat ze in de query worden gebruikt
					$formattedDate  = mysqli_real_escape_string($conn, $formattedDate );
					$id = mysqli_real_escape_string($conn, $columns[1] ?? '');
					$type = mysqli_real_escape_string($conn, $columns[2] ?? '');
					$details = mysqli_real_escape_string($conn, $columns[3] ?? '');
					$ordernummer = mysqli_real_escape_string($conn, $columns[4] ?? '');
					$oplossing = mysqli_real_escape_string($conn, $columns[5] ?? '');

					// Query om de gegevens toe te voegen aan de database
					$query = "INSERT INTO $tableName (datum, id, type, details, ordernummer, oplossing)
							  VALUES ('$formattedDate ', '$id', '$type', '$details', '$ordernummer', '$oplossing')";

					// Uitvoeren van de query
					if (mysqli_query($conn, $query)) {
						echo "Gegevens succesvol toegevoegd aan de database.<br>";
					} else {
						echo "Fout bij toevoegen van gegevens: " . mysqli_error($conn);
					}
				}
			}
		}
	}

	// Functie om de maandindex op basis van de maandnaam te krijgen
	function getMonthIndex($dateString) {
		$dateParts = explode('-', $dateString);
		$day = $dateParts[0];
		$month = $dateParts[1];
		$month = strtolower($month);

		// Controleer of $month een geldige waarde bevat voordat je strtolower gebruikt
		if (!empty($month)) {
			// Controleer de taal van de maand en retourneer de juiste index
			if ($month === 'jan') {
				return 1;
			} elseif ($month === 'feb') {
				return 2;
			} elseif ($month === 'mrt' || $month === 'mar') {
				return 3;
			} elseif ($month === 'apr') {
				return 4;
			} elseif ($month === 'mei' || $month === 'may') {
				return 5;
			} elseif ($month === 'jun') {
				return 6;
			} elseif ($month === 'jul') {
				return 7;
			} elseif ($month === 'aug') {
				return 8;
			} elseif ($month === 'sep') {
				return 9;
			} elseif ($month === 'okt' || $month === 'oct') {
				return 10;
			} elseif ($month === 'nov') {
				return 11;
			} elseif ($month === 'dec') {
				return 12;
			} else {
				return 0; // Ongeldige maand, retourneer 0
			}
		}
	}
?>
