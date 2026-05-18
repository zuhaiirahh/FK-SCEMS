<?php
$hostname = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "fkscems";

$conn = mysqli_connect($hostname, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
