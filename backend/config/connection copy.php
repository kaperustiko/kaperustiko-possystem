<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

$host = 'sql12.freesqldatabase.com';
$dbname = 'sql12759808';
$user = 'sql12759808';
$password = 'B3NiNYECSl';
$port = 3306;

try {
    $connection = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $user, $password);
    // echo "Connection successful!"; // Commented out to prevent non-JSON output
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?> 
