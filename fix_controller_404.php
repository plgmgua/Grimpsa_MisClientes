<?php
/**
 * Fix Controller 404 Error
 * 
 * Run this via Sourcerer to diagnose and fix the "Invalid controller class: display" error
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
    <title>Fix Controller 404 Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { color: #3498db; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 3px; overflow-x: auto; }
        .path { font-family: monospace; font-size: 0.9em; color: #7f8c8d; }
        .code { background: #ecf0f1; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔧 Fix Controller 404 Error</h1>
    <p class="info">Error: "404 Invalid controller class: display"</p>
    
    <?php
    $controllerFile = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/src/Controller/DisplayController.php';
    $controllerNamespace = 'Grimpsa\Component\OdooContacts\Administrator\Controller';
    $controllerClass = $controllerNamespace . '\DisplayController';
    
    echo '<div class="section">';
    echo '<h2>1. Controller File Check</h2>';
    
    if (file_exists($controllerFile)) {
        echo '<p class="success">✓ Controller file exists</p>';
        echo '<p class="path">' . $controllerFile . '</p>';
        
        $content = file_get_contents($controllerFile);
        
        // Extract namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $fileNamespace = trim($matches[1]);
            echo '<p><strong>File namespace:</strong> <span class="path">' . $fileNamespace . '</span></p>';
            echo '<p><strong>Expected namespace:</strong> <span class="path">' . $controllerNamespace . '</span></p>';
            
            if ($fileNamespace === $controllerNamespace) {
                echo '<p class="success">✓ Namespace matches</p>';
            } else {
                echo '<p class="error">✗ Namespace mismatch!</p>';
            }
        }
        
        // Extract class name
        if (preg_match('/class\s+(\w+Controller)\s+extends/', $content, $matches)) {
            $className = trim($matches[1]);
            echo '<p><strong>Class name:</strong> <span class="path">' . $className . '</span></p>';
            
            if ($className === 'DisplayController') {
                echo '<p class="success">✓ Class name is correct</p>';
            }
        }
    } else {
        echo '<p class="error">✗ Controller file NOT found!</p>';
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>2. Test Class Loading</h2>';
    
    // Try to load the class
    if (file_exists($controllerFile)) {
        try {
            require_once $controllerFile;
            
            if (class_exists($controllerClass)) {
                echo '<p class="success">✓ Controller class can be loaded</p>';
                
                // Try to instantiate
                try {
                    $reflection = new ReflectionClass($controllerClass);
                    echo '<p class="success">✓ Controller class is valid and can be reflected</p>';
                    echo '<p><strong>Full class name:</strong> <span class="path">' . $reflection->getName() . '</span></p>';
                } catch (Exception $e) {
                    echo '<p class="error">✗ Cannot reflect class: ' . $e->getMessage() . '</p>';
                }
            } else {
                echo '<p class="error">✗ Controller class NOT found after require_once</p>';
                echo '<p>This suggests a namespace or class name issue.</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">✗ Error loading file: ' . $e->getMessage() . '</p>';
        } catch (Throwable $e) {
            echo '<p class="error">✗ Fatal error: ' . $e->getMessage() . '</p>';
        }
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>3. MVCFactory Configuration</h2>';
    
    $providerFile = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/services/provider.php';
    if (file_exists($providerFile)) {
        $providerContent = file_get_contents($providerFile);
        
        // Check MVCFactory registration
        if (preg_match("/MVCFactory\(['\"]([^'\"]+)['\"]\)/", $providerContent, $matches)) {
            $mvcNamespace = $matches[1];
            echo '<p><strong>MVCFactory namespace:</strong> <span class="path">' . $mvcNamespace . '</span></p>';
            
            // Expected: \Grimpsa\Component\OdooContacts
            $expectedMvcNamespace = '\\Grimpsa\\Component\\OdooContacts';
            if ($mvcNamespace === $expectedMvcNamespace || $mvcNamespace === 'Grimpsa\\Component\\OdooContacts') {
                echo '<p class="success">✓ MVCFactory namespace is correct</p>';
                
                // Explain how Joomla builds controller path
                echo '<p class="info">Joomla will look for controllers at:</p>';
                echo '<p class="path">' . str_replace('\\', '\\', $mvcNamespace) . '\\Administrator\\Controller\\DisplayController</p>';
                echo '<p class="path">Which should map to: ' . $controllerClass . '</p>';
            } else {
                echo '<p class="error">✗ MVCFactory namespace may be incorrect</p>';
            }
        }
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>4. Directory Structure</h2>';
    
    $controllerDir = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/src/Controller';
    if (is_dir($controllerDir)) {
        echo '<p class="success">✓ Controller directory exists</p>';
        echo '<p class="path">' . $controllerDir . '</p>';
        
        $files = scandir($controllerDir);
        echo '<p><strong>Files in Controller directory:</strong></p>';
        echo '<ul>';
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $controllerDir . '/' . $file;
                $isDir = is_dir($filePath) ? 'Directory' : 'File';
                echo '<li>' . $isDir . ': <span class="path">' . $file . '</span></li>';
            }
        }
        echo '</ul>';
    } else {
        echo '<p class="error">✗ Controller directory does not exist!</p>';
    }
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>5. Possible Solutions</h2>';
    echo '<ol>';
    echo '<li><strong>Clear all caches:</strong> System → Clear Cache, then restart PHP-FPM/Apache</li>';
    echo '<li><strong>Check file permissions:</strong> Controller file should be readable (644)</li>';
    echo '<li><strong>Verify namespace:</strong> Must be exactly <span class="code">Grimpsa\Component\OdooContacts\Administrator\Controller</span></li>';
    echo '<li><strong>Check MVCFactory:</strong> Should be registered with <span class="code">\\Grimpsa\\Component\\OdooContacts</span></li>';
    echo '<li><strong>Verify class name:</strong> Must be exactly <span class="code">DisplayController</span></li>';
    echo '</ol>';
    echo '</div>';
    
    echo '<div class="section">';
    echo '<h2>6. Manual Fix (if needed)</h2>';
    echo '<p>If the controller still can\'t be found, try adding this to the service provider:</p>';
    echo '<pre>';
    echo htmlspecialchars('// Explicitly require controller classes
$controllerFile = __DIR__ . \'/../src/Controller/DisplayController.php\';
if (file_exists($controllerFile)) {
    require_once $controllerFile;
}');
    echo '</pre>';
    echo '</div>';
    ?>
</div>
</body>
</html>

