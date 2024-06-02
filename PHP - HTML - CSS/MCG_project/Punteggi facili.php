<!DOCTYPE html>
<html>
<head>
    <title>Visualizza Punteggi Facili</title>
    <!-- Importa la libreria Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Stile per il contenitore del grafico */
        .chart-container {
            display: flex; /* Disposizione degli elementi in linea */
            align-items: center; /* Allinea verticalmente gli elementi al centro */
        }
        /* Dimensioni del grafico */
        #doughnutChart {
            width: 1550px !important; /* Imposta larghezza del grafico */
            height: 500px !important; /* Imposta altezza del grafico */
        }
         /* Stile per la tabella */
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

    // Query SQL per selezionare i dati dalla tabella userinfodb con difficoltà 'FACILE'
    $query_sql = "SELECT * FROM userinfodb WHERE Difficoltà LIKE 'FACILE' ORDER BY Punteggio DESC";
    $risultato = $conn->query($query_sql);

    if ($risultato == FALSE) {
        die("Errore nell'esecuzione della query : " . $query_sql); // Termina l'esecuzione se la query fallisce
    }

    echo "<h3>Elenco dei massimi punteggi ottenuti nella modalità FACILE : </h3>";

    // Inizia la tabella HTML per visualizzare i risultati
    echo "<table border='1' cellspacing='0'>
    <tr>
        <th> ID </th>
        <th> Username </th>
        <th> Difficoltà </th>
        <th> Punteggio </th>
        <th> Data e ora </th>
    </tr>";

    // Array per memorizzare i punteggi e i conteggi
    $punteggi = [];
    $conteggi = [];

    // Ciclo per ogni riga del risultato della query
    while ($riga = $risultato->fetch_assoc()) {
        $id = $riga["ID"];
        $username = $riga["Username"];
        $difficolta = $riga["Difficoltà"];
        $punteggio = $riga["Punteggio"];
        $data_ora = $riga["Data e ora"];

        // Aggiungi una riga alla tabella HTML
        echo "<tr>
            <td> $id </td>
            <td> $username </td>
            <td> $difficolta </td>
            <td> $punteggio </td>
            <td> $data_ora </td>
        </tr>";

        // Popola i dati per il grafico
        if (isset($punteggi[$punteggio])) {
            $punteggi[$punteggio]++; // Incrementa il conteggio se il punteggio esiste già
        } else {
            $punteggi[$punteggio] = 1; // Inizializza il conteggio se il punteggio non esiste
        }
    }
    echo "</table>"; // Chiudi la tabella HTML
    $conn->close(); // Chiudi la connessione al database

    // Prepara i dati per il grafico
    $punteggi_labels = array_keys($punteggi); // Estrae i punteggi unici come etichette
    $conteggi_data = array_values($punteggi); // Estrae i conteggi corrispondenti
    ?>

    <!-- Contenitore per il grafico -->
    <div class="chart-container">
        <!-- Elemento canvas dove verrà disegnato il grafico -->
        <canvas id="doughnutChart"></canvas>
    </div>

    <script>
        // Ottieni il contesto del canvas
        var ctx = document.getElementById('doughnutChart').getContext('2d');
        
        // Crea un nuovo grafico di tipo doughnut (ciambella)
        var doughnutChart = new Chart(ctx, {
            type: 'doughnut', // Specifica il tipo di grafico
            data: {
                labels: <?php echo json_encode($punteggi_labels); ?>, // Etichette per i settori del grafico
                datasets: [{
                    label: 'Distribuzione Punteggi', // Etichetta del dataset
                    data: <?php echo json_encode($conteggi_data); ?>, // Dati del dataset
                    backgroundColor: [ // Colori di sfondo per ogni settore
                        'rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)', 'rgba(255, 99, 71, 0.2)', 'rgba(60, 179, 113, 0.2)',
                        'rgba(106, 90, 205, 0.2)', 'rgba(255, 140, 0, 0.2)', 'rgba(30, 144, 255, 0.2)', 'rgba(220, 20, 60, 0.2)',
                        'rgba(0, 191, 255, 0.2)', 'rgba(127, 255, 0, 0.2)', 'rgba(255, 20, 147, 0.2)', 'rgba(50, 205, 50, 0.2)',
                        'rgba(0, 255, 127, 0.2)', 'rgba(186, 85, 211, 0.2)', 'rgba(255, 215, 0, 0.2)', 'rgba(46, 139, 87, 0.2)'
                    ],
                    borderColor: [ // Colori dei bordi per ogni settore
                        'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)', 'rgba(255, 99, 71, 1)', 'rgba(60, 179, 113, 1)',
                        'rgba(106, 90, 205, 1)', 'rgba(255, 140, 0, 1)', 'rgba(30, 144, 255, 1)', 'rgba(220, 20, 60, 1)',
                        'rgba(0, 191, 255, 1)', 'rgba(127, 255, 0, 1)', 'rgba(255, 20, 147, 1)', 'rgba(50, 205, 50, 1)',
                        'rgba(0, 255, 127, 1)', 'rgba(186, 85, 211, 1)', 'rgba(255, 215, 0, 1)', 'rgba(46, 139, 87, 1)'
                    ],
                    borderWidth: 1 // Larghezza del bordo dei settori
                }]
            },
            options: {
                responsive: true, // Il grafico è adattabile
                maintainAspectRatio: false, // Mantiene il rapporto di aspetto
                plugins: {
                    legend: {
                        position: 'top', // Posizione della legenda
                    },
                    title: {
                        display: true, // Mostra il titolo del grafico
                        text: 'Distribuzione Punteggi nella modalità FACILE' // Testo del titolo del grafico
                    }
                }
            },
        });
    </script>
</body>
</html>
