<?php
header("Access-Control-Allow-Origin: *"); // Allow all origins, or specify a domain
header("Access-Control-Allow-Methods: GET, POST, OPTIONS"); // Allow specific methods
header("Access-Control-Allow-Headers: Content-Type"); // Allow specific headers
header('Content-Type: application/json');

// Database connection parameters
$host = 'localhost';
$db = 'kaperustiko-pos';
$user = 'root'; // Replace with your database username
$pass = ''; // Replace with your database password

// Create a connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Check if 'code' and 'order_quantity' are set in the GET request
if (!isset($_GET['code']) || !isset($_GET['order_quantity'])) {
    die(json_encode(["status" => "error", "message" => "Code or order_quantity parameter is missing."]));
}

$code = $_GET['code']; // Replace with the specific code you want to update
$order_quantity = intval($_GET['order_quantity']); // Get the order quantity from the request

// Get current quantity
$current_qty_sql = "SELECT qty FROM `pos-menu` WHERE code = ?";
$current_stmt = $conn->prepare($current_qty_sql);
$current_stmt->bind_param("s", $code);
$current_stmt->execute();
$current_stmt->bind_result($current_qty);
$current_stmt->fetch();
$current_stmt->close();

$new_qty = $current_qty - $order_quantity; // Decrement the quantity by order_quantity

$sql = "UPDATE `pos-menu` SET qty = ? WHERE code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $new_qty, $code); // "is" means integer and string
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["status" => "success", "message" => "Quantity updated successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "No rows updated."]);
}

$stmt->close();
// Close the connection
$conn->close();
