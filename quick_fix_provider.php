<?php
/**
 * Quick Fix: Update Service Provider File
 * 
 * Run this via Sourcerer to fix the autoloading issue
 * This updates the provider.php file to explicitly require the component class
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
    <title>Quick Fix: Service Provider</title>
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
    <h1>🔧 Quick Fix: Service Provider</h1>
    
    <?php
    if (!file_exists($providerFile)) {
        echo '<p class="error">✗ Provider file not found: ' . $providerFile . '</p>';
        echo '<p>Please ensure the component files are installed.</p>';
        exit;
    }
    
    // Read current file
    $currentContent = file_get_contents($providerFile);
    
    // Check if fix is already applied
    if (strpos($currentContent, 'require_once $componentClassFile') !== false) {
        echo '<p class="success">✓ Fix already applied!</p>';
        echo '<p>The provider.php file already has the explicit require statement.</p>';
    } else {
        // Apply the fix
        $fixCode = "// Explicitly require the component class to ensure it's loaded
\$componentClassFile = __DIR__ . '/../src/Extension/OdooContactsComponent.php';
if (file_exists(\$componentClassFile)) {
    require_once \$componentClassFile;
}

";
        
        // Find the insertion point (before the use statement for OdooContactsComponent)
        $insertBefore = 'use Grimpsa\Component\OdooContacts\Administrator\Extension\OdooContactsComponent;';
        
        if (strpos($currentContent, $insertBefore) !== false) {
            // Insert the fix code before the use statement
            $newContent = str_replace($insertBefore, $fixCode . $insertBefore, $currentContent);
            
            // Backup original file
            $backupFile = $providerFile . '.backup.' . date('YmdHis');
            copy($providerFile, $backupFile);
            
            // Write updated file
            if (file_put_contents($providerFile, $newContent)) {
                echo '<p class="success">✓ Fix applied successfully!</p>';
                echo '<p>Backup saved to: <code>' . basename($backupFile) . '</code></p>';
                echo '<p>The provider.php file has been updated to explicitly require the component class.</p>';
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
    
    if (strpos($updatedContent, 'require_once $componentClassFile') !== false) {
        echo '<p class="success">✓ Verification passed - fix is in place</p>';
    } else {
        echo '<p class="error">✗ Verification failed - fix not found</p>';
    }
    
    // Check if class file exists
    $classFile = dirname($providerFile) . '/../src/Extension/OdooContactsComponent.php';
    if (file_exists($classFile)) {
        echo '<p class="success">✓ Component class file exists: ' . $classFile . '</p>';
    } else {
        echo '<p class="error">✗ Component class file NOT found: ' . $classFile . '</p>';
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

