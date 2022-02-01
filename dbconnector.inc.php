<?php
$host     = 'localhost';       // host
$username = 'readwrite';            // username
$password = '123456';        // Passwort (brauchen Sie nie dieses Passwort)
$database = 'm151';   // database

// Verbindung herstellen
$conn = mysqli_connect($host, $username, $password, $database);

// Verbindung prüfen
if (!$conn) {
    die("Verbindung misslungen: " . mysqli_connect_error());
}
echo "Verbindung erfolgreich";
