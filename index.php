<?php
	include 'script.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tabel met maandgegevens</title>
    <link rel="stylesheet" type="text/css" href="style.css">
	<link href="../fontawesome/css/all.css" rel="stylesheet">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
	<script src="java.js"></script>
	<script>
		window.onload = function () {
			<?php
			foreach ($maanden as $index => $maand) {
				// Bepaal het aantal voorkomende typen per maand
				$typenPerMaand = array();
				
				// Loop door de gegevens in de database en tel de typen per maand
				$query = "SELECT type, DATE_FORMAT(datum, '%b') AS maand, COUNT(*) AS aantal FROM $tableName GROUP BY type, maand";
				$result = mysqli_query($conn, $query);

				while ($row = mysqli_fetch_assoc($result)) {
					$maandnaam = $row['maand']; // Haal de maandnaam uit de database
					$type = $row['type']; // Haal het type uit de database
					$aantal = $row['aantal']; // Haal het aantal uit de database

					// Tel het aantal voorkomende typen per maand
					if (!isset($typenPerMaand[$maandnaam])) {
						$typenPerMaand[$maandnaam] = array();
					}

					$typenPerMaand[$maandnaam][$type] = $aantal;
				}
			
				$dataPoints = array();
				
				// Loop door de typen en voeg de gegevens toe aan de dataPoints array
				foreach ($typenPerMaand[$maand] as $type => $aantal) {
					$dataPoints[] = array('label' => $type, 'y' => $aantal);
				}
			
				?>
				var dataPoints<?php echo $index ?> = <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK) ?>;
				var chart<?php echo $index ?> = new CanvasJS.Chart('chartContainer<?php echo $index ?>', {
					title: {
						text: 'Aantal voorkomende typen in <?php echo $maand ?>'
					},
					axisX: {
						title: 'Type'
					},
					axisY: {
						title: 'Aantal'
					},
					data: [{
						type: 'column',
						dataPoints: dataPoints<?php echo $index ?>
					}]
				});
				chart<?php echo $index ?>.render();
			<?php } ?>
		}
	</script>
