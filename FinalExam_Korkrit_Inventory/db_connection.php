<?php	 	

$servername = getenv('DB_SERVER') ?: '**************';
$username = getenv('DB_USERNAME') ?: '***********';
$password = getenv('DB_PASSWORD') ?: '*********';
$dbname = getenv('DB_NAME') ?: '********';

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {

    error_log("Connection failed: " . mysqli_connect_error()); 

    die("Sorry, we're experiencing some technical difficulties. Please try again later.");

}


mysqli_set_charset($conn, "utf8");


?>