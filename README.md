he Pizza Tracker System allows customers to:

Browse a delicious pizza menu

Place orders online

Track order status in real-time

View order history

Manage their account

🚀 Features
🔐 Authentication & Security
User login/logout system

Session management

Secure password handling

User role-based access

🍕 Menu & Ordering
Interactive pizza menu with 8 varieties

Real-time pricing (Philippine Peso)

Popular item highlighting

Quick order functionality

Special instructions support

📊 Order Tracking
Real-time order status updates

Order history timeline

Status change notifications

Auto-refresh dashboard (30 seconds)

🎨 User Interface
Responsive design for all devices

Modern gradient backgrounds

Interactive tab system

Smooth animations and transitions

Mobile-friendly layout

🗂️ File Structure
text
pizza-status-secure/
├── dashboard.php          # Main dashboard with tabs
├── login.php             # User authentication
├── logout.php            # Session termination
├── order.php             # Order placement form
├── config.php            # Database configuration
├── style.css             # Global styles
└── README.md            # This file
📊 System Tabs
1. Dashboard Tab (📊)
User welcome and statistics

Active orders tracking

Order history with status

Real-time updates

2. Products Tab (🍕)
Complete pizza menu

Product details and pricing

Popular item badges

Direct ordering buttons

3. Order Now Tab (🛒)
Quick order access

Menu browsing shortcut

Order form redirection

🍕 Pizza Menu
Pizza	Price	Description	Popular
Pepperoni	₱299	Classic pepperoni with mozzarella cheese	🔥
Hawaiian	₱329	Ham, pineapple, and cheese	
Vegetarian	₱349	Fresh vegetables and mushrooms	
Supreme	₱399	All meats with vegetables	🔥
Cheese Overload	₱279	Four cheese blend	
Meat Lovers	₱429	All meat toppings	🔥
Margherita	₱269	Fresh basil and tomatoes	
BBQ Chicken	₱379	BBQ sauce with chicken	
🛠️ Installation
Prerequisites
PHP 7.4 or higher

MySQL 5.7 or higher

Web server (Apache/Nginx)

Modern web browser

Setup Steps
Clone or download the project files

bash
git clone [repository-url]
Configure database

Create MySQL database

Update config.php with your database credentials

Import required tables (orders, order_status, order_status_history, users)

Set up web server

Place files in web server directory (e.g., htdocs/pizza-status-secure/)

Ensure PHP and MySQL extensions are enabled

Configure permissions

bash
chmod 755 *.php
chmod 644 *.css
Access the application

text
http://localhost/pizza-status-secure/login.php
🗃️ Database Schema
Required Tables:
users - User accounts and authentication

orders - Order information and details

order_status - Status types (Received, Preparing, Baking, etc.)

order_status_history - Order status timeline

Sample Table Structure:
sql
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100),
    pizza_type VARCHAR(50),
    quantity INT,
    special_instructions TEXT,
    order_time DATETIME
);
🎯 Usage Guide
For Customers:
Login to your account

Browse the pizza menu in Products tab

Place order using Order Now or direct from menu

Track progress in Dashboard tab

View history of all past orders

Order Status Flow:
text
Order Received → Preparing → Baking → Quality Check → Out for Delivery → Delivered
🔧 Configuration
Database Configuration (config.php)
php
define('DB_HOST', 'localhost');
define('DB_NAME', 'pizza_tracker');
define('DB_USER', 'username');
define('DB_PASS', 'password');
Session Configuration
Session timeout: 24 minutes

Auto-redirect to login if not authenticated

Secure session management

🌐 API Endpoints
Available API Functions:
GET /api.php/orders?username={username} - Fetch user orders

POST /api.php/create-order - Create new order

Order status updates via database triggers

📱 Responsive Design
The system is optimized for:

Desktop: Full feature access

Tablet: Adaptive layout

Mobile: Collapsible navigation, touch-friendly buttons

🔒 Security Features
SQL injection prevention using prepared statements

XSS protection with htmlspecialchars()

Session hijacking protection

Input validation and sanitization

Secure password hashing

🚀 Performance Features
Auto-refresh orders every 30 seconds

Image optimization and lazy loading

Efficient database queries

Minimal external dependencies

🐛 Troubleshooting
Common Issues:
Login not working

Check database connection in config.php

Verify user table exists with proper columns

Orders not displaying

Ensure order tables are created

Check customer name matches session username

Images not loading

Verify internet connection for external images

Check browser console for errors

Style issues

Ensure CSS file is accessible

Check browser compatibility

Debug Mode:
Add to config.php for detailed errors:

php
ini_set('display_errors', 1);
error_reporting(E_ALL);
📞 Support
For technical support or questions:

Check this README file

Verify database configuration

Ensure all required PHP extensions are enabled

Check server error logs for detailed messages

🔄 Version History
v1.0 - Initial release with core functionality

v1.1 - Added real-time tracking and responsive design

v1.2 - Enhanced security and API integration

📄 License
This project is for educational and demonstration purposes. Please ensure proper licensing for production use.

Enjoy your pizza journey! 🍕

