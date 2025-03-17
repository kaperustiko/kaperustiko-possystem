<?php
include '../config/connection.php';

try {
    // Check if 'action' key exists in POST request
    if (isset($_POST['action']) && !empty($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'login') {
            // Get data from POST request for login
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Prepare and bind for login
            $stmt = $conn->prepare("SELECT password, staff_token FROM `user-staff` WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($hashedPassword, $staffToken);
            $stmt->fetch();

            if ($stmt->num_rows > 0 && password_verify($password, $hashedPassword)) {
                echo json_encode(["status" => "success", "message" => "Login successful!", "staff_token" => $staffToken]);
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid email or password."]); // Invalid credentials
            }

            $stmt->close();
        } elseif ($action === 'register') {
            // Get data from POST request for registration
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $middleName = $_POST['middleName'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
            $contactNumber = $_POST['contactNumber'];

            // Prepare and bind for registration
            $stmt = $conn->prepare("INSERT INTO `user-staff` (firstName, lastName, middleName, email, password, contactNumber, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $avatar = 'default.jpg'; // Default avatar
            $stmt->bind_param("sssssss", $firstName, $lastName, $middleName, $email, $password, $contactNumber, $avatar);

            // Execute the statement
            if ($stmt->execute()) {
                // Get the last inserted ID
                $staff_no = $conn->insert_id; // Get the last inserted ID
                $staff_token = $staff_no; // Set staff_token to the same value as staff_no

                // Update the staff_token in the database
                $update_stmt = $conn->prepare("UPDATE `user-staff` SET staff_token = ? WHERE staff_no = ?");
                $update_stmt->bind_param("ii", $staff_token, $staff_no);
                $update_stmt->execute();
                $update_stmt->close();

                echo "Registration successful!";
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Action not specified. Please provide 'action' in your request."]); // More informative error message
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "An error occurred: " . $e->getMessage()]);
}

$conn->close(); 