<?php
function dbconnector($admin){
    $host     = 'localhost';       // host
    $password = '123456';        // Passwort (brauchen Sie nie dieses Passwort)
    $database = 'm151';   // database

    if(isset($admin)){
        $username = 'adminM151';
    }else{
        $username = 'userM151';
    }
// Verbindung herstellen
    $conn = new mysqli($host, $username, $password, $database);

// Verbindung prüfen
    if ($conn->connect_error) {
        die("Verbindung misslungen: " . $conn->connect_error);
    }
    return $conn;
}
