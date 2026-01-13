<?php
/**
 * Upload Dispatcher and Update Provider
 * 
 * Run this via Sourcerer to add the admin Dispatcher and update provider.php
 */

defined('_JEXEC') or define('_JEXEC', 1);

if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', __DIR__);
    require_once JPATH_BASE . '/includes/defines.php';
    require_once JPATH_BASE . '/includes/framework.php';
    $app = \Joomla\CMS\Factory::getApplication('site');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Dispatcher Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Upload Dispatcher Fix</h1>
    
    <?php
    $dispatcherDir = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/src/Dispatcher';
    $dispatcherFile = $dispatcherDir . '/Dispatcher.php';
    $providerFile = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/services/provider.php';
    
    // Dispatcher content
    $dispatcherContent = '<?php
/**
 * @package     Grimpsa.Administrator
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

namespace Grimpsa\Component\OdooContacts\Administrator\Dispatcher;

defined(\'_JEXEC\') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * ComponentDispatcher class for com_odoocontacts
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * The extension namespace
     *
     * @var    string
     */
    protected $namespace = \'Grimpsa\\\\Component\\\\OdooContacts\';
}
';
    
    echo '<h2>1. Create Dispatcher Directory</h2>';
    
    if (!is_dir($dispatcherDir)) {
        if (mkdir($dispatcherDir, 0755, true)) {
            echo '<p class="success">✓ Dispatcher directory created</p>';
        } else {
            echo '<p class="error">✗ Failed to create Dispatcher directory</p>';
            echo '<p>Please create it manually: <code>' . $dispatcherDir . '</code></p>';
        }
    } else {
        echo '<p class="success">✓ Dispatcher directory exists</p>';
    }
    
    echo '<h2>2. Create Dispatcher File</h2>';
    
    if (file_exists($dispatcherFile)) {
        echo '<p class="warning">⚠ Dispatcher file already exists</p>';
        echo '<p>Checking if it needs to be updated...</p>';
        
        $existingContent = file_get_contents($dispatcherFile);
        if (strpos($existingContent, 'namespace Grimpsa\\Component\\OdooContacts\\Administrator\\Dispatcher') !== false) {
            echo '<p class="success">✓ Dispatcher file is correct</p>';
        } else {
            // Backup and update
            $backupFile = $dispatcherFile . '.backup.' . date('YmdHis');
            copy($dispatcherFile, $backupFile);
            
            if (file_put_contents($dispatcherFile, $dispatcherContent)) {
                echo '<p class="success">✓ Dispatcher file updated</p>';
                echo '<p>Backup saved to: <code>' . basename($backupFile) . '</code></p>';
            } else {
                echo '<p class="error">✗ Failed to update Dispatcher file</p>';
            }
        }
    } else {
        if (file_put_contents($dispatcherFile, $dispatcherContent)) {
            echo '<p class="success">✓ Dispatcher file created</p>';
        } else {
            echo '<p class="error">✗ Failed to create Dispatcher file</p>';
            echo '<p>Please create it manually with the following content:</p>';
            echo '<pre>' . htmlspecialchars($dispatcherContent) . '</pre>';
        }
    }
    
    echo '<h2>3. Update Provider to Require Dispatcher</h2>';
    
    if (file_exists($providerFile)) {
        $providerContent = file_get_contents($providerFile);
        
        // Check if Dispatcher require is already there
        if (strpos($providerContent, 'Dispatcher.php') !== false && strpos($providerContent, 'require_once') !== false) {
            echo '<p class="success">✓ Provider already requires Dispatcher</p>';
        } else {
            // Add Dispatcher require after controller require
            $dispatcherRequire = "\n// Explicitly require dispatcher class to ensure it's loaded\n\$dispatcherFile = __DIR__ . '/../src/Dispatcher/Dispatcher.php';\nif (file_exists(\$dispatcherFile)) {\n    require_once \$dispatcherFile;\n}\n";
            
            // Find insertion point (after controller require, before use statement)
            $insertAfter = 'require_once $controllerFile;';
            $insertBefore = 'use Grimpsa\Component\OdooContacts\Administrator\Extension\OdooContactsComponent;';
            
            if (strpos($providerContent, $insertAfter) !== false && strpos($providerContent, $insertBefore) !== false) {
                $newContent = str_replace(
                    $insertAfter . "\n\n" . $insertBefore,
                    $insertAfter . $dispatcherRequire . "\n" . $insertBefore,
                    $providerContent
                );
                
                // Backup
                $backupFile = $providerFile . '.backup.' . date('YmdHis');
                copy($providerFile, $backupFile);
                
                if (file_put_contents($providerFile, $newContent)) {
                    echo '<p class="success">✓ Provider updated to require Dispatcher</p>';
                    echo '<p>Backup saved to: <code>' . basename($backupFile) . '</code></p>';
                } else {
                    echo '<p class="error">✗ Failed to update provider file</p>';
                }
            } else {
                echo '<p class="warning">⚠ Could not find insertion point in provider file</p>';
                echo '<p>Please add this code manually after the controller require:</p>';
                echo '<pre>' . htmlspecialchars($dispatcherRequire) . '</pre>';
            }
        }
    } else {
        echo '<p class="error">✗ Provider file not found</p>';
    }
    
    echo '<h2>4. Verification</h2>';
    
    if (file_exists($dispatcherFile)) {
        echo '<p class="success">✓ Dispatcher file exists</p>';
        
        // Check syntax
        $syntaxCheck = shell_exec("php -l " . escapeshellarg($dispatcherFile) . " 2>&1");
        if (strpos($syntaxCheck, 'No syntax errors') !== false) {
            echo '<p class="success">✓ No PHP syntax errors</p>';
        } else {
            echo '<p class="error">✗ PHP syntax errors:</p>';
            echo '<pre>' . htmlspecialchars($syntaxCheck) . '</pre>';
        }
    } else {
        echo '<p class="error">✗ Dispatcher file NOT found</p>';
    }
    
    echo '<h2>5. Next Steps</h2>';
    echo '<ol>';
    echo '<li>Clear Joomla cache: <strong>System → Clear Cache</strong></li>';
    echo '<li>Restart PHP-FPM or Apache to clear OPcache</li>';
    echo '<li>Run <code>diagnose_controller_404.php</code> to verify everything is working</li>';
    echo '<li>Test component: <strong>Components → COM_ODOOCONTACTS</strong></li>';
    echo '</ol>';
    ?>
</div>
</body>
</html>

