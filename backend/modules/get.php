<?php
include '../config/connection.php';

// Function to get bestsellers
function getBestsellers($conn)
{
    $sql = "SELECT JSON_UNQUOTE(JSON_EXTRACT(items_ordered, '$[*].order_name')) AS order_names FROM total_sales";
    $result = $conn->query($sql);
    $order_counts = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $order_names = json_decode($row['order_names']);
            foreach ($order_names as $name) {
                if (isset($order_counts[$name])) {
                    $order_counts[$name]++;
                } else {
                    $order_counts[$name] = 1;
                }
            }
        }
    }
    $most_ordered = array_keys($order_counts, max($order_counts));
    echo json_encode($most_ordered);
}

// Function to get menu items
function getMenu($conn)
{
    $sql = "SELECT * FROM `pos-menu`"; // Fetch all menu items
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $menuItems = [];
        while ($row = $result->fetch_assoc()) {
            $menuItems[] = $row;
        }
        echo json_encode($menuItems);
    } else {
        echo json_encode(["message" => "No menu items found."]);
    }
}

// Function to get items by code
function getItems($conn)
{
    $code = isset($_GET['code']) ? $_GET['code'] : ''; // Retrieve code from query parameters
    $sql = "SELECT * FROM `pos-menu` WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $items = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($items);
    } else {
        echo json_encode([]);
    }
    $stmt->close();
}

// Function to get least sellers
function getLeastsellers($conn)
{
    $sql = "SELECT JSON_UNQUOTE(JSON_EXTRACT(items_ordered, '$[*].order_name')) AS order_names FROM total_sales";
    $result = $conn->query($sql);
    $order_counts = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $order_names = json_decode($row['order_names']);
            foreach ($order_names as $name) {
                if (isset($order_counts[$name])) {
                    $order_counts[$name]++;
                } else {
                    $order_counts[$name] = 1;
                }
            }
        }
    }
    $least_ordered = array_keys($order_counts, min($order_counts));
    echo json_encode($least_ordered);
}

// Function to get menu for dashboard
function getMenuDashboard($conn)
{
    $selectedCategory = $_GET['label']; // Use GET method for label1
    $selectedCategory2 = $_GET['label2']; // Use GET method for label2
    $query = "SELECT * FROM `pos-menu` WHERE label = ? OR label2 = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $selectedCategory, $selectedCategory2);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $menuItems = [];
        while ($row = $result->fetch_assoc()) {
            $menuItems[] = $row;
        }
        echo json_encode($menuItems);
    } else {
        echo json_encode(["message" => "No menu items found."]);
    }
    $stmt->close();
}

// Function to get orders
function getOrders($conn)
{
    $sql = "SELECT * FROM orders";
    $result = $conn->query($sql);
    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    echo json_encode($orders);
}

// Function to get orders
function getQueOrders($conn)
{
    $sql = "SELECT * FROM que_orders";
    $result = $conn->query($sql);
    $orders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
    }
    echo json_encode($orders);
}

// Function to get product quantity
function getProductQty($conn)
{
    $code = isset($_GET['code']) ? $_GET['code'] : ''; // Retrieve code from query parameters
    $sql = "SELECT qty FROM `pos-menu` WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row); // Return the quantity as JSON
    } else {
        echo json_encode(["qty" => 0]); // Return 0 if no record found
    }
    $stmt->close();
}

// Function to get remit returns
function getRemitReturns($conn)
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['date'])) {
            $date = $_GET['date'];
            $stmt = $conn->prepare("SELECT * FROM remit_returns WHERE return_date = ?");
            $stmt->bind_param("s", $date);
        } else {
            $stmt = $conn->prepare("SELECT * FROM remit_returns");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $remits = [];
        while ($row = $result->fetch_assoc()) {
            $remits[] = $row;
        }
        echo json_encode($remits);
        exit; // Stop further execution
    }
}

