<?php
include __DIR__ . '/fkscems.php';
$r = mysqli_query($conn, 'DESCRIBE user');
while ($row = mysqli_fetch_assoc($r)) print_r($row);
