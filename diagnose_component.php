<?php
/**
 * Component Diagnostic Script for com_odoocontacts
 * 
 * Run this via Sourcerer or upload to Joomla root and access via browser
 * 
 * Usage with Sourcerer:
 * 1. Install Sourcerer extension if not already installed
 * 2. Create a new article/page
 * 3. Add {source} tag with this script content
 * 4. Or upload to root and access: your-site.com/diagnose_component.php
 */

// Security check
defined('_JEXEC') or define('_JEXEC', 1);

// If running standalone (not via Sourcerer)
if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', __DIR__);
    require_once JPATH_BASE . '/includes/defines.php';
    require_once JPATH_BASE . '/includes/framework.php';
    $app = \Joomla\CMS\Factory::getApplication('site');
}

use Joomla\CMS\Factory;

// Start output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Component Diagnostic - com_odoocontacts</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; border-left: 4px solid #3498db; padding-left: 10px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 3px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .info { color: #3498db; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #3498db; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .path { font-family: monospace; font-size: 0.9em; color: #7f8c8d; }
        .summary { background: #ecf0f1; padding: 15px; border-radius: 3px; margin: 20px 0; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Component Diagnostic: com_odoocontacts</h1>
    <p class="info">Run time: <?php echo date('Y-m-d H:i:s'); ?></p>
    
    <?php
    $issues = [];
    $warnings = [];
    $success = [];
    
    // ============================================
    // SECTION 1: System Information
    // ============================================
    echo '<h2>1. System Information</h2>';
    echo '<div class="section">';
    echo '<table>';
    echo '<tr><th>Setting</th><th>Value</th></tr>';
    echo '<tr><td>Joomla Version</td><td>' . (defined('JVERSION') ? JVERSION : 'Not defined') . '</td></tr>';
    echo '<tr><td>PHP Version</td><td>' . PHP_VERSION . '</td></tr>';
    echo '<tr><td>Joomla Root</td><td class="path">' . JPATH_ROOT . '</td></tr>';
    echo '<tr><td>Administrator Path</td><td class="path">' . JPATH_ADMINISTRATOR . '</td></tr>';
    echo '<tr><td>Site Path</td><td class="path">' . JPATH_SITE . '</td></tr>';
    echo '</table>';
    echo '</div>';
    
    // ============================================
    // SECTION 2: Admin Component Files
    // ============================================
    echo '<h2>2. Admin Component Files</h2>';
    echo '<div class="section">';
    
    $adminBase = JPATH_ADMINISTRATOR . '/components/com_odoocontacts';
    $adminBaseAlt = $adminBase . '/admin'; // Check for wrong structure
    
    echo '<p><strong>Checking:</strong> <span class="path">' . $adminBase . '</span></p>';
    
    // Critical files to check
    $criticalFiles = [
        'src/Extension/OdooContactsComponent.php' => 'Extension Component Class',
        'services/provider.php' => 'Service Provider',
        'config.xml' => 'Component Config',
        'src/Controller/DisplayController.php' => 'Display Controller',
        'src/View/Dashboard/HtmlView.php' => 'Dashboard View',
        'tmpl/dashboard/default.php' => 'Dashboard Template',
    ];
    
    echo '<table>';
    echo '<tr><th>File</th><th>Status</th><th>Location</th></tr>';
    
    foreach ($criticalFiles as $file => $description) {
        $correctPath = $adminBase . '/' . $file;
        $wrongPath = $adminBaseAlt . '/' . $file;
        
        $found = false;
        $location = '';
        
        if (file_exists($correctPath)) {
            $found = true;
            $location = $correctPath;
            $success[] = "Admin file found: {$file}";
        } elseif (file_exists($wrongPath)) {
            $found = true;
            $location = $wrongPath . ' <span class="warning">(WRONG LOCATION!)</span>';
            $warnings[] = "File in wrong location: {$file} - should be in root, not admin/ subdirectory";
        }
        
        if ($found) {
            $size = filesize($location);
            $perms = substr(sprintf('%o', fileperms($location)), -4);
            echo '<tr>';
            echo '<td><strong>' . $description . '</strong><br><span class="path">' . $file . '</span></td>';
            echo '<td class="success">✓ Found</td>';
            echo '<td class="path">' . $location . '<br>Size: ' . number_format($size) . ' bytes | Perms: ' . $perms . '</td>';
            echo '</tr>';
        } else {
            echo '<tr>';
            echo '<td><strong>' . $description . '</strong><br><span class="path">' . $file . '</span></td>';
            echo '<td class="error">✗ Missing</td>';
            echo '<td class="path">Not found in either location</td>';
            echo '</tr>';
            $issues[] = "Missing admin file: {$file}";
        }
    }
    
    echo '</table>';
    
    // Check directory structure
    echo '<h3>Directory Structure Check</h3>';
    echo '<table>';
    echo '<tr><th>Directory</th><th>Status</th><th>Path</th></tr>';
    
    $dirs = [
        'src' => 'Source directory',
        'src/Extension' => 'Extension classes',
        'src/Controller' => 'Controllers',
        'src/View' => 'Views',
        'services' => 'Service providers',
        'tmpl' => 'Templates',
    ];
    
    foreach ($dirs as $dir => $desc) {
        $correctDir = $adminBase . '/' . $dir;
        $wrongDir = $adminBaseAlt . '/' . $dir;
        
        if (is_dir($correctDir)) {
            echo '<tr><td>' . $desc . '</td><td class="success">✓ Correct location</td><td class="path">' . $correctDir . '</td></tr>';
        } elseif (is_dir($wrongDir)) {
            echo '<tr><td>' . $desc . '</td><td class="warning">⚠ Wrong location</td><td class="path">' . $wrongDir . '</td></tr>';
            $warnings[] = "Directory in wrong location: {$dir}";
        } else {
            echo '<tr><td>' . $desc . '</td><td class="error">✗ Missing</td><td class="path">Not found</td></tr>';
            $issues[] = "Missing directory: {$dir}";
        }
    }
    
    echo '</table>';
    echo '</div>';
    
    // ============================================
    // SECTION 3: Site Component Files
    // ============================================
    echo '<h2>3. Site Component Files</h2>';
    echo '<div class="section">';
    
    $siteBase = JPATH_SITE . '/components/com_odoocontacts';
    echo '<p><strong>Checking:</strong> <span class="path">' . $siteBase . '</span></p>';
    
    $siteFiles = [
        'src/Controller/DisplayController.php' => 'Display Controller',
        'src/Helper/OdooHelper.php' => 'Odoo Helper',
        'src/Model/ContactsModel.php' => 'Contacts Model',
    ];
    
    echo '<table>';
    echo '<tr><th>File</th><th>Status</th><th>Location</th></tr>';
    
    foreach ($siteFiles as $file => $description) {
        $path = $siteBase . '/' . $file;
        if (file_exists($path)) {
            $size = filesize($path);
            echo '<tr>';
            echo '<td><strong>' . $description . '</strong><br><span class="path">' . $file . '</span></td>';
            echo '<td class="success">✓ Found</td>';
            echo '<td class="path">' . $path . '<br>Size: ' . number_format($size) . ' bytes</td>';
            echo '</tr>';
            $success[] = "Site file found: {$file}";
        } else {
            echo '<tr>';
            echo '<td><strong>' . $description . '</strong><br><span class="path">' . $file . '</span></td>';
            echo '<td class="error">✗ Missing</td>';
            echo '<td class="path">Not found</td>';
            echo '</tr>';
            $issues[] = "Missing site file: {$file}";
        }
    }
    
    echo '</table>';
    echo '</div>';
    
    // ============================================
    // SECTION 4: Database Check
    // ============================================
    echo '<h2>4. Database Registration</h2>';
    echo '<div class="section">';
    
    try {
        $db = Factory::getDbo();
        $prefix = $db->getPrefix();
        
        echo '<p><strong>Database Prefix:</strong> <span class="path">' . $prefix . '</span></p>';
        
        // Check extensions table
        echo '<h3>Extensions Table</h3>';
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_odoocontacts'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
        
        $db->setQuery($query);
        $extensions = $db->loadObjectList();
        
        echo '<table>';
        echo '<tr><th>Extension ID</th><th>Name</th><th>Enabled</th><th>Version</th></tr>';
        
        if (count($extensions) > 0) {
            foreach ($extensions as $ext) {
                $manifest = json_decode($ext->manifest_cache);
                $version = isset($manifest->version) ? $manifest->version : 'Unknown';
                $enabled = $ext->enabled ? '<span class="success">Yes</span>' : '<span class="error">No</span>';
                echo '<tr>';
                echo '<td>' . $ext->extension_id . '</td>';
                echo '<td>' . $ext->name . '</td>';
                echo '<td>' . $enabled . '</td>';
                echo '<td>' . $version . '</td>';
                echo '</tr>';
                $success[] = "Component registered in database (ID: {$ext->extension_id})";
            }
            
            if (count($extensions) > 1) {
                $warnings[] = "Multiple component entries found (" . count($extensions) . ") - may need cleanup";
            }
        } else {
            echo '<tr><td colspan="4" class="error">✗ Component not found in extensions table</td></tr>';
            $issues[] = "Component not registered in database";
        }
        
        echo '</table>';
        
        // Check menu items
        echo '<h3>Menu Items</h3>';
        if (count($extensions) > 0) {
            $extensionIds = array_column($extensions, 'extension_id');
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('component_id') . ' IN (' . implode(',', $extensionIds) . ')');
            
            $db->setQuery($query);
            $menus = $db->loadObjectList();
            
            echo '<table>';
            echo '<tr><th>Menu ID</th><th>Title</th><th>Alias</th><th>Published</th><th>Link</th></tr>';
            
            if (count($menus) > 0) {
                foreach ($menus as $menu) {
                    $published = $menu->published ? '<span class="success">Yes</span>' : '<span class="error">No</span>';
                    echo '<tr>';
                    echo '<td>' . $menu->id . '</td>';
                    echo '<td>' . $menu->title . '</td>';
                    echo '<td>' . $menu->alias . '</td>';
                    echo '<td>' . $published . '</td>';
                    echo '<td class="path">' . $menu->link . '</td>';
                    echo '</tr>';
                }
                $success[] = "Menu items found: " . count($menus);
            } else {
                echo '<tr><td colspan="5" class="warning">⚠ No menu items found</td></tr>';
                $warnings[] = "No menu items registered";
            }
            
            echo '</table>';
        }
        
        // Check assets
        echo '<h3>Assets Table</h3>';
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__assets'))
            ->where($db->quoteName('name') . ' LIKE ' . $db->quote('com_odoocontacts%'));
        
        $db->setQuery($query);
        $assets = $db->loadObjectList();
        
        echo '<table>';
        echo '<tr><th>Asset ID</th><th>Name</th><th>Title</th><th>Parent ID</th></tr>';
        
        if (count($assets) > 0) {
            foreach ($assets as $asset) {
                echo '<tr>';
                echo '<td>' . $asset->id . '</td>';
                echo '<td>' . $asset->name . '</td>';
                echo '<td>' . $asset->title . '</td>';
                echo '<td>' . $asset->parent_id . '</td>';
                echo '</tr>';
            }
            $success[] = "Assets found: " . count($assets);
        } else {
            echo '<tr><td colspan="4" class="warning">⚠ No assets found</td></tr>';
            $warnings[] = "No assets registered";
        }
        
        echo '</table>';
        
    } catch (Exception $e) {
        echo '<p class="error">Database Error: ' . $e->getMessage() . '</p>';
        $issues[] = "Database connection error: " . $e->getMessage();
    }
    
    echo '</div>';
    
    // ============================================
    // SECTION 5: Summary
    // ============================================
    echo '<h2>5. Diagnostic Summary</h2>';
    echo '<div class="summary">';
    
    echo '<h3>✅ Success (' . count($success) . ')</h3>';
    if (count($success) > 0) {
        echo '<ul>';
        foreach ($success as $item) {
            echo '<li class="success">' . $item . '</li>';
        }
        echo '</ul>';
    }
    
    echo '<h3>⚠️ Warnings (' . count($warnings) . ')</h3>';
    if (count($warnings) > 0) {
        echo '<ul>';
        foreach ($warnings as $item) {
            echo '<li class="warning">' . $item . '</li>';
        }
        echo '</ul>';
    }
    
    echo '<h3>❌ Issues (' . count($issues) . ')</h3>';
    if (count($issues) > 0) {
        echo '<ul>';
        foreach ($issues as $item) {
            echo '<li class="error">' . $item . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="success">No critical issues found!</p>';
    }
    
    echo '</div>';
    
    // ============================================
    // SECTION 6: Recommendations
    // ============================================
    if (count($issues) > 0 || count($warnings) > 0) {
        echo '<h2>6. Recommendations</h2>';
        echo '<div class="section">';
        echo '<ul>';
        
        if (count($warnings) > 0 && strpos(implode(' ', $warnings), 'wrong location') !== false) {
            echo '<li class="warning"><strong>Fix File Structure:</strong> Run <code>fix_file_structure.sh</code> to move files from admin/ subdirectory to root</li>';
        }
        
        if (count($issues) > 0 && strpos(implode(' ', $issues), 'not registered') !== false) {
            echo '<li class="error"><strong>Register Component:</strong> Run <code>manual_install_com_odoocontacts.sql</code> in phpMyAdmin</li>';
        }
        
        if (count($extensions) > 1) {
            echo '<li class="warning"><strong>Clean Duplicates:</strong> Run <code>cleanup_com_odoocontacts.sql</code> first, then re-register</li>';
        }
        
        echo '<li class="info"><strong>Clear Cache:</strong> Go to System → Clear Cache after making changes</li>';
        echo '</ul>';
        echo '</div>';
    }
    ?>
    
    <hr>
    <p class="info"><small>Diagnostic script completed at <?php echo date('Y-m-d H:i:s'); ?></small></p>
</div>
</body>
</html>