</head>
<body>
	<header>
		<section>
			<img src="logo.png" alt="Logo" class="logo">
			<div class="title-subtitle">
				<h1 class="title">FaJA</h1>
				<h4 class="subtitle">FailedJobs Analyser</h4>
			</div>
		</section>
		<nav>
			<?php
			// Loop door de maanden en genereer de tabknoppen
			foreach ($maanden as $index => $maand) {
				if ($index === 0) {	
					echo '<button class=" active" onclick="openTab(event, \'tab' . $index . '\')">' . $maand . '</button>';
				} else {
					echo '<button onclick="openTab(event, \'tab' . $index . '\')">' . $maand . '</button>';
				}
			}
			?>
		</nav>
	</header>
	
    <main>
	<?php
		foreach ($maanden as $index => $maand) {
			// Bepaal het aantal voorkomende typen per maand
			$typenPerMaand = array();
			$totaalPerType = array();
			$totaalPerMaandPerType = array();
			
			// Loop door de gegevens in de database en tel de typen per maand
			$query = "SELECT type, DATE_FORMAT(datum, '%b') AS maand, COUNT(*) AS aantal FROM $tableName GROUP BY type, maand";
			$result = mysqli_query($conn, $query);

			 while ($row = mysqli_fetch_assoc($result)) {
				$maandnaam = $row['maand']; // Haal de maandnaam uit de database
				$type = $row['type']; // Haal het type uit de database
				$aantal = $row['aantal']; // Haal het aantal uit de database

				// Tel het aantal voorkomende typen per maand
				if (!isset($typenPerMaand[$type])) {
					$typenPerMaand[$type] = array();
				}
				
				// Tel het aantal voorkomende typen per maand
				if (!isset($typenPerMaand[$type])) {
					$typenPerMaand[$type] = array();
				}

				$typenPerMaand[$type][$maandnaam] = $aantal;

				// Tel het aantal voorkomende typen per maand per type
				if (!isset($totaalPerMaandPerType[$maandnaam])) {
					$totaalPerMaandPerType[$maandnaam] = 0;
				}

				$totaalPerMaandPerType[$maandnaam] += $aantal;
			}

			// Loop door de typen en bereken het totaal per type
			foreach ($typenPerMaand as $type => $maandenData) {
				$totaal = array_sum($maandenData);
				$totaalPerType[$type] = $totaal;
			}
				
			echo '<article class="tab" id="tab' . $index . '">';
			echo '<h2>' . $maand . '</h2>';
			echo '<div id="chartContainer'. $index .'" class="chartContainer"></div><br>';
			
// Overzicht

			// Controleer of het de eerste tabblad is (overzicht)
			if ($index === 0) {

				// Genereer de tabelkop voor het overzicht
				echo '
					<table>
						<tr>
							<th>Type</th>';

				// Loop door de maanden en voeg de maandnamen toe aan de tabelkop
				foreach ($maandenKort as $maandnaam) {
					if ($maandnaam != 'Overzicht') {
						echo '<th>' . $maandnaam . '</th>';
					}
				}
				// Voeg de totalenkolom toe
				echo '<th>Totaal</th>';
				echo '</tr>';
				
				// Loop door de typen en toon het aantal per maand en het totaal
				foreach ($typenPerMaand as $type => $maandenData) {
					echo '<tr onclick="selectRow(this)">';
					echo '<td>' . $type . '</td>';

					// Loop door de maanden en toon het aantal voorkomende typen per maand
					foreach ($maandenKort as $index => $maandnaam) {
						if ($maandnaam != 'Overzicht') {
							$aantal = 0;

							// Loop door de maanden in de database en vergelijk case-insensitive
							foreach ($maandenData as $maand => $aantalPerMaand) {
								if (strcasecmp($maand, $maandnaam) === 0) {
									$aantal = $aantalPerMaand;
									break;
								}
							}

							echo '<td data-index="' . $index . '">' . $aantal . '</td>';
						}
					}

					// Voeg de totaalwaarde toe aan de totalenkolom
					echo '<td class="total-column">' . $totaalPerType[$type] . '</td>';
					echo '</tr>';
				}

				// Voeg de totalenrij toe
				echo '<tr class="total-row">';
				echo '<td>Totaal</td>';

				// Loop door de maanden en toon het totaal per maand
				foreach ($maandenKort as $index => $maandnaam) {
					if ($maandnaam != 'Overzicht') {
						$maandnaam = strtolower($maandnaam);
						// Voeg het totaal uit de $totaalPerMaandPerType array toe aan de totalenrij
						$totaal = isset($totaalPerMaandPerType[$maandnaam]) ? $totaalPerMaandPerType[$maandnaam] : 0;
						echo '<td data-index="' . $index . '">' . $totaal . '</td>';

						// Voeg het totaal toe aan de $totalen array
						$totalen[$maandnaam] = $totaal;
					}
				}

				echo '<td></td></tr>';
				echo '</table>';
				
			} else {

// Tabbladen
				   
				// Genereer de tabelkop voor de maandtab
				echo '
					<table>
						<tr>
							<th onclick="sorteerTabel(this, 0)">Datum<i class="fad fa-sort"></i></th>
							<th onclick="sorteerTabel(this, 1)">ID<i class="fad fa-sort"></i></th>
							<th onclick="sorteerTabel(this, 2)">Type<i class="fad fa-sort"></i></th>
							<th onclick="sorteerTabel(this, 3)">Details<i class="fad fa-sort"></i></th>
							<th onclick="sorteerTabel(this, 4)">Ordernummer<i class="fad fa-sort"></i></th>
							<th onclick="sorteerTabel(this, 5)">Oplossing<i class="fad fa-sort"></i></th>
						</tr>';

				// Haal de gegevens uit de database voor de huidige maandtab en toon ze
				$maandnaam = strtolower($maandenKort[$index]);
				$query = "SELECT DATE_FORMAT(datum, '%e-%b-%y') AS maand, id, type, details, ordernummer, oplossing FROM $tableName WHERE DATE_FORMAT(datum, '%b') = '$maandnaam' ORDER BY id ASC";
				$result = mysqli_query($conn, $query);

				while ($row = mysqli_fetch_assoc($result)) {
					echo '<tr onclick="selectRow(this);"' . ($row['oplossing'] !== '' ? ' class="oplossing-gevuld"' : '') . '>';
					foreach ($row as $kolom => $waarde) {
						if ($kolom == 'maand') {
							$datum = explode('-', $waarde);
							$waarde = $datum[0] . ' ' . $datum[1];
						}
						if ($kolom == 'maand' || $kolom == 'id' || $kolom == 'type') {
							$contenteditable = 'contenteditable="false"';
						} else {
							$contenteditable = 'contenteditable="true"';
						}
						echo '<td  '. $contenteditable . ' oninput="updateData(this, ' . $index . ', \'' . $kolom . '\')">' . $waarde . '</td>';
					}
					echo '</tr>';
				}

				echo '</table>';
			}
			echo '</article>';
		}
		// Voeg een formulier toe om tekstgegevens aan de tabel toe te voegen
		echo '<article class="tab" id="tabAdd">';
		echo '<h2> Toevoegen </h2>';
		echo '
			<form method="post" action="">
				<textarea name="data" rows="4" cols="50" placeholder="Plak hier je gegevens (gescheiden door tabs)"></textarea><br>
				<input type="hidden" name="month" value="' . $maand . '">
				<button type="submit">Toevoegen</button>
			</form>';
		echo '</article>';
		mysqli_close($conn);
	?>
    </main>
	
	<script></script>
	
	<footer>
		<article>
			<p>&copy; Copyright <?php echo "2003-" . date("Y") ?> <a href="https://denniz03.nl" target="_blank">Denniz03</a>. Alle rechten voorbehouden.</p>
		</article>
	</footer>

</body>
</html>
