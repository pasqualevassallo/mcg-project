<!DOCTYPE html>
<html>
<head>
    <title>Visualizza Ultime Partite</title>
    <!-- Importa la libreria Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fafafa;
        }
        th, td {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        canvas {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // Configurazione della connessione al database
        $hostname = "localhost";
        $username = "root";
        $password = "";
        $database = "MCG - Database";

        // Creazione della connessione al database
        $conn = new mysqli($hostname, $username, $password, $database);

        // Controlla la connessione
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error); // Termina l'esecuzione se la connessione fallisce
        }

        // Query SQL per selezionare i dati dalla tabella userinfodb con punteggi diversi da -1
        $query_sql = "SELECT * FROM userinfodb WHERE Punteggio NOT LIKE -1 ORDER BY id DESC";
        $risultato = $conn->query($query_sql);

        if ($risultato == FALSE) {
            die("Errore nell'esecuzione della query : " . $query_sql); // Termina l'esecuzione se la query fallisce
        }

        echo "<h3>Elenco delle ultime partite effettuate:</h3>";

        // Inizia la tabella HTML per visualizzare i risultati
        echo "<table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Difficoltà</th>
            <th>Punteggio</th>
            <th>Data e ora</th>
        </tr>";

        // Array per contare le occorrenze di ogni livello di difficoltà
        $difficolta_counts = ["FACILE" => 0, "DIFFICILE" => 0];

        // Ciclo per ogni riga del risultato della query
        while ($riga = $risultato->fetch_assoc()) {
            $id = $riga["ID"];
            $username = $riga["Username"];
            $difficolta = $riga["Difficoltà"];
            $punteggio = $riga["Punteggio"];
            $data_ora = $riga["Data e ora"];

            // Aggiungi una riga alla tabella HTML
            echo "<tr>
                <td>$id</td>
                <td>$username</td>
                <td>$difficolta</td>
                <td>$punteggio</td>
                <td>$data_ora</td>
            </tr>";

            // Conta le occorrenze di ogni difficoltà
            if (isset($difficolta_counts[$difficolta])) {
                $difficolta_counts[$difficolta]++; // Incrementa il conteggio se la difficoltà esiste già
            }
        }
        echo "</table>"; // Chiudi la tabella HTML
        $conn->close(); // Chiudi la connessione al database
        ?>

        <!-- Intestazione per il grafico -->
        <h3>Distribuzione dei Livelli di Difficoltà</h3>
        <!-- Elemento canvas dove verrà disegnato il grafico -->
        <canvas id="barChart"></canvas>
    </div>

    <script>
        // Ottieni il contesto del canvas (l'area di disegno)
        var ctx = document.getElementById('barChart').getContext('2d');
        
        // Crea un nuovo grafico di tipo bar (bar chart)
        var barChart = new Chart(ctx, {
            type: 'bar', // Specifica il tipo di grafico
            data: {
                labels: ['FACILE', 'DIFFICILE'], // Etichette per ogni barra del grafico
                datasets: [{
                    label: 'Numero di Partite', // Etichetta del dataset
                    data: [<?php echo $difficolta_counts['FACILE']; ?>, <?php echo $difficolta_counts['DIFFICILE']; ?>], // Dati del dataset
                    backgroundColor: [ // Colori di sfondo per ogni barra
                        'rgba(54, 162, 235, 0.2)', // Colore per 'FACILE'
                        'rgba(255, 99, 132, 0.2)'  // Colore per 'DIFFICILE'
                    ],
                    borderColor: [ // Colori dei bordi per ogni barra
                        'rgba(54, 162, 235, 1)', // Bordo per 'FACILE'
                        'rgba(255, 99, 132, 1)'  // Bordo per 'DIFFICILE'
                    ],
                    borderWidth: 1 // Larghezza del bordo delle barre
                }]
            },
            options: {
                indexAxis: 'y', // Orienta il grafico orizzontalmente
                scales: {
                    x: {
                        beginAtZero: true // Imposta l'asse X per iniziare da zero
                    }
                },
                responsive: true, // Il grafico è adattabile
                plugins: {
                    legend: {
                        position: 'top', // Posiziona la legenda in alto
                    },
                    title: {
                        display: true, // Mostra il titolo del grafico
                        text: 'Distribuzione dei Livelli di Difficoltà' // Testo del titolo del grafico
                    }
                }
            },
        });
    </script>
</body>
</html>