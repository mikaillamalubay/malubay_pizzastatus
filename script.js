// Form enhancements for login/signup pages
class AuthForms {
    constructor() {
        this.init();
    }

    init() {
        this.setupPasswordToggle();
        this.setupPasswordStrength();
        this.setupFormAnimations();
    }

    setupPasswordToggle() {
        const toggleButtons = document.querySelectorAll('.password-toggle');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const input = button.previousElementSibling;
                const type = input.type === 'password' ? 'text' : 'password';
                input.type = type;
                button.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
            });
        });
    }

    setupPasswordStrength() {
        const passwordInput = document.getElementById('password');
        const strengthBar = document.querySelector('.strength-bar');
        const requirements = document.querySelectorAll('.requirement');

        if (!passwordInput || !strengthBar) return;

        passwordInput.addEventListener('input', () => {
            const password = passwordInput.value;
            const strength = this.calculatePasswordStrength(password);
            
            // Update strength bar
            strengthBar.className = 'strength-bar';
            if (password.length > 0) {
                strengthBar.classList.add(`strength-${strength.level}`);
            }

            // Update requirements
            this.updateRequirements(requirements, password);
        });
    }

    calculatePasswordStrength(password) {
        let score = 0;
        
        if (password.length >= 8) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;

        const levels = ['weak', 'medium', 'strong'];
        return {
            level: levels[Math.min(score - 1, 2)] || 'weak',
            score: score
        };
    }

    updateRequirements(requirements, password) {
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[^A-Za-z0-9]/.test(password)
        };

        requirements.forEach(req => {
            const type = req.dataset.requirement;
            if (checks[type]) {
                req.classList.add('met');
                req.classList.remove('unmet');
                req.innerHTML = '✅ ' + req.textContent.replace(/[✅❌] /, '');
            } else {
                req.classList.add('unmet');
                req.classList.remove('met');
                req.innerHTML = '❌ ' + req.textContent.replace(/[✅❌] /, '');
            }
        });
    }

    setupFormAnimations() {
        const inputs = document.querySelectorAll('.form-input');
        
        inputs.forEach(input => {
            // Add focus animation
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', () => {
                if (!input.value) {
                    input.parentElement.classList.remove('focused');
                }
            });

            // Check initial state
            if (input.value) {
                input.parentElement.classList.add('focused');
            }
        });
    }
}

// Initialize auth forms when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.auth-form')) {
        new AuthForms();
    }
    // API Base URL
const API_BASE = 'http://localhost/pizza-status-secure/api.php';

// API Helper Functions
class PizzaAPI {
    // Get all orders for a user
    static async getOrders(username) {
        try {
            const response = await fetch(`${API_BASE}/orders?username=${encodeURIComponent(username)}`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching orders:', error);
            return { success: false, error: 'Failed to fetch orders' };
        }
    }

    // Get detailed status for a specific order
    static async getOrderStatus(orderId) {
        try {
            const response = await fetch(`${API_BASE}/order-status?order_id=${orderId}`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching order status:', error);
            return { success: false, error: 'Failed to fetch order status' };
        }
    }

    // Get all status types
    static async getStatusTypes() {
        try {
            const response = await fetch(`${API_BASE}/status-types`);
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error fetching status types:', error);
            return { success: false, error: 'Failed to fetch status types' };
        }
    }

    // Update order status
    static async updateOrderStatus(orderId, statusId) {
        try {
            const response = await fetch(`${API_BASE}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId,
                    status_id: statusId
                })
            });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error updating order status:', error);
            return { success: false, error: 'Failed to update order status' };
        }
    }

    // Create new order
    static async createOrder(orderData) {
        try {
            const response = await fetch(`${API_BASE}/create-order`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error creating order:', error);
            return { success: false, error: 'Failed to create order' };
        }
    }

    // Real-time order status polling
    static startOrderPolling(username, callback, interval = 10000) {
        const poll = async () => {
            const result = await this.getOrders(username);
            if (result.success) {
                callback(result.data);
            }
        };

        // Poll immediately
        poll();

        // Set up interval polling
        return setInterval(poll, interval);
    }
}

// Real-time updates for dashboard
function initializeRealTimeUpdates() {
    const username = document.body.dataset.username;
    if (!username) return;

    // Start polling for order updates every 10 seconds
    PizzaAPI.startOrderPolling(username, (orders) => {
        console.log('Orders updated:', orders);
        // You can update the UI here with new order data
        updateOrdersUI(orders);
    }, 10000);
}

// Example usage in your dashboard
async function loadOrders() {
    const username = '<?php echo $_SESSION["username"]; ?>';
    const result = await PizzaAPI.getOrders(username);
    
    if (result.success) {
        displayOrders(result.data);
    } else {
        showError(result.error);
    }
}

// Auto-refresh orders every 30 seconds
setInterval(loadOrders, 30000);

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeRealTimeUpdates();
    loadOrders();
});
});