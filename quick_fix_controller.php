<?php
/**
 * Quick Fix: Update Service Provider to Require Controller
 * 
 * Run this via Sourcerer to fix the controller 404 error
 */

defined('_JEXEC') or define('_JEXEC', 1);

if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', __DIR__);
    require_once JPATH_BASE . '/includes/defines.php';
    require_once JPATH_BASE . '/includes/framework.php';
    $app = \Joomla\CMS\Factory::getApplication('site');
}

$providerFile = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/services/provider.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Fix: Controller Autoloading</title>
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
    <h1>🔧 Quick Fix: Controller Autoloading</h1>
    
    <?php
    if (!file_exists($providerFile)) {
        echo '<p class="error">✗ Provider file not found: ' . $providerFile . '</p>';
        exit;
    }
    
    // Read current file
    $currentContent = file_get_contents($providerFile);
    
    // Check if fix is already applied
    if (strpos($currentContent, 'require_once $controllerFile') !== false) {
        echo '<p class="success">✓ Fix already applied!</p>';
        echo '<p>The provider.php file already requires the DisplayController.</p>';
    } else {
        // Apply the fix
        $fixCode = "// Explicitly require controller classes to ensure they're loaded
\$controllerFile = __DIR__ . '/../src/Controller/DisplayController.php';
if (file_exists(\$controllerFile)) {
    require_once \$controllerFile;
}

";
        
        // Find the insertion point (after the component class require, before the use statement)
        $insertAfter = 'require_once $componentClassFile;';
        $insertBefore = 'use Grimpsa\Component\OdooContacts\Administrator\Extension\OdooContactsComponent;';
        
        if (strpos($currentContent, $insertAfter) !== false && strpos($currentContent, $insertBefore) !== false) {
            // Insert the fix code after component require and before use statement
            $newContent = str_replace(
                $insertAfter . "\n\n" . $insertBefore,
                $insertAfter . "\n\n" . $fixCode . $insertBefore,
                $currentContent
            );
            
            // Backup original file
            $backupFile = $providerFile . '.backup.' . date('YmdHis');
            copy($providerFile, $backupFile);
            
            // Write updated file
            if (file_put_contents($providerFile, $newContent)) {
                echo '<p class="success">✓ Fix applied successfully!</p>';
                echo '<p>Backup saved to: <code>' . basename($backupFile) . '</code></p>';
                echo '<p>The provider.php file now explicitly requires the DisplayController.</p>';
            } else {
                echo '<p class="error">✗ Failed to write file. Check permissions.</p>';
            }
        } else {
            echo '<p class="error">✗ Could not find insertion point in file.</p>';
            echo '<p>File structure may be different. Please update manually.</p>';
        }
    }
    
    // Verify the fix
    echo '<h2>Verification</h2>';
    $updatedContent = file_get_contents($providerFile);
    
    // Check for various possible patterns
    $found = false;
    if (strpos($updatedContent, 'require_once $controllerFile') !== false) {
        $found = true;
    } elseif (strpos($updatedContent, 'DisplayController.php') !== false && strpos($updatedContent, 'require_once') !== false) {
        $found = true;
    } elseif (preg_match('/require_once.*Controller.*DisplayController/', $updatedContent)) {
        $found = true;
    }
    
    if ($found) {
        echo '<p class="success">✓ Verification passed - fix is in place</p>';
        
        // Show the relevant section
        $lines = explode("\n", $updatedContent);
        $inControllerSection = false;
        $controllerLines = [];
        foreach ($lines as $i => $line) {
            if (strpos($line, 'controller') !== false || strpos($line, 'Controller') !== false) {
                $inControllerSection = true;
            }
            if ($inControllerSection && ($i < 35)) { // Show first 35 lines which should include the fix
                $controllerLines[] = ($i + 1) . ': ' . htmlspecialchars($line);
                if (count($controllerLines) >= 10) break;
            }
        }
        if (!empty($controllerLines)) {
            echo '<p><strong>Relevant section of provider.php:</strong></p>';
            echo '<pre>' . implode("\n", $controllerLines) . '</pre>';
        }
    } else {
        echo '<p class="warning">⚠ Verification pattern not found, but file was modified</p>';
        echo '<p>Please check the file manually or try accessing the component.</p>';
    }
    
    // Check if controller file exists
    $controllerFile = dirname($providerFile) . '/../src/Controller/DisplayController.php';
    if (file_exists($controllerFile)) {
        echo '<p class="success">✓ Controller file exists: ' . $controllerFile . '</p>';
    } else {
        echo '<p class="error">✗ Controller file NOT found: ' . $controllerFile . '</p>';
    }
    
    echo '<h2>Next Steps</h2>';
    echo '<ol>';
    echo '<li>Clear Joomla cache: <strong>System → Clear Cache</strong></li>';
    echo '<li>If using OPcache, restart PHP-FPM or Apache</li>';
    echo '<li>Test component: <strong>Components → COM_ODOOCONTACTS</strong></li>';
    echo '</ol>';
    ?>
</div>
</body>
</html>

