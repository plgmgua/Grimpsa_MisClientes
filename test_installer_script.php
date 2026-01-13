<?php
/**
 * Manual Test Script for Installer
 * 
 * Upload this file to your Joomla root directory and access it via browser:
 * https://your-site.com/test_installer_script.php
 * 
 * DELETE THIS FILE AFTER TESTING!
 */

// Define Joomla execution
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);

// Load Joomla framework
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Bootstrap Joomla
$app = \Joomla\CMS\Factory::getApplication('site');

echo "<h1>Installer Script Test</h1>";
echo "<pre>";

// Test 1: Check if script file exists
$scriptPath = JPATH_BASE . '/administrator/components/com_odoocontacts/admin/script.php';
echo "Test 1: Checking if script file exists...\n";
if (file_exists($scriptPath)) {
    echo "✓ Script file exists: {$scriptPath}\n";
    echo "  File size: " . filesize($scriptPath) . " bytes\n";
    echo "  Is readable: " . (is_readable($scriptPath) ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ Script file NOT found: {$scriptPath}\n";
    echo "  This means the component was not extracted properly.\n";
    echo "</pre></body></html>";
    exit;
}

// Test 2: Try to include the script
echo "\nTest 2: Attempting to include script file...\n";
try {
    require_once $scriptPath;
    echo "✓ Script file included successfully\n";
} catch (Exception $e) {
    echo "✗ Error including script: " . $e->getMessage() . "\n";
    echo "</pre></body></html>";
    exit;
}

// Test 3: Check if class exists
echo "\nTest 3: Checking if installer class exists...\n";
if (class_exists('Com_OdoocontactsInstallerScript')) {
    echo "✓ Class Com_OdoocontactsInstallerScript exists\n";
} else {
    echo "✗ Class Com_OdoocontactsInstallerScript NOT found\n";
    echo "  Available classes:\n";
    $classes = get_declared_classes();
    foreach ($classes as $class) {
        if (strpos($class, 'Installer') !== false || strpos($class, 'Odoo') !== false) {
            echo "    - {$class}\n";
        }
    }
    echo "</pre></body></html>";
    exit;
}

// Test 4: Try to instantiate the class
echo "\nTest 4: Attempting to instantiate installer class...\n";
try {
    $installer = new Com_OdoocontactsInstallerScript();
    echo "✓ Installer class instantiated successfully\n";
} catch (Exception $e) {
    echo "✗ Error instantiating class: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    echo "</pre></body></html>";
    exit;
}

// Test 5: Check log files
echo "\nTest 5: Checking for log files...\n";
$logLocations = [
    JPATH_ADMINISTRATOR . '/logs/com_odoocontacts_install.log',
    JPATH_BASE . '/com_odoocontacts_install.log',
    sys_get_temp_dir() . '/com_odoocontacts_install.log',
    '/tmp/com_odoocontacts_install.log'
];

$foundLogs = false;
foreach ($logLocations as $logPath) {
    if (file_exists($logPath)) {
        echo "✓ Found log file: {$logPath}\n";
        echo "  Size: " . filesize($logPath) . " bytes\n";
        echo "  Last modified: " . date('Y-m-d H:i:s', filemtime($logPath)) . "\n";
        echo "  Content (last 20 lines):\n";
        $lines = file($logPath);
        $lastLines = array_slice($lines, -20);
        foreach ($lastLines as $line) {
            echo "    " . htmlspecialchars($line);
        }
        $foundLogs = true;
    }
}

if (!$foundLogs) {
    echo "✗ No log files found in any location\n";
}

// Test 6: Check directory permissions
echo "\nTest 6: Checking directory permissions...\n";
$dirsToCheck = [
    JPATH_ADMINISTRATOR . '/logs',
    JPATH_ADMINISTRATOR . '/components',
    JPATH_ADMINISTRATOR . '/components/com_odoocontacts',
    JPATH_ADMINISTRATOR . '/components/com_odoocontacts/admin',
    JPATH_BASE . '/tmp'
];

foreach ($dirsToCheck as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? 'Yes' : 'No';
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "  {$dir}: Writable={$writable}, Perms={$perms}\n";
    } else {
        echo "  {$dir}: Does not exist\n";
    }
}

// Test 7: Check PHP version
echo "\nTest 7: System Information...\n";
echo "  PHP Version: " . PHP_VERSION . "\n";
if (defined('JVERSION')) {
    echo "  Joomla Version: " . JVERSION . "\n";
} else {
    echo "  Joomla Version: Not defined\n";
}

echo "\n=== TEST COMPLETE ===\n";
echo "</pre>";
echo "<p><strong>IMPORTANT: Delete this file (test_installer_script.php) after testing!</strong></p>";

