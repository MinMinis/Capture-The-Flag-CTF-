<?php
$host = "localhost";
$user = "root";
$pwd = "";
$sql_db = "test";


$conn = @mysqli_connect($host, $user, $pwd, $sql_db);
if (!$conn) {
	die("Connection failed: " . mysqli_connect_error());
}
?>

