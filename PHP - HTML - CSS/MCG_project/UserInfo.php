<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database = "MCG - Database";

// Connessione al database
$conn = new mysqli($hostname, $username, $password, $database);

// Controlla la connessione
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} echo "Database connection succeeded ... ";

// Controlla se i dati sono stati inviati via POST
if (isset($_POST["Username"]) && isset($_POST["Difficoltà"]) && isset($_POST["Punteggio"])) {
    $u = $_POST["Username"];
    $d = $_POST["Difficoltà"];
    $p = $_POST["Punteggio"];

    // Prepara e bind
    $stmt = $conn->prepare("INSERT INTO userinfodb (Username, Difficoltà, Punteggio) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $u, $d, $p);

    // Esegui la query
    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Chiudi lo statement
    $stmt->close();
}

// Chiudi la connessione
$conn->close();
?>