// Function to get remit sales
function getRemitSales($conn)
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['date'])) {
            $date = $_GET['date'];
            $stmt = $conn->prepare("SELECT * FROM remit_sales WHERE remit_date = ?");
            $stmt->bind_param("s", $date);
        } else {
            $stmt = $conn->prepare("SELECT * FROM remit_sales");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $remits = [];
        while ($row = $result->fetch_assoc()) {
            $remits[] = $row;
        }
        echo json_encode($remits);
        exit; // Stop further execution
    }
}

// Function to get return orders
function getReturnOrders($conn)
{
    $return_date = isset($_GET['return_date']) ? $_GET['return_date'] : '';
    if (!empty($return_date)) {
        $sql = "SELECT * FROM `return-orders` WHERE return_date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $return_date);
    } else {
        $sql = "SELECT * FROM `total_returns`"; // Query to get all data
        $stmt = $conn->prepare($sql);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
}

// Function to get sales information
function getSalesInformation($conn)
{
    $date = isset($_GET['date']) ? $_GET['date'] : null;
    $query = "SELECT * FROM total_sales" . ($date ? " WHERE date = '$date'" : "");
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $salesData = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($salesData);
    } else {
        echo json_encode(["message" => "No sales data found"]);
    }
}

// Function to get today's sales
function getTodaySales($conn)
{
    $date = $_GET['date'] ?? null; // Check for date_time in query params
    if ($date === null) {
        $date = date('Y-m-d'); // Use current date if no date is provided
    }
    if ($date) {
        $sql = "SELECT SUM(total_amount) AS total_amount FROM total_sales WHERE date = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $salesData = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $salesData[] = $row;
            }
        }
        echo json_encode($salesData);
    } else {
        echo json_encode(["error" => "Date parameter is required."]);
    }
}

// Function to get total orders
function getTotalOrders($conn)
{
    $query = "SELECT MAX(total_order) as total_order FROM total_sales";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $row['total_order'] += 1;
    echo json_encode($row);
}
function getTotalQueOrders($conn)
{
    $query = "SELECT MAX(total_order) as total_order FROM que_orders";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $row['total_order'] += 1;
    echo json_encode($row);
}

// Function to get total sales
function getTotalSales($conn)
{
    $sql = "SELECT SUM(total_sales) AS total_sales_sum FROM remit_sales";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $totalSales = $row['total_sales_sum'];
        echo json_encode(['total_sales' => $totalSales]);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }
}

// Function to get user information
function getUser($conn)
{
    $staff_token = $_GET['staff_token']; // Assuming you're getting the token from a GET request
    $query = "SELECT firstName FROM `user-staff` WHERE staff_token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $staff_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $firstName = $result->fetch_assoc()['firstName'];
    echo json_encode($firstName);
}

// Function to update product quantity
function updateProductQty($conn)
{
    if (!isset($_GET['code'])) {
        echo json_encode(["status" => "error", "message" => "Code parameter is missing."]);
        return;
    }

    $code = $_GET['code'];

    // Get current quantity
    $current_qty_sql = "SELECT qty FROM `pos-menu` WHERE code = ?";
    $current_stmt = $conn->prepare($current_qty_sql);
    $current_stmt->bind_param("s", $code);
    $current_stmt->execute();
    $result = $current_stmt->get_result();
    $row = $result->fetch_assoc();
    $current_qty = $row['qty'];
    $current_stmt->close();

    $new_qty = $current_qty - 1;

    $sql = "UPDATE `pos-menu` SET qty = ? WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $new_qty, $code);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Quantity updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "No rows updated."]);
    }

    $stmt->close();
}

// Function to get total shortage cost
function getTotalShortage($conn)
{
    $sql = "SELECT SUM(remit_shortage) AS total_shortage FROM remit_sales";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $totalShortage = $row['total_shortage'];
        echo json_encode(['total_shortage' => $totalShortage]);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }
}

// Function to get today's total shortage cost
function getTodayShortage($conn)
{
    $today = date('Y-m-d'); // Get today's date
    $sql = "SELECT SUM(remit_shortage) AS total_shortage FROM remit_sales WHERE remit_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        $totalShortage = $row['total_shortage'] ?? 0; // Default to 0 if no records found
        echo json_encode(['total_shortage' => $totalShortage]);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }
}

