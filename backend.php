<?php
session_start();
if(isset($_POST['usernameLogin']) && isset($_POST['passwordLogin'])){
    $host     = 'localhost';       // host
    $password = '123456';        // Passwort (brauchen Sie nie dieses Passwort)
    $database = 'm151';   // database
    $username = 'userM151';

// Verbindung herstellen
    $mysqli = new mysqli($host, $username, $password, $database);

// Verbindung prüfen
    if ($mysqli->connect_error) {
        die("Verbindung misslungen: " . $mysqli->connect_error);
    }
    echo "test0";
    $username = htmlspecialchars(trim($_POST['usernameLogin']));
    $password = htmlspecialchars($_POST['passwordLogin']);
    $result = $mysqli->query("SELECT * from users where username = '$username'");
    if ($result->num_rows == 1) {
        echo "test1";
        while($row = $result->fetch_assoc()) {
            echo "test2";
            if(password_verify($password, $row['hash']) && !empty($row['username'])){
                echo "test3";
                $_SESSION['ID'] = $row['ID'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['firstName'] = $row['firstName'];
                $_SESSION['lastName'] = $row['lastName'];
                $_SESSION['admin'] = $row['status'];
                session_regenerate_id();
                print_r($_SESSION);
            }else{
                echo "Benutzername oder Passwort sind falsch";
                sleep(10);
            }
            echo "<meta http-equiv='refresh' content='0;url=index.php'>";
        }
    }

}
if(isset($_GET['logout'])){
    session_destroy();
    echo "<meta http-equiv='refresh' content='0;url=index.php'>";
}
function dbconnector($admin){
    $host     = 'localhost';       // host
    $password = '123456';        // Passwort (brauchen Sie nie dieses Passwort)
    $database = 'm151';   // database

    if($admin == 1){
        $username = 'adminM151';
    }else{
        $username = 'userM151';
    }
// Verbindung herstellen
    $conn = mysqli_connect($host, $username, $password, $database);

// Verbindung prüfen
    if ($conn->connect_error) {
        die("Verbindung misslungen: " . $conn->connect_error);
    }
    return $conn;
}