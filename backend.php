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
    $username = htmlspecialchars(trim($_POST['usernameLogin']));
    $password = htmlspecialchars($_POST['passwordLogin']);
    $result = $mysqli->query("SELECT * from users where Benutzername = '$username'");
    if ($result->num_rows == 0) {
        while($row = $result->fetch_assoc()) {
            if(password_verify($password, $row['password']) && !empty($row['username'])){
                echo $_SESSION['ID'] = $row['ID'];
                echo $_SESSION['username'] = $row['Benutzername'];
                echo $_SESSION['firstName'] = $row['Vorname'];
                echo $_SESSION['lastName'] = $row['Nachname'];
                echo $_SESSION['admin'] = $row['Status'];
                session_regenerate_id();
                echo "<meta http-equiv='refresh' content='0;url=index.php'>";
            }else{
                echo "Benutzername oder Passwort sind falsch";
            }
        }
    }

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
if(isset($_GET['page']) && $_GET['page']=='reee'){
    reee();
}
if(isset($_GET['page']) && $_GET['page']=='raaa'){
    raaa();
}