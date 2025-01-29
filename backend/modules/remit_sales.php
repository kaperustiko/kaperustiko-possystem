<?php
require_once '../config/connection.php';
// Get the JSON data from the request
$data = json_decode(file_get_contents("php://input"), true);

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO remit_sales (cashier_name, total_sales, remit_date, remit_time, remit_shortage, remit_validation) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $data['cashier_name'], $data['total_sales'], $data['remit_date'], $data['remit_time'], $data['remit_shortage'], $data['remit_validation']);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Record added successfully."]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
}

// Close connections
$stmt->close();
$conn->close();