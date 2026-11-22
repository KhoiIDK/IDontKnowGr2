<?php
$host = "localhost";
$user = "root";
$pwd = "";
$sql_db = "smarter_future_db";

$conn = mysqli_connect($host, $user, $pwd, $sql_db);

if (!$conn) {
    die("Failed to connect with database: " . mysqli_connect_error());
}
?>