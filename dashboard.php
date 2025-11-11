<?php
require_once 'config.php';
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's orders and their current status
$username = $_SESSION['username'];
$orders_with_status = [];
$error = '';

try {
    $pdo = getDBConnection();
    
    // Get orders for this customer
    $sql_orders = "SELECT * FROM orders WHERE customer_name = ? ORDER BY order_time DESC";
    $stmt_orders = $pdo->prepare($sql_orders);
    $stmt_orders->execute([$username]);
    $orders = $stmt_orders->fetchAll();

    // For each order, get the current status and complete history
    foreach ($orders as $order) {
        // Get current status
        $sql_status = "SELECT os.status_name, osh.status_time 
                      FROM order_status_history osh 
                      JOIN order_status os ON osh.status_id = os.status_id 
                      WHERE osh.order_id = ? 
                      ORDER BY osh.status_time DESC 
                      LIMIT 1";
        $stmt_status = $pdo->prepare($sql_status);
        $stmt_status->execute([$order['order_id']]);
        $current_status = $stmt_status->fetch();
        
        // Get status history for timeline
        $sql_history = "SELECT os.status_name, osh.status_time 
                       FROM order_status_history osh 
                       JOIN order_status os ON osh.status_id = os.status_id 
                       WHERE osh.order_id = ? 
                       ORDER BY osh.status_time ASC";
        $stmt_history = $pdo->prepare($sql_history);
        $stmt_history->execute([$order['order_id']]);
        $status_history = $stmt_history->fetchAll();
        
        $orders_with_status[] = [
            'order' => $order,
            'current_status' => $current_status ? $current_status['status_name'] : 'Order Received',
            'last_update' => $current_status ? $current_status['status_time'] : $order['order_time'],
            'history' => $status_history
        ];
    }
    
} catch (PDOException $e) {
    $error = "Error loading orders: " . $e->getMessage();
}

// Pizza products data
$pizza_products = [
    [
        'name' => 'Pepperoni',
        'emoji' => '🍕',
        'price' => 299,
        'description' => 'Classic pepperoni with mozzarella cheese',
        'popular' => true
    ],
    [
        'name' => 'Hawaiian',
        'emoji' => '🍍',
        'price' => 329,
        'description' => 'Ham, pineapple, and cheese',
        'popular' => false
    ],
    [
        'name' => 'Vegetarian',
        'emoji' => '🥬',
        'price' => 349,
        'description' => 'Fresh vegetables and mushrooms',
        'popular' => false
    ],
    [
        'name' => 'Supreme',
        'emoji' => '👑',
        'price' => 399,
        'description' => 'All meats with vegetables',
        'popular' => true
    ],
    [
        'name' => 'Cheese Overload',
        'emoji' => '🧀',
        'price' => 279,
        'description' => 'Four cheese blend',
        'popular' => false
    ],
    [
        'name' => 'Meat Lovers',
        'emoji' => '🥓',
        'price' => 429,
        'description' => 'All meat toppings',
        'popular' => true
    ],
    [
        'name' => 'Margherita',
        'emoji' => '🇮🇹',
        'price' => 269,
        'description' => 'Fresh basil and tomatoes',
        'popular' => false
    ],
    [
        'name' => 'BBQ Chicken',
        'emoji' => '🍗',
        'price' => 379,
        'description' => 'BBQ sauce with chicken',
        'popular' => false
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pizza Tracker</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .welcome {
            margin-bottom: 20px;
        }
        
        .welcome h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .welcome p {
            color: #666;
            font-size: 1.2em;
        }
        
        /* Navigation Tabs */
        .nav-tabs {
            display: flex;
            background: white;
            border-radius: 15px;
            padding: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        .tab {
            padding: 15px 30px;
            background: none;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
            margin: 0 5px;
        }
        
        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .tab:hover:not(.active) {
            background: #f8f9fa;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .section-title {
            font-size: 1.8em;
            margin-bottom: 25px;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        /* Dashboard Tab Styles */
        .user-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 15px 25px;
            border-radius: 10px;
            border-left: 4px solid #e74c3c;
            text-align: center;
        }
        
        .orders-grid {
            display: grid;
            gap: 25px;
        }
        
        .order-card {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            border-left: 5px solid #e74c3c;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .order-id {
            font-size: 1.4em;
            font-weight: bold;
            color: #333;
        }
        
        .order-status {
            background: #e74c3c;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 0.9em;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-weight: bold;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #333;
            font-size: 1.1em;
        }
        
        /* Products Tab Styles */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .product-card {
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            background: white;
        }
        
        .product-card:hover {
            border-color: #e74c3c;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        
        .product-card.popular {
            border-color: #ffd700;
            background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
        }
        
        .popular-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background: #ffd700;
            color: #333;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .product-emoji {
            font-size: 4em;
            margin-bottom: 15px;
        }
        
        .product-name {
            font-size: 1.4em;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product-price {
            color: #e74c3c;
            font-weight: bold;
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        
        .product-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .order-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 1em;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
        }
        
        .order-btn:hover {
            transform: translateY(-2px);
        }
        
        .no-orders {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-orders h3 {
            font-size: 1.5em;
            margin-bottom: 15px;
        }
        
        .error {
            background: #ffeaea;
            color: #d63031;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logout-btn {
            display: inline-block;
            background: #e74c3c;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: background 0.3s;
            margin-top: 20px;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        @media (max-width: 768px) {
            .nav-tabs {
                flex-direction: column;
            }
            
            .tab {
                margin: 5px 0;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .user-info {
                flex-direction: column;
                align-items: center;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="header">
            <div class="welcome">
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>! 🍕</h1>
                <p>Track your orders and place new ones</p>
            </div>
            
            <div class="user-info">
                <div class="info-card">
                    <div class="detail-label">User Type</div>
                    <div class="detail-value"><?php echo htmlspecialchars($_SESSION['user_type']); ?></div>
                </div>
                <div class="info-card">
                    <div class="detail-label">Total Orders</div>
                    <div class="detail-value"><?php echo count($orders_with_status); ?></div>
                </div>
                <div class="info-card">
                    <div class="detail-label">Active Session</div>
                    <div class="detail-value"><?php echo date('M j, Y g:i A'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <button class="tab active" onclick="switchTab('dashboard')">📊 Dashboard</button>
            <button class="tab" onclick="switchTab('products')">🍕 Pizza Products</button>
            <button class="tab" onclick="switchTab('order')">🛒 Order Now</button>
        </div>
        
        <!-- Dashboard Tab -->
        <div id="dashboard-tab" class="tab-content active">
            <h2 class="section-title">Your Pizza Orders</h2>
            
            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($orders_with_status)): ?>
                <div class="orders-grid">
                    <?php foreach($orders_with_status as $order_data): 
                        $order = $order_data['order'];
                        $current_status = $order_data['current_status'];
                        $last_update = $order_data['last_update'];
                        $history = $order_data['history'];
                    ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                                <div class="order-status"><?php echo htmlspecialchars($current_status); ?></div>
                            </div>
                            
                            <div class="order-details">
                                <div class="detail-item">
                                    <span class="detail-label">Pizza Type</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($order['pizza_type']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Quantity</span>
                                    <span class="detail-value"><?php echo $order['quantity']; ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Order Time</span>
                                    <span class="detail-value"><?php echo date('M j, Y g:i A', strtotime($order['order_time'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Last Update</span>
                                    <span class="detail-value"><?php echo date('M j, Y g:i A', strtotime($last_update)); ?></span>
                                </div>
                            </div>
                            
                            <?php if (!empty($order['special_instructions'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Special Instructions</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($order['special_instructions']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-orders">
                    <h3>No orders found yet! 🍕</h3>
                    <p>Start by ordering from our delicious pizza selection!</p>
                    <button class="order-btn" onclick="switchTab('products')" style="width: auto; margin-top: 20px;">
                        View Pizza Products
                    </button>
                </div>
            <?php endif; ?>
        </div>
                <!-- Products Tab WITH REAL PIZZA IMAGES -->
<div id="products-tab" class="tab-content">
    <h2 class="section-title">Our Pizza Menu</h2>
    <p style="text-align: center; color: #666; margin-bottom: 30px;">Choose from our delicious selection of pizzas</p>
    
    <div class="products-grid">
        <?php 
        // Real pizza image URLs
        $pizza_images = [
            'Pepperoni' => 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=200&h=200&fit=crop&crop=center',
            'Hawaiian' => 'https://images.unsplash.com/photo-1604068549290-dea0e4a305ca?w=200&h=200&fit=crop&crop=center',
            'Vegetarian' => 'https://images.unsplash.com/photo-1604068549290-dea0e4a305ca?w=200&h=200&fit=crop&crop=center',
            'Supreme' => 'h ttps://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=200&h=200&fit=crop&crop=center',
            'Cheese Overload' => 'https://images.unsplash.com/photo-1571407970349-bc81e7e96d47?w=200&h=200&fit=crop&crop=center',
            'Meat Lovers' => 'https://images.unsplash.com/photo-1595708684082-a173bb3a06c5?w=200&h=200&fit=crop&crop=center',
            'Margherita' => 'https://images.unsplash.com/photo-1593560708920-61dd98c46a4e?w=200&h=200&fit=crop&crop=center',
            'BBQ Chicken' => 'https://images.unsplash.com/photo-1595708684082-a173bb3a06c5?w=200&h=200&fit=crop&crop=center'
        ];
        ?>
        
        <?php foreach($pizza_products as $pizza): ?>
            <div class="product-card <?php echo $pizza['popular'] ? 'popular' : ''; ?>">
                <?php if ($pizza['popular']): ?>
                    <div class="popular-badge">🔥 Popular</div>
                <?php endif; ?>
                
                <!-- Real Pizza Image -->
                <img src="<?php echo $pizza_images[$pizza['name']]; ?>" 
                     alt="<?php echo htmlspecialchars($pizza['name']); ?> Pizza" 
                     class="product-real-image">
                
                <div class="product-name"><?php echo $pizza['name']; ?></div>
                <div class="product-price">₱<?php echo number_format($pizza['price'], 0); ?></div>
                <div class="product-description"><?php echo $pizza['description']; ?></div>
                
                <form method="POST" action="order.php" style="margin-top: 15px;">
                    <input type="hidden" name="pizza_type" value="<?php echo $pizza['name']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="order-btn">
                        Order Now 🍕
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
    .product-real-image {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        margin: 0 auto 15px;
        display: block;
        border: 3px solid #ff6a00;
        transition: all 0.3s;
    }

    .product-card:hover .product-real-image {
        transform: scale(1.05);
        border-color: #e74c3c;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    .product-card.popular .product-real-image {
        border-color: #ffd700;
        box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
    }
</style>

        
        <!-- Order Tab -->
        <div id="order-tab" class="tab-content">
            <h2 class="section-title">Quick Order</h2>
            <p style="text-align: center; color: #666; margin-bottom: 30px;">Ready to order? Let's get started!</p>
            
            <div style="text-align: center; padding: 40px;">
                <h3 style="margin-bottom: 20px; color: #333;">Choose Your Order Method</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;">
                    <a href="#products-tab" onclick="switchTab('products')" 
                       style="display: block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 15px; text-decoration: none; transition: transform 0.3s;">
                        <div style="font-size: 3em; margin-bottom: 15px;">🍕</div>
                        <h4>Browse Menu</h4>
                        <p>View all pizza options with prices</p>
                    </a>
                    
                    <a href="order.php" 
                       style="display: block; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; padding: 30px; border-radius: 15px; text-decoration: none; transition: transform 0.3s;">
                        <div style="font-size: 3em; margin-bottom: 15px;">🛒</div>
                        <h4>Quick Order</h4>
                        <p>Go directly to order form</p>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Logout -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Activate selected tab
            event.target.classList.add('active');
        }
        
        // Auto-refresh orders every 30 seconds
        setInterval(() => {
            if (document.getElementById('dashboard-tab').classList.contains('active')) {
                window.location.reload();
            }
        }, 30000);
        // API Base URL
const API_BASE = 'http://localhost/pizza-status-secure/api.php';

// Function to load orders via API
async function loadOrdersViaAPI() {
    try {
        const username = '<?php echo $_SESSION["username"]; ?>';
        const response = await fetch(`${API_BASE}/orders?username=${encodeURIComponent(username)}`);
        const result = await response.json();
        
        if (result.success) {
            console.log('Orders loaded via API:', result.data);
            // Update your UI with the orders
            updateOrdersUI(result.data);
        } else {
            console.error('API Error:', result.error);
        }
    } catch (error) {
        console.error('Failed to fetch orders:', error);
    }
}

// Function to create order via API
async function createOrderAPI(orderData) {
    try {
        const response = await fetch(`${API_BASE}/create-order`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData)
        });
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('Failed to create order:', error);
        return { success: false, error: 'Network error' };
    }
}

// Test API on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Testing API connection...');
    loadOrdersViaAPI();
});
    </script>
</body>
</html>