<?php
include '../config/connection.php';

// Log the request method for debugging
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

// Route handling based on request type
$requestMethod = $_SERVER['REQUEST_METHOD'];
switch ($requestMethod) {
    case 'DELETE':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'deleteProduct':
                    delete_product($conn);
                    break;

                case 'deleteAllOrders':
                    delete_all_orders($conn);
                    break;

                case 'voidOrder':
                    void_order($conn);
                    break;

                case 'deleteSalesInformation':
                    delete_sales_information($conn);
                    break;

                case 'deleteReturn':
                    $return_id = $_GET['return_id'] ?? null;
                    if ($return_id) {
                        $stmt = $conn->prepare("DELETE FROM `remit_returns` WHERE return_id = ?");
                        $stmt->bind_param("i", $return_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true]);
                        } else {
                            echo json_encode(["success" => false, "message" => $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(["success" => false, "message" => "No return ID provided"]);
                    }
                    break;

                case 'deleteRemit':
                    $remit_id = $_GET['remit_id'] ?? null;
                    if ($remit_id) {
                        $stmt = $conn->prepare("DELETE FROM remit_sales WHERE remit_id = ?");
                        $stmt->bind_param("i", $remit_id);
                        if ($stmt->execute()) {
                            echo json_encode(["success" => true]);
                        } else {
                            echo json_encode(["success" => false, "message" => $stmt->error]);
                        }
                        $stmt->close();
                    }
                    break;

                default:
                    echo json_encode(["status" => "error", "message" => "Invalid action"]);
                    break;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "No action specified"]);
        }
        break;

    case 'OPTIONS':
        // Handle preflight request
        http_response_code(200);
        exit;

    default:
        echo json_encode(["status" => "error", "message" => "Method not allowed"]);
        break;
}

// Close the connection
$conn->close();

function delete_product($conn) {
    // Get the code from the request
    $data = json_decode(file_get_contents("php://input"), true);
    $code = $data['code'] ?? null;

    if ($code) {
        // Prepare and bind
        $stmt = $conn->prepare("DELETE FROM `pos-menu` WHERE `code` = ?");
        $stmt->bind_param("s", $code);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["message" => "Product deleted successfully."]);
        } else {
            echo json_encode(["message" => "Error deleting product: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["message" => "No code provided."]);
    }
}

function delete_all_orders($conn) {
    // SQL to delete all records
    $sql = "DELETE FROM orders";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["message" => "All orders deleted successfully."]);
    } else {
        echo json_encode(["message" => "Error deleting orders: " . $conn->error]);
    }
}

function void_order($conn) {
    $orderName = $_GET['order_name'] ?? null;

    if ($orderName) {
        // Prepare and bind
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_name = ?");
        $stmt->bind_param("s", $orderName);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["message" => "Order voided successfully."]);
        } else {
            echo json_encode(["message" => "Error voiding order: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["message" => "No order name provided."]);
    }
}

function delete_sales_information($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    $receipt_number = $data['receipt_number'] ?? null;

    if ($receipt_number) {
        // Prepare and bind
        $stmt = $conn->prepare("DELETE FROM total_sales WHERE receipt_number = ?");
        $stmt->bind_param("s", $receipt_number);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Sale deleted successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error deleting sale: " . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "No receipt number provided."]);
    }
}
