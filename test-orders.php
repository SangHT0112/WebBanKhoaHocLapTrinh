<!-- Quick test: Check if orders page works -->
<?php
session_start();
$_SESSION['id'] = 1; // Simulate logged-in user
$_SESSION['username'] = 'Test User';
$_SESSION['avatar'] = 'default.png';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Orders Page Test</title>
</head>
<body>
    <h1>Testing Orders Page</h1>
    <p>Click link below to test:</p>
    <a href="/page/orders/orders.php">View Orders Page</a>
    <p>Current session: User ID = <?= $_SESSION['id'] ?? 'Not Set' ?></p>
</body>
</html>
