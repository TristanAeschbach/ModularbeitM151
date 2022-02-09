<?php
require "backend.php";
$mysqli = dbConnector(1);
$result = $mysqli->query("SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'todo';");
while ($row = $result->fetch_assoc()){
    print_r($row);
    echo "<br>";
}