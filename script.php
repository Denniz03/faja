<?php
	session_start();

	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	//error_reporting(E_ALL);

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

	// Controleer of de sessie-array voor maandtabellen al bestaat, anders initialiseren we deze
	if (!isset($_SESSION['maandtabellen'])) {
		$_SESSION['maandtabellen'] = array();
	}

	// Verwerk het ingediende formulier als er gegevens zijn ingevuld
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$data = $_POST['data'];

		// Verwerk de ingediende gegevens en voeg ze toe aan de juiste maandtab-array in de sessie
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

				// Bepaal de juiste maandindex op basis van de datum
				$monthIndex = getMonthIndex($datum);

				// Controleer of monthId niet leeg is
				if ($monthIndex != '' || $monthIndex != 0) {
					$month = $maandenKort[$monthIndex];
					$gegevens['datum'] = $day . ' ' . $month;
					
					// Controleer of de maandnaam al bestaat als een array in $_SESSION['maandtabellen']
					if (!isset($_SESSION['maandtabellen'][$month])) {
						$_SESSION['maandtabellen'][$month] = array();
					} 
				
					// Controleer of het ID al bestaat in de maandtab-array van de sessie
					$dubbelId = false;
					foreach ($_SESSION['maandtabellen'][$month] as $bestaandeGegevens) {
						if ($bestaandeGegevens['id'] === $id) {
							$dubbelId = true;
							break;
						}
					}

					// Voeg de gegevens alleen toe als het ID nog niet bestaat
					if (!$dubbelId) {
						$_SESSION['maandtabellen'][$month][] = $gegevens;
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
