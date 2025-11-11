<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');

// Enable CORS for frontend requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get the requested endpoint
$request_uri = $_SERVER['REQUEST_URI'];
$endpoint = str_replace('/pizza-status-secure/api.php', '', $request_uri);
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string from endpoint
$endpoint = strtok($endpoint, '?');

try {
    $pdo = getDBConnection();
    
    switch ($endpoint) {
        case '/orders':
        case '/orders/':
            if ($method === 'GET') {
                getOrders($pdo);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case '/order-status':
        case '/order-status/':
            if ($method === 'GET') {
                getOrderStatus($pdo);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case '/status-types':
        case '/status-types/':
            if ($method === 'GET') {
                getStatusTypes($pdo);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case '/update-status':
        case '/update-status/':
            if ($method === 'POST') {
                updateOrderStatus($pdo);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case '/create-order':
        case '/create-order/':
            if ($method === 'POST') {
                createOrder($pdo);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

// API Functions

function getOrders($pdo) {
    $username = $_GET['username'] ?? '';
    
    if (empty($username)) {
        http_response_code(400);
        echo json_encode(['error' => 'Username parameter required']);
        return;
    }
    
    $sql = "SELECT o.*, 
                   (SELECT status_name FROM order_status_history osh 
                    JOIN order_status os ON osh.status_id = os.status_id 
                    WHERE osh.order_id = o.order_id 
                    ORDER BY osh.status_time DESC LIMIT 1) as current_status,
                   (SELECT status_time FROM order_status_history 
                    WHERE order_id = o.order_id 
                    ORDER BY status_time DESC LIMIT 1) as last_update
            FROM orders o 
            WHERE o.customer_name = ? 
            ORDER BY o.order_time DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $orders = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $orders,
        'count' => count($orders)
    ]);
}

function getOrderStatus($pdo) {
    $order_id = $_GET['order_id'] ?? '';
    
    if (empty($order_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Order ID parameter required']);
        return;
    }
    
    // Get order details
    $sql_order = "SELECT * FROM orders WHERE order_id = ?";
    $stmt_order = $pdo->prepare($sql_order);
    $stmt_order->execute([$order_id]);
    $order = $stmt_order->fetch();
    
    if (!$order) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        return;
    }
    
    // Get status history
    $sql_status = "SELECT os.status_name, osh.status_time 
                   FROM order_status_history osh 
                   JOIN order_status os ON osh.status_id = os.status_id 
                   WHERE osh.order_id = ? 
                   ORDER BY osh.status_time ASC";
    $stmt_status = $pdo->prepare($sql_status);
    $stmt_status->execute([$order_id]);
    $status_history = $stmt_status->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'order' => $order,
            'status_history' => $status_history
        ]
    ]);
}

function getStatusTypes($pdo) {
    $sql = "SELECT * FROM order_status ORDER BY status_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $status_types = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $status_types
    ]);
}

function updateOrderStatus($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $order_id = $input['order_id'] ?? '';
    $status_id = $input['status_id'] ?? '';
    
    if (empty($order_id) || empty($status_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Order ID and Status ID are required']);
        return;
    }
    
    // Verify order exists
    $sql_check = "SELECT order_id FROM orders WHERE order_id = ?";
    $stmt_check = $pdo->prepare($sql_check);
    $stmt_check->execute([$order_id]);
    
    if (!$stmt_check->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Order not found']);
        return;
    }
    
    // Insert new status
    $sql = "INSERT INTO order_status_history (order_id, status_id, status_time) VALUES (?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_id, $status_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully'
    ]);
}

function createOrder($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $customer_name = $input['customer_name'] ?? '';
    $pizza_type = $input['pizza_type'] ?? '';
    $quantity = $input['quantity'] ?? 1;
    $special_instructions = $input['special_instructions'] ?? '';
    
    if (empty($customer_name) || empty($pizza_type)) {
        http_response_code(400);
        echo json_encode(['error' => 'Customer name and pizza type are required']);
        return;
    }
    
    // Insert new order
    $sql = "INSERT INTO orders (customer_name, pizza_type, quantity, order_time, special_instructions) 
            VALUES (?, ?, ?, NOW(), ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_name, $pizza_type, $quantity, $special_instructions]);
    
    $order_id = $pdo->lastInsertId();
    
    // Set initial status to "Preparing" (status_id = 1)
    $sql_status = "INSERT INTO order_status_history (order_id, status_id, status_time) VALUES (?, 1, NOW())";
    $stmt_status = $pdo->prepare($sql_status);
    $stmt_status->execute([$order_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $order_id
    ]);
}
?>