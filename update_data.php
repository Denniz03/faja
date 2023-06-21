<?php
    require 'script.php';

    // Controleer of de vereiste gegevens zijn verzonden
    if (isset($_POST['id'], $_POST['kolom'], $_POST['waarde'])) {
        // Ontvang de verzonden gegevens
        $id = $_POST['id'];
        $kolom = $_POST['kolom'];
        $waarde = $_POST['waarde'];

        // Escapen van speciale karakters voordat ze in de query worden gebruikt
        $id = mysqli_real_escape_string($conn, $id);
        $kolom = mysqli_real_escape_string($conn, $kolom);
        $waarde = mysqli_real_escape_string($conn, $waarde);

        // Query om de gegevens bij te werken in de database
        $query = "UPDATE $tableName SET $kolom='$waarde' WHERE id='$id'";

        // Uitvoeren van de query
        if (mysqli_query($conn, $query)) {
            echo "Gegevens succesvol bijgewerkt in de database.<br>";
        } else {
            echo "Fout bij bijwerken van gegevens: " . mysqli_error($conn);
        }

        // Stuur een succesvolle respons terug naar de client
        $response = array(
            'status' => 'success',
            'message' => 'Oplossing succesvol bijgewerkt.',
            'received_data' => array(
                'id' => $id,
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
