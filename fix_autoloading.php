<?php
/**
 * Fix Autoloading Issues for com_odoocontacts
 * 
 * Run this via Sourcerer to diagnose and fix autoloading
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
    <title>Fix Autoloading - com_odoocontacts</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #2c3e50; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 3px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { color: #3498db; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 3px; overflow-x: auto; }
        .path { font-family: monospace; font-size: 0.9em; color: #7f8c8d; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Fix Autoloading Issues</h1>
    
    <?php
    $extensionFile = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/src/Extension/OdooContactsComponent.php';
    $providerFile = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/services/provider.php';
    
    echo '<div class="section">';
    echo '<h2>1. Check File Existence</h2>';
    
    if (file_exists($extensionFile)) {
        echo '<p class="success">✓ Extension file exists: <span class="path">' . $extensionFile . '</span></p>';
        echo '<p>File size: ' . filesize($extensionFile) . ' bytes</p>';
        echo '<p>Is readable: ' . (is_readable($extensionFile) ? 'Yes' : 'No') . '</p>';
    } else {
        echo '<p class="error">✗ Extension file NOT found!</p>';
    }
    
    if (file_exists($providerFile)) {
        echo '<p class="success">✓ Provider file exists: <span class="path">' . $providerFile . '</span></p>';
    } else {
        echo '<p class="error">✗ Provider file NOT found!</p>';
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>2. Check File Contents</h2>';
    
    if (file_exists($extensionFile)) {
        $content = file_get_contents($extensionFile);
        
        // Check namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = trim($matches[1]);
            echo '<p><strong>Namespace found:</strong> <span class="path">' . $namespace . '</span></p>';
            
            $expectedNamespace = 'Grimpsa\Component\OdooContacts\Administrator\Extension';
            if ($namespace === $expectedNamespace) {
                echo '<p class="success">✓ Namespace is correct</p>';
            } else {
                echo '<p class="error">✗ Namespace mismatch!</p>';
                echo '<p>Expected: <span class="path">' . $expectedNamespace . '</span></p>';
                echo '<p>Found: <span class="path">' . $namespace . '</span></p>';
            }
        } else {
            echo '<p class="error">✗ No namespace found in file!</p>';
        }
        
        // Check class name
        if (preg_match('/class\s+(\w+)\s+extends/', $content, $matches)) {
            $className = trim($matches[1]);
            echo '<p><strong>Class name found:</strong> <span class="path">' . $className . '</span></p>';
            
            $expectedClass = 'OdooContactsComponent';
            if ($className === $expectedClass) {
                echo '<p class="success">✓ Class name is correct</p>';
            } else {
                echo '<p class="error">✗ Class name mismatch!</p>';
            }
        } else {
            echo '<p class="error">✗ No class definition found!</p>';
        }
        
        // Check for syntax errors
        $syntaxCheck = shell_exec("php -l " . escapeshellarg($extensionFile) . " 2>&1");
        if (strpos($syntaxCheck, 'No syntax errors') !== false) {
            echo '<p class="success">✓ No PHP syntax errors</p>';
        } else {
            echo '<p class="error">✗ PHP syntax errors found:</p>';
            echo '<pre>' . htmlspecialchars($syntaxCheck) . '</pre>';
        }
        
        // Show first 50 lines
        echo '<h3>First 50 lines of file:</h3>';
        echo '<pre>' . htmlspecialchars(implode('', array_slice(file($extensionFile), 0, 50))) . '</pre>';
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>3. Check Service Provider</h2>';
    
    if (file_exists($providerFile)) {
        $providerContent = file_get_contents($providerFile);
        
        // Check if it references the class
        if (strpos($providerContent, 'OdooContactsComponent') !== false) {
            echo '<p class="success">✓ Provider references OdooContactsComponent</p>';
        } else {
            echo '<p class="error">✗ Provider does not reference OdooContactsComponent</p>';
        }
        
        // Check use statement
        if (preg_match('/use\s+([^;]+OdooContactsComponent[^;]*);/', $providerContent, $matches)) {
            $useStatement = trim($matches[1]);
            echo '<p><strong>Use statement:</strong> <span class="path">' . $useStatement . '</span></p>';
            
            $expectedUse = 'Grimpsa\Component\OdooContacts\Administrator\Extension\OdooContactsComponent';
            if ($useStatement === $expectedUse) {
                echo '<p class="success">✓ Use statement is correct</p>';
            } else {
                echo '<p class="error">✗ Use statement mismatch!</p>';
                echo '<p>Expected: <span class="path">' . $expectedUse . '</span></p>';
                echo '<p>Found: <span class="path">' . $useStatement . '</span></p>';
            }
        } else {
            echo '<p class="error">✗ No use statement found for OdooContactsComponent!</p>';
        }
        
        // Show line 27 (where error occurs)
        $lines = file($providerFile);
        if (isset($lines[26])) { // Line 27 is index 26
            echo '<h3>Line 27 (where error occurs):</h3>';
            echo '<pre>' . htmlspecialchars($lines[26]) . '</pre>';
        }
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>4. Test Class Loading</h2>';
    
    // Try to manually include and check
    if (file_exists($extensionFile)) {
        try {
            // Check if class is already loaded
            if (class_exists('Grimpsa\Component\OdooContacts\Administrator\Extension\OdooContactsComponent')) {
                echo '<p class="success">✓ Class is already loaded</p>';
            } else {
                echo '<p class="warning">⚠ Class is not loaded</p>';
                
                // Try to require it
                echo '<p>Attempting to require file...</p>';
                try {
                    require_once $extensionFile;
                    if (class_exists('Grimpsa\Component\OdooContacts\Administrator\Extension\OdooContactsComponent')) {
                        echo '<p class="success">✓ Class loaded successfully after require_once</p>';
                    } else {
                        echo '<p class="error">✗ Class still not found after require_once</p>';
                    }
                } catch (Exception $e) {
                    echo '<p class="error">✗ Error requiring file: ' . $e->getMessage() . '</p>';
                } catch (Throwable $e) {
                    echo '<p class="error">✗ Fatal error requiring file: ' . $e->getMessage() . '</p>';
                }
            }
        } catch (Exception $e) {
            echo '<p class="error">✗ Error: ' . $e->getMessage() . '</p>';
        }
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>5. Check Autoloader</h2>';
    
    // Check if namespace path is registered
    $manifestPath = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/config.xml';
    if (file_exists($manifestPath)) {
        $xml = simplexml_load_file($manifestPath);
        if ($xml) {
            $namespace = (string)$xml->namespace['path'];
            echo '<p><strong>Namespace path in config.xml:</strong> <span class="path">' . $namespace . '</span></p>';
        }
    }
    
    // Check Joomla's component helper
    $componentPath = JPATH_ADMINISTRATOR . '/components/com_odoocontacts';
    echo '<p><strong>Component path:</strong> <span class="path">' . $componentPath . '</span></p>';
    
    // Check if src directory exists
    $srcPath = $componentPath . '/src';
    if (is_dir($srcPath)) {
        echo '<p class="success">✓ src/ directory exists</p>';
        echo '<p>Contents of src/:</p>';
        $srcContents = scandir($srcPath);
        echo '<ul>';
        foreach ($srcContents as $item) {
            if ($item !== '.' && $item !== '..') {
                $itemPath = $srcPath . '/' . $item;
                $type = is_dir($itemPath) ? 'Directory' : 'File';
                echo '<li>' . $type . ': <span class="path">' . $item . '</span></li>';
            }
        }
        echo '</ul>';
    } else {
        echo '<p class="error">✗ src/ directory does not exist!</p>';
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>6. Recommendations</h2>';
    echo '<ul>';
    echo '<li>Clear Joomla cache: <strong>System → Clear Cache</strong></li>';
    echo '<li>Clear OPcache if enabled: Restart PHP-FPM or Apache</li>';
    echo '<li>Check file permissions: Files should be readable (644) and directories (755)</li>';
    echo '<li>Verify namespace matches exactly: <span class="path">Grimpsa\Component\OdooContacts\Administrator\Extension</span></li>';
    echo '</ul>';
    echo '</div>';
    ?>
</div>
</body>
</html>

