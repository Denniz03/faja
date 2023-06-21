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
	<script>
		$(document).ready(function() {
		  // Voeg een eventlistener toe aan alle cellen
		  $('#tab0 td:not(:first-child)').on('mouseover', highlightColumn);
		  $('#tab0 td').on('mouseout', resetColumn);

		  // Functie om de hele kolom  te kleuren
		  function highlightColumn(event) {
			var currentCell = $(this);
			var columnIndex = currentCell.index();
			var table = currentCell.closest('table');

			// Loop door alle rijen en kleur de cellen in de kolom 
			table.find('tr').each(function() {
			  var cell = $(this).find('td').eq(columnIndex);
			  cell.css('background-color', '#d9edf7bb');
			});
		  }

		  // Functie om de oorspronkelijke kleur van de kolom te herstellen
		  function resetColumn(event) {
			var currentCell = $(this);
			var columnIndex = currentCell.index();
			var table = currentCell.closest('table');

			// Loop door alle rijen en herstel de oorspronkelijke kleur van de cellen in de kolom
			table.find('tr').each(function() {
			  var cell = $(this).find('td').eq(columnIndex);
			  cell.css('background-color', '');
			});
		  }
	
	});
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
			echo '<article class="tab" id="tab' . $index . '">';
			echo '<h2>' . $maand . '</h2>';

			// Controleer of het de eerste tabblad is (overzicht)
			if ($index === 0) {

				echo '<div id="chartContainer"></div><br>';

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

				// Maak een array van de typen en hun gegevens voor de grafiek
				$grafiekGegevens = array();

				foreach ($typenPerMaand as $type => $maandenData) {
					$dataPoints = array();

					foreach ($maandenData as $maand => $aantal) {
						$dataPoints[] = array(
							"label" => $maand,
							"y" => $aantal
						);
					}

					$grafiekGegevens[] = array(
						"type" => "area",
						"name" => $type,
						"showInLegend" => true,
						"dataPoints" => $dataPoints
					);
				}
				
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

				// Variabele om het totaal van typen per maand bij te houden
				$totaalPerMaand = array();

				// Loop door de maanden en bereken het totaal per maand
				foreach ($maandenKort as $index => $maandnaam) {
					if ($maandnaam != 'Overzicht') {
						$totaal = 0;

						// Loop door de typen per maand en tel het totaal op
						foreach ($typenPerMaand as $type => $maandenData) {
							$aantal = isset($maandenData[$maandnaam]) ? $maandenData[$maandnaam] : 0;
							$totaal += $aantal;
						}

						// Voeg het totaal toe aan de totalenrij
						echo '<td data-index="' . $index . '">' . $totaal . '</td>';

						// Voeg het totaal toe aan de $totalen array
						$totalen[$maandnaam] = $totaal;
					}
				}

				echo '<td></td></tr>';
				echo '</table>';
				
			} else {
				
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
					echo '<tr onclick="selectRow(this)"' . ($row['oplossing'] !== '' ? ' class="oplossing-gevuld"' : '') . '>';
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
	
	<footer>
		<article>
			<p>&copy; Copyright <?php echo "2003-" . date("Y") ?> <a href="https://denniz03.nl" target="_blank">Denniz03</a>. Alle rechten voorbehouden.</p>
		</article>
	</footer>

    <script>
        // Functie om een specifiek tabblad te openen
        function openTab(event, tabId) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByTagName("button");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabId).style.display = "block";
            event.currentTarget.className += " active";
        }

        // Functie om de tabel te sorteren op basis van een kolom
        function sorteerTabel(header, kolom) {
            var tabel, rijen, i, x, y, moetWisselen, wisselVolgorde, inhoudCel1, inhoudCel2;
            tabel = header.parentNode.parentNode.parentNode; // Tabel element
            wisselVolgorde = header.getAttribute("data-volgorde") === "asc" ? "desc" : "asc"; // Nieuwe sorteervolgorde
            header.setAttribute("data-volgorde", wisselVolgorde); // Bijwerken van de sorteervolgorde attribuut

            // Reset de sorteerpictogrammen in andere headers
            rijen = tabel.getElementsByTagName("th");
            for (i = 0; i < rijen.length; i++) {
                rijen[i].classList.remove("asc", "desc");
            }

            header.classList.add(wisselVolgorde); // Toevoegen van sorteerpictogram aan de geselecteerde header

            rijen = tabel.getElementsByTagName("tr");
            moetWisselen = true;
            while (moetWisselen) {
                moetWisselen = false;
                for (i = 1; i < (rijen.length - 1); i++) {
                    x = rijen[i].getElementsByTagName("td")[kolom];
                    y = rijen[i + 1].getElementsByTagName("td")[kolom];

                    inhoudCel1 = x.innerHTML.toLowerCase();
                    inhoudCel2 = y.innerHTML.toLowerCase();

                    // Controleer of de kolom numeriek is en sorteer op numerieke waarde
                    if (!isNaN(inhoudCel1) && !isNaN(inhoudCel2)) {
                        inhoudCel1 = parseFloat(inhoudCel1);
                        inhoudCel2 = parseFloat(inhoudCel2);
                    }

                    if (wisselVolgorde === "asc") {
                        if (inhoudCel1 > inhoudCel2) {
                            rijen[i].parentNode.insertBefore(rijen[i + 1], rijen[i]);
                            moetWisselen = true;
                            break;
                        }
                    } else if (wisselVolgorde === "desc") {
                        if (inhoudCel1 < inhoudCel2) {
                            rijen[i].parentNode.insertBefore(rijen[i + 1], rijen[i]);
                            moetWisselen = true;
                            break;
                        }
                    }
                }
            }
        }
		
		// Functie om de gegevens te updaten
		function updateData(cel, month, kolom) {
			var waarde = cel.innerText;
			var parentRow = cel.parentNode;
			if (parentRow && parentRow.getElementsByTagName('td') && parentRow.getElementsByTagName('td').length > 1) {
				var id = parentRow.getElementsByTagName('td')[1].innerText;
				console.log('id:', id);
				console.log('kolom:', kolom);
			} else {
				console.log('Fout: Ongeldige cel of cel.parentNode');
			}

			// Stuur de bijgewerkte gegevens naar de server via een AJAX-verzoek
			var xhr = new XMLHttpRequest();
			xhr.onreadystatechange = function() {
				if (xhr.readyState === 4 && xhr.status === 200) {
					// Gegevens succesvol bijgewerkt
					console.log('Gegevens bijgewerkt: ' + xhr.responseText);
				}
			};
			xhr.open('POST', 'update_data.php', true);
			xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhr.send('&id=' + id + '&kolom=' + kolom + '&waarde=' + waarde);
		}
		
		// Functie om een tabelrij te markeren bij selectie
		function selectRow(row) {
			var table = row.parentNode;
			var rows = table.getElementsByTagName("tr");
			
			// Deselecteer alle rijen
			for (var i = 0; i < rows.length; i++) {
				rows[i].classList.remove("selected");
			}
			
			// Selecteer de huidige rij
			row.classList.add("selected");
		}
		
		window.addEventListener('DOMContentLoaded', function() {
			// Haal alle tabknoppen op
			var tabknoppen = $('nav button');

			// Loop door de tabknoppen
			for (var i = 0; i < tabknoppen.length; i++) {
				var tabknop = tabknoppen[i];
				var maandnaam = tabknop.innerHTML.trim(); // Haal de maandnaam op

				// Zoek de bijbehorende totalen per maand op
				var totalenPerMaand = <?php echo json_encode($totaalPerMaand); ?>;

				// Voeg de totalen toe aan de tabknoppen
				if (totalenPerMaand.hasOwnProperty(maandnaam)) {
					var totalen = totalenPerMaand[maandnaam];
					var totaalHtml = '<span class="totalen">';

					// Loop door de totalen en voeg ze toe aan de HTML
					for (var type in totalen) {
						if (totalen.hasOwnProperty(type)) {
							totaalHtml += '<span class="type">' + type + ': ' + totalen[type] + '</span>';
						}
					}

					totaalHtml += '</span>';

					// Voeg de HTML toe aan de tabknop
					tabknop.innerHTML += totaalHtml;
				}
			}
		});
		
		window.onload = function() {
		// Grafiekgegevens ophalen uit de PHP-variabele
		var grafiekData = <?php echo $grafiekGegevens; ?>;

		// Grafiek instellen en renderen
		var grafiek = new CanvasJS.Chart("chartContainer", {
			animationEnabled: true,
			title: {
				text: "Grafiek van typen per maand"
			},
			axisX: {
				title: "Maand"
			},
			axisY: {
				title: "Aantal"
			},
			legend: {
				verticalAlign: "top"
			},
			data: grafiekData
		});

		grafiek.render();
	}
	
    </script>
</body>
</html>
