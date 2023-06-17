<?php
    require 'script.php';

    // Controleer of de vereiste gegevens zijn verzonden
    if (isset($_POST['id'], $_POST['month'], $_POST['kolom'], $_POST['waarde'])) {
        // Ontvang de verzonden gegevens
        $id = $_POST['id'];
        $month = $_POST['month'];
        $kolom = $_POST['kolom'];
        $waarde = $_POST['waarde'];

        // Werk de sessiegegevens bij
        $month = $maanden[$month];

        // Doorloop de gegevens van de maand en zoek naar het specifieke id
        foreach ($_SESSION['maandtabellen'][$month] as &$gegevens) {
            if ($gegevens['id'] === $id) {
                // Update het veld met de nieuwe waarde
                $gegevens[$kolom] = $waarde;
                break;
            }
        }

        // Stuur een succesvolle respons terug naar de client
        $response = array(
            'status' => 'success',
            'message' => 'Oplossing succesvol bijgewerkt.',
            'received_data' => array(
                'id' => $id,
                'maand' => $month,
                'kolom' => $kolom,
                'waarde' => $waarde
            )
        );
    } else {
        // Stuur een foutmelding terug naar de client als de vereiste gegevens ontbreken
        $response = array(
            'status' => 'error',
            'message' => 'Ontbrekende gegevens bij het bijwerken van de gegevens.',
            'received_data' => $_POST
        );
    }

    // Zet de respons om naar JSON en stuur het terug naar de client
    header('Content-Type: application/json');
    echo json_encode($response);
?>
