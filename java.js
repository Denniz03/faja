$(document).ready(function() {

    // Voeg een eventlistener toe aan alle cellen
    $('#tab0 td').on('mouseout', resetColumn);
	$('#tab0 td:not(:first-child)').on('mouseover', highlightColumn);

    // Functie om de hele kolom te markeren
    function highlightColumn(event) {
        var currentCell = $(this);
        var columnIndex = currentCell.index();
        var table = currentCell.closest('table');

        // Loop door alle rijen en markeer de cellen in de kolom
        table.find('tr').each(function() {
            var cell = $(this).find('td').eq(columnIndex);
            cell.css('background-color', '#d9edf7bb');
            cell.css('border-left', '1px solid #31708f44');
            cell.css('border-right', '1px solid #31708f44');
        });
    }
	
	    // Voeg een eventlistener toe aan de tabelrijen om de grafiek bij te werken wanneer erop wordt geklikt
    $("#tab0 tr").on("click", function() {
        selectRow(this);
    });

    // Laad de initiële grafiekgegevens
    updateChartData();
	
});

// Functies

// Haal de gegevens voor de grafiek op van de tabel
function getDataFromTable() {
	var data = [];

	$("#tab0 tr").each(function() {
		var type = $(this).find("td:first-child").text();
		var monthData = $(this).find("td:not(:first-child):not(:last-child)");

		monthData.each(function(index) {
			var value = parseInt($(this).text());

			data.push({
				label: maandNamen[index],
				originalLabel: type, // Extra attribuut om de oorspronkelijke label op te slaan
				x: index + 1, // Voeg 1 toe aan de index om te beginnen vanaf 1
				y: value
			});
		});
	});

	return data;
}

// Functie om de grafiekgegevens bij te werken
function updateChartData() {
	chartData = getDataFromTable();
	initChart();
}

// Functie om de oorspronkelijke markeringskleur van de kolom te herstellen
function resetColumn(event) {
	var currentCell = $(this);
	var columnIndex = currentCell.index();
	var table = currentCell.closest('table');

	// Loop door alle rijen en herstel de oorspronkelijke kleur van de cellen in de kolom
	table.find('tr').each(function() {
		var cell = $(this).find('td').eq(columnIndex);
		cell.css('background-color', '');
		cell.css('border-left', '0px solid transparent');
		cell.css('border-right', '0px solid transparent');
	});
}

var chartData = [];

// Functie om de grafiek te initialiseren en bij te werken
function initChart() {
	// Sorteer de chartData-array op basis van de x-waarde
	chartData.sort(function(a, b) {
		return a.x - b.x;
	});
	
	var chart = new CanvasJS.Chart("chartContainer0", {
		title: {
			text: "Aantal Jobs per maand"
		},
		axisX: {
			title: "Maanden",
			interval: 1,
			labelAngle: -0
		},
		axisY: {
			title: "Aantal"
		},
		data: [{
			type: "area",
			lineColor: "#3C763D",
			color: "#DFF0D8",
			dataPoints: chartData,
		}]
	});

	chart.render();
}

var maandNamen = ['jan', 'feb', 'mrt', 'apr', 'mei', 'jun', 'jul', 'aug', 'sep', 'okt', 'nov', 'dec'];

// Functie om de grafiekgegevens bij te werken op basis van het geselecteerde type
function updateChart(selectedType) {
	var filteredData = chartData.filter(function(dataPoint) {
		return dataPoint.originalLabel === selectedType;
	});

	filteredData.forEach(function(dataPoint, index) {
		dataPoint.label = maandNamen[index]; // Vervang de oorspronkelijke label met de maandnaam
		dataPoint.color = "#3C763D";
	});

	var chart = new CanvasJS.Chart("chartContainer0", {
		title: {
			text: selectedType
		},
		axisX: {
			title: "Maanden",
			interval: 1,
			labelAngle: -0
		},
		axisY: {
			title: "Aantal"
		},
		data: [{
			type: "area",
			lineColor: "#3C763D",
			color: "#DFF0D8",
			dataPoints: filteredData
		}]
	});

	chart.render();
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

// Functie om een tabelrij te markeren bij selectie
var currentType = "";

function selectRow(row) {
	var table = row.parentNode;
	var rows = table.getElementsByTagName("tr");
	var selectedType = $(row).find("td:first-child").text();

	// Controleer of selectedType hetzelfde is als de huidige waarde
	if (selectedType !== currentType) {
		// Bijwerkende currentType naar de geselecteerde waarde
		currentType = selectedType;
		updateChart(selectedType);
	}

	// Deselecteer alle rijen
	for (var i = 0; i < rows.length; i++) {
		rows[i].classList.remove("selected");
	}

	// Selecteer de huidige rij
	row.classList.add("selected");
}
