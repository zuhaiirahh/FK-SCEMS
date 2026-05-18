<?php
$hostname = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "fkscems";
/*$port = 3307;*/

$conn = mysqli_connect($hostname, $username, $password, $dbname/*,$port*/);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
