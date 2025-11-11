<?php include 'check_auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pizza - Service Status</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="header-actions">
            <button id="refresh-btn" class="btn btn-outline">🔄 Refresh</button>
            <a href="logout.php" class="btn btn-outline">🚪 Logout</a>
        </div>
        <h1>Status Pizza</h1>
        <p>Real-time service status monitoring</p>
    </div>

    <main class="main">
        <div class="status-container">
            <div class="status-header">
                <h2>System Status</h2>
                <div id="last-updated" class="last-updated">Last updated: Loading...</div>
            </div>
            <ul id="status-list" class="status-list">
                <div class="loading">Loading services...</div>
            </ul>
        </div>
    </main>

    <script src="script.js"></script>
</body>
</html>