<?php	 	

$servername = getenv('DB_SERVER') ?: '158.108.101.153';
$username = getenv('DB_USERNAME') ?: 'std6630202040';
$password = getenv('DB_PASSWORD') ?: 'nZ!4pQrt';
$dbname = getenv('DB_NAME') ?: 'it_std6630202040';

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {

    error_log("Connection failed: " . mysqli_connect_error()); 

    die("Sorry, we're experiencing some technical difficulties. Please try again later.");

}


mysqli_set_charset($conn, "utf8");


?>