<?php
/**
 * Diagnose Controller 404 Error
 * 
 * Run this via Sourcerer to diagnose why the controller isn't being found
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
    <title>Diagnose Controller 404</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { color: #3498db; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 3px; overflow-x: auto; font-size: 0.9em; }
        .path { font-family: monospace; font-size: 0.9em; color: #7f8c8d; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #34495e; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Diagnose Controller 404 Error</h1>
    
    <?php
    $controllerFile = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/src/Controller/DisplayController.php';
    $controllerClass = 'Grimpsa\Component\OdooContacts\Administrator\Controller\DisplayController';
    $providerFile = JPATH_ADMINISTRATOR . '/components/com_odoocontacts/services/provider.php';
    
    echo '<h2>1. File System Check</h2>';
    echo '<table>';
    echo '<tr><th>Item</th><th>Status</th><th>Path</th></tr>';
    
    // Check controller file
    if (file_exists($controllerFile)) {
        echo '<tr><td>Controller File</td><td class="success">✓ Exists</td><td class="path">' . $controllerFile . '</td></tr>';
        echo '<tr><td>File Size</td><td>' . filesize($controllerFile) . ' bytes</td><td></td></tr>';
        echo '<tr><td>File Permissions</td><td>' . substr(sprintf('%o', fileperms($controllerFile)), -4) . '</td><td></td></tr>';
        echo '<tr><td>Readable</td><td>' . (is_readable($controllerFile) ? '<span class="success">✓ Yes</span>' : '<span class="error">✗ No</span>') . '</td><td></td></tr>';
    } else {
        echo '<tr><td>Controller File</td><td class="error">✗ NOT FOUND</td><td class="path">' . $controllerFile . '</td></tr>';
    }
    
    // Check provider file
    if (file_exists($providerFile)) {
        echo '<tr><td>Provider File</td><td class="success">✓ Exists</td><td class="path">' . $providerFile . '</td></tr>';
    } else {
        echo '<tr><td>Provider File</td><td class="error">✗ NOT FOUND</td><td class="path">' . $providerFile . '</td></tr>';
    }
    
    echo '</table>';
    
    echo '<h2>2. Class Loading Test</h2>';
    
    // Try to load the class
    if (file_exists($controllerFile)) {
        try {
            // Clear any previous class definition
            if (class_exists($controllerClass, false)) {
                echo '<p class="warning">⚠ Class already defined (this might cause issues)</p>';
            }
            
            // Try to require it
            require_once $controllerFile;
            
            if (class_exists($controllerClass, false)) {
                echo '<p class="success">✓ Controller class can be loaded</p>';
                
                // Try reflection
                try {
                    $reflection = new ReflectionClass($controllerClass);
                    echo '<p class="success">✓ Class can be reflected</p>';
                    echo '<p><strong>Full class name:</strong> <span class="path">' . $reflection->getName() . '</span></p>';
                    echo '<p><strong>Namespace:</strong> <span class="path">' . $reflection->getNamespaceName() . '</span></p>';
                    echo '<p><strong>Short name:</strong> <span class="path">' . $reflection->getShortName() . '</span></p>';
                    echo '<p><strong>Parent class:</strong> <span class="path">' . $reflection->getParentClass()->getName() . '</span></p>';
                    
                    // Check methods
                    $methods = $reflection->getMethods();
                    echo '<p><strong>Methods:</strong> ';
                    foreach ($methods as $method) {
                        echo $method->getName() . ' ';
                    }
                    echo '</p>';
                } catch (Exception $e) {
                    echo '<p class="error">✗ Reflection error: ' . $e->getMessage() . '</p>';
                }
            } else {
                echo '<p class="error">✗ Controller class NOT found after require_once</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">✗ Error loading file: ' . $e->getMessage() . '</p>';
        } catch (Throwable $e) {
            echo '<p class="error">✗ Fatal error: ' . $e->getMessage() . '</p>';
        }
    }
    
    echo '<h2>3. Provider File Analysis</h2>';
    
    if (file_exists($providerFile)) {
        $providerContent = file_get_contents($providerFile);
        
        // Check for controller require
        if (strpos($providerContent, 'DisplayController') !== false) {
            echo '<p class="success">✓ Provider file references DisplayController</p>';
            
            // Show the relevant section
            $lines = explode("\n", $providerContent);
            echo '<p><strong>Relevant lines from provider.php:</strong></p>';
            echo '<pre>';
            foreach ($lines as $i => $line) {
                if (strpos($line, 'Controller') !== false || strpos($line, 'controller') !== false || ($i >= 23 && $i <= 35)) {
                    echo sprintf('%3d: %s', $i + 1, htmlspecialchars($line)) . "\n";
                }
            }
            echo '</pre>';
        } else {
            echo '<p class="error">✗ Provider file does NOT reference DisplayController</p>';
        }
        
        // Check MVCFactory registration
        if (preg_match("/MVCFactory\(['\"]([^'\"]+)['\"]\)/", $providerContent, $matches)) {
            $mvcNamespace = $matches[1];
            echo '<p><strong>MVCFactory namespace:</strong> <span class="path">' . $mvcNamespace . '</span></p>';
            
            // Expected controller path
            $expectedControllerPath = str_replace('\\\\', '\\', $mvcNamespace) . '\\Administrator\\Controller\\DisplayController';
            echo '<p><strong>Expected controller path:</strong> <span class="path">' . $expectedControllerPath . '</span></p>';
            echo '<p><strong>Actual controller class:</strong> <span class="path">' . $controllerClass . '</span></p>';
            
            if ($expectedControllerPath === $controllerClass) {
                echo '<p class="success">✓ Namespace paths match</p>';
            } else {
                echo '<p class="error">✗ Namespace paths DO NOT match!</p>';
            }
        }
    }
    
    echo '<h2>4. Directory Structure</h2>';
    
    $adminComponentDir = JPATH_ADMINISTRATOR . '/components/com_odoocontacts';
    $srcDir = $adminComponentDir . '/src';
    $controllerDir = $srcDir . '/Controller';
    
    echo '<table>';
    echo '<tr><th>Directory</th><th>Status</th><th>Path</th></tr>';
    
    $dirs = [
        'Admin Component' => $adminComponentDir,
        'src' => $srcDir,
        'Controller' => $controllerDir,
        'Extension' => $srcDir . '/Extension',
        'View' => $srcDir . '/View',
        'services' => $adminComponentDir . '/services',
    ];
    
    foreach ($dirs as $name => $dir) {
        if (is_dir($dir)) {
            echo '<tr><td>' . $name . '</td><td class="success">✓ Exists</td><td class="path">' . $dir . '</td></tr>';
        } else {
            echo '<tr><td>' . $name . '</td><td class="error">✗ Missing</td><td class="path">' . $dir . '</td></tr>';
        }
    }
    
    echo '</table>';
    
    // List files in Controller directory
    if (is_dir($controllerDir)) {
        $files = scandir($controllerDir);
        echo '<p><strong>Files in Controller directory:</strong></p>';
        echo '<ul>';
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $controllerDir . '/' . $file;
                $isDir = is_dir($filePath);
                echo '<li>' . ($isDir ? '📁' : '📄') . ' ' . $file;
                if (!$isDir) {
                    echo ' (' . filesize($filePath) . ' bytes)';
                }
                echo '</li>';
            }
        }
        echo '</ul>';
    }
    
    echo '<h2>5. Autoloader Check</h2>';
    
    // Check if autoloader can find the class
    spl_autoload_register(function ($class) use ($controllerClass) {
        if ($class === $controllerClass) {
            echo '<p class="info">ℹ Autoloader called for: <span class="path">' . $class . '</span></p>';
        }
    });
    
    // Try to use the class
    if (class_exists($controllerClass)) {
        echo '<p class="success">✓ Class exists and can be autoloaded</p>';
    } else {
        echo '<p class="warning">⚠ Class does not exist (but file was required)</p>';
    }
    
    echo '<h2>6. Recommendations</h2>';
    echo '<ol>';
    echo '<li><strong>Clear all caches:</strong> System → Clear Cache, then restart PHP-FPM/Apache</li>';
    echo '<li><strong>Check OPcache:</strong> If enabled, restart PHP-FPM to clear OPcache</li>';
    echo '<li><strong>Verify file permissions:</strong> Controller file should be readable (644)</li>';
    echo '<li><strong>Check namespace:</strong> Must match exactly: <span class="path">Grimpsa\Component\OdooContacts\Administrator\Controller</span></li>';
    echo '<li><strong>Verify MVCFactory:</strong> Should be registered with: <span class="path">\\Grimpsa\\Component\\OdooContacts</span></li>';
    echo '</ol>';
    
    echo '<h2>7. Test MVCFactory Directly</h2>';
    
    try {
        // Try to get the application
        $app = Factory::getApplication('administrator');
        
        // Try to get the component
        $component = $app->bootComponent('com_odoocontacts');
        
        if ($component) {
            echo '<p class="success">✓ Component can be booted</p>';
            
            // Try to get MVCFactory
            $mvcFactory = $component->getMVCFactory();
            if ($mvcFactory) {
                echo '<p class="success">✓ MVCFactory is available</p>';
                
                // Try to create controller
                try {
                    $controller = $mvcFactory->createController('Display', 'Administrator');
                    if ($controller) {
                        echo '<p class="success">✓ Controller can be created via MVCFactory</p>';
                        echo '<p><strong>Controller class:</strong> <span class="path">' . get_class($controller) . '</span></p>';
                    } else {
                        echo '<p class="error">✗ MVCFactory cannot create Display controller</p>';
                    }
                } catch (Exception $e) {
                    echo '<p class="error">✗ Error creating controller: ' . $e->getMessage() . '</p>';
                    echo '<p class="error">Error details: ' . $e->getTraceAsString() . '</p>';
                }
            } else {
                echo '<p class="error">✗ MVCFactory is not available</p>';
            }
        } else {
            echo '<p class="error">✗ Component cannot be booted</p>';
        }
    } catch (Exception $e) {
        echo '<p class="error">✗ Error testing MVCFactory: ' . $e->getMessage() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }
    ?>
</div>
</body>
</html>

