<?php
/**
 * Fix Controller Autoloading Issue
 * 
 * Run this via Sourcerer to diagnose and fix controller loading
 */

defined('_JEXEC') or define('_JEXEC', 1);

if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', __DIR__);
    require_once JPATH_BASE . '/includes/defines.php';
    require_once JPATH_BASE . '/includes/framework.php';
    $app = \Joomla\CMS\Factory::getApplication('site');
}

use Joomla\CMS\Factory;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Controller Autoloading</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 3px; overflow-x: auto; }
        .path { font-family: monospace; font-size: 0.9em; color: #7f8c8d; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Fix Controller Autoloading</h1>
    
    <?php
    $controllerFile = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/src/Controller/DisplayController.php';
    
    echo '<div class="section">';
    echo '<h2>1. Check Controller File</h2>';
    
    if (file_exists($controllerFile)) {
        echo '<p class="success">✓ Controller file exists</p>';
        echo '<p class="path">' . $controllerFile . '</p>';
        
        // Check file contents
        $content = file_get_contents($controllerFile);
        
        // Check namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = trim($matches[1]);
            echo '<p><strong>Namespace:</strong> <span class="path">' . $namespace . '</span></p>';
            
            $expectedNamespace = 'Grimpsa\Component\OdooContacts\Administrator\Controller';
            if ($namespace === $expectedNamespace) {
                echo '<p class="success">✓ Namespace is correct</p>';
            } else {
                echo '<p class="error">✗ Namespace mismatch!</p>';
            }
        }
        
        // Check class name
        if (preg_match('/class\s+(\w+)\s+extends/', $content, $matches)) {
            $className = trim($matches[1]);
            echo '<p><strong>Class name:</strong> <span class="path">' . $className . '</span></p>';
            
            if ($className === 'DisplayController') {
                echo '<p class="success">✓ Class name is correct</p>';
            }
        }
        
        // Check syntax
        $syntaxCheck = shell_exec("php -l " . escapeshellarg($controllerFile) . " 2>&1");
        if (strpos($syntaxCheck, 'No syntax errors') !== false) {
            echo '<p class="success">✓ No PHP syntax errors</p>';
        } else {
            echo '<p class="error">✗ PHP syntax errors:</p>';
            echo '<pre>' . htmlspecialchars($syntaxCheck) . '</pre>';
        }
    } else {
        echo '<p class="error">✗ Controller file NOT found!</p>';
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>2. Test Class Loading</h2>';
    
    $fullClassName = 'Grimpsa\Component\OdooContacts\Administrator\Controller\DisplayController';
    
    // Check if class exists
    if (class_exists($fullClassName)) {
        echo '<p class="success">✓ Controller class is already loaded</p>';
    } else {
        echo '<p class="warning">⚠ Controller class is not loaded</p>';
        
        // Try to require it
        if (file_exists($controllerFile)) {
            try {
                require_once $controllerFile;
                if (class_exists($fullClassName)) {
                    echo '<p class="success">✓ Controller class loaded successfully after require_once</p>';
                } else {
                    echo '<p class="error">✗ Controller class still not found after require_once</p>';
                    echo '<p>This suggests a namespace or class name mismatch.</p>';
                }
            } catch (Exception $e) {
                echo '<p class="error">✗ Error requiring file: ' . $e->getMessage() . '</p>';
            } catch (Throwable $e) {
                echo '<p class="error">✗ Fatal error: ' . $e->getMessage() . '</p>';
            }
        }
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>3. Check Component Extension Class</h2>';
    
    // Check if the component extension class is loading controllers correctly
    $extensionFile = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/src/Extension/OdooContactsComponent.php';
    
    if (file_exists($extensionFile)) {
        $extContent = file_get_contents($extensionFile);
        
        // Check if it implements MVCFactoryInterface
        if (strpos($extContent, 'MVCFactory') !== false) {
            echo '<p class="success">✓ Extension class uses MVCFactory</p>';
        } else {
            echo '<p class="warning">⚠ Extension class may not be properly configured for MVC</p>';
        }
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>4. Check Service Provider</h2>';
    
    $providerFile = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/services/provider.php';
    if (file_exists($providerFile)) {
        $providerContent = file_get_contents($providerFile);
        
        // Check MVCFactory registration
        if (strpos($providerContent, "MVCFactory('\\\\Grimpsa\\\\Component\\\\OdooContacts')") !== false) {
            echo '<p class="success">✓ MVCFactory is registered with correct namespace</p>';
        } else {
            echo '<p class="warning">⚠ MVCFactory registration may be incorrect</p>';
        }
        
        // Show MVCFactory line
        if (preg_match("/MVCFactory\([^)]+\)/", $providerContent, $matches)) {
            echo '<p><strong>MVCFactory registration:</strong></p>';
            echo '<pre>' . htmlspecialchars($matches[0]) . '</pre>';
        }
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>5. Recommendations</h2>';
    echo '<ul>';
    echo '<li>Clear Joomla cache: <strong>System → Clear Cache</strong></li>';
    echo '<li>Clear OPcache: Restart PHP-FPM or Apache</li>';
    echo '<li>Verify namespace matches exactly: <span class="path">Grimpsa\Component\OdooContacts\Administrator\Controller</span></li>';
    echo '<li>Check that MVCFactory is properly registered in service provider</li>';
    echo '</ul>';
    echo '</div>';
    ?>
</div>
</body>
</html>