// Function to get today's total return cost
function getTodayReturnCost($conn)
{
    $today = date('Y-m-d'); // Get today's date
    $sql = "SELECT SUM(total_sales) AS total_return FROM `remit_returns` WHERE return_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $row = $result->fetch_assoc();
        $totalReturn = $row['total_return'] ?? 0; // Default to 0 if no records found
        echo json_encode(['total_return' => $totalReturn]);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }
}

// Function to get total return cost
function getTotalReturnCost($conn)
{
    $sql = "SELECT SUM(total_sales) AS total_return_cost FROM remit_returns";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $totalReturnCost = $row['total_return_cost'] ?? 0; // Default to 0 if no records found
        echo json_encode(['total_return_cost' => $totalReturnCost]);
    } else {
        echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    }
}

// Function to get monthly sales
function getMonthlySales($conn) {
    // Use STR_TO_DATE to properly parse the date format MM/DD/YYYY
    $sql = "SELECT 
                MONTH(STR_TO_DATE(date, '%m/%d/%Y')) AS month, 
                SUM(total_amount) AS total_amount 
            FROM total_sales 
            GROUP BY MONTH(STR_TO_DATE(date, '%m/%d/%Y'))
            ORDER BY month";
    
    $result = $conn->query($sql);
    
    // Initialize array with all months set to 0
    $monthlySales = array_fill(1, 12, [
        'month' => 0,
        'total_amount' => 0.0
    ]);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $month = (int)$row['month'];
            $monthlySales[$month] = [
                'month' => $month,
                'total_amount' => (float)$row['total_amount']
            ];
        }
    }
    
    // Convert to indexed array and preserve order
    $monthlySales = array_values($monthlySales);
    
    echo json_encode($monthlySales);
}

// Route handling based on request type
$requestMethod = $_SERVER['REQUEST_METHOD'];
switch ($requestMethod) {
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'getBestsellers':
                    getBestsellers($conn);
                    break;
                case 'getMenu':
                    getMenu($conn);
                    break;
                case 'getItems':
                    getItems($conn);
                    break;
                case 'getLeastsellers':
                    getLeastsellers($conn);
                    break;
                case 'getMenuDashboard':
                    getMenuDashboard($conn);
                    break;
                case 'getOrders':
                    getOrders($conn);
                    break;
                case 'getProductQty':
                    getProductQty($conn);
                    break;
                case 'getRemitReturns':
                    getRemitReturns($conn);
                    break;
                case 'getRemitSales':
                    getRemitSales($conn);
                    break;
                case 'getReturnOrders':
                    getReturnOrders($conn);
                    break;
                case 'getSalesInformation':
                    getSalesInformation($conn);
                    break;
                case 'getTodaySales':
                    getTodaySales($conn);
                    break;
                case 'getTotalOrders':
                    getTotalOrders($conn);
                    break;
                case 'getTotalSales':
                    getTotalSales($conn);
                    break;
                case 'getUser':
                    getUser($conn);
                    break;
                case 'updateProductQty':
                    updateProductQty($conn);
                    break;
                case 'getTotalShortage':
                    getTotalShortage($conn);
                    break;
                case 'getTodayShortage':
                    getTodayShortage($conn);
                    break;
                case 'getTodayReturnCost':
                    getTodayReturnCost($conn);
                    break;
                case 'getTotalReturnCost':
                    getTotalReturnCost($conn);
                    break;
                case 'getMonthlySales':
                    getMonthlySales($conn);
                    break;
                case 'getQueOrders':
                    getQueOrders($conn);
                    break;
                default:
                    echo json_encode(["status" => "error", "message" => "Invalid action"]);
                    break;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "No action specified"]);
        }
        break;
    case 'POST':
        // Handle POST requests if needed
        break;
    default:
        echo json_encode(["status" => "error", "message" => "Method not allowed"]);
        break;
}

// Close the connection
$conn->close();
