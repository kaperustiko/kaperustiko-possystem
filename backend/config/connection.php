    <?php

header("Access-Control-Allow-Origin: *"); // Allow all origins, or specify a domain
header("Access-Control-Allow-Methods: POST, OPTIONS, DELETE"); // Allow specific methods including DELETE
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers

$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "sql12759808";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// New connection details
$host = 'sql12.freesqldatabase.com';
$dbname2 = 'sql12759808'; // Renamed to avoid conflict
$user2 = 'sql12759808';
$password2 = 'B3NiNYECSl';
$port = 3306;

// Create second connection
$conn2 = new mysqli($host, $user2, $password2, $dbname2, $port);

// Check second connection
if ($conn2->connect_error) {
    die("Connection failed: " . $conn2->connect_error);
}

?> 
