<?php
/**
 * @package     Grimpsa.Administrator
 * @subpackage  com_odoocontacts
 *
 * @copyright   Copyright (C) 2025 Grimpsa. All rights reserved.
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

/**
 * Installation script for com_odoocontacts component
 * 
 * This script logs every installation step, traps all exceptions,
 * writes them to a custom log file, and rethrows errors so Joomla
 * can display the real cause of installation failures.
 */
class Com_OdoocontactsInstallerScript extends InstallerScript
{
    /**
     * Custom log file path
     *
     * @var    string
     */
    private $logFile;

    /**
     * Minimum Joomla version required
     *
     * @var    string
     */
    protected $minimumJoomla = '5.0.0';

    /**
     * Minimum PHP version required
     *
     * @var    string
     */
    protected $minimumPhp = '8.0.0';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logFile = JPATH_ADMINISTRATOR . '/logs/com_odoocontacts_install.log';
        $this->setupLogging();
    }

    /**
     * Set up custom logging
     *
     * @return  void
     */
    private function setupLogging()
    {
        try {
            // Ensure log directory exists
            $logDir = dirname($this->logFile);
            if (!is_dir($logDir)) {
                Folder::create($logDir);
            }

            // Clear previous log for fresh installation
            if (file_exists($this->logFile)) {
                File::delete($this->logFile);
            }
        } catch (Exception $e) {
            // If we can't set up logging, continue anyway
        }
    }

    /**
     * Write log message
     *
     * @param   string  $message   The log message
     * @param   string  $level      Log level (INFO, ERROR, WARNING, DEBUG)
     * @param   array   $context    Additional context data
     *
     * @return  void
     */
    private function writeLog($message, $level = 'INFO', $context = [])
    {
        try {
            $timestamp = date('Y-m-d H:i:s');
            $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
            $logEntry = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
            
            file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
            
            // Also log to Joomla's log system
            Log::add($message, constant('Log::' . $level), 'com_odoocontacts');
        } catch (Exception $e) {
            // Silently fail if logging fails
        }
    }

    /**
     * Method to run before an install/update/uninstall method
     *
     * @param   string  $type    The action being performed (install|update|uninstall)
     * @param   object  $parent  The class calling this method
     *
     * @return  boolean  True on success
     */
    public function preflight($type, $parent)
    {
        $this->writeLog("=== PREFLIGHT START: {$type} ===");
        $this->writeLog("Component: com_odoocontacts");
        $this->writeLog("Joomla Version: " . JVERSION);
        $this->writeLog("PHP Version: " . PHP_VERSION);
        $this->writeLog("Installation Type: {$type}");

        try {
            // Check Joomla version
            $this->writeLog("Checking Joomla version requirements...");
            if (version_compare(JVERSION, $this->minimumJoomla, 'lt')) {
                $error = "Joomla {$this->minimumJoomla} or higher is required. You are running " . JVERSION;
                $this->writeLog($error, 'ERROR');
                throw new RuntimeException($error);
            }
            $this->writeLog("Joomla version check passed");

            // Check PHP version
            $this->writeLog("Checking PHP version requirements...");
            if (version_compare(PHP_VERSION, $this->minimumPhp, 'lt')) {
                $error = "PHP {$this->minimumPhp} or higher is required. You are running " . PHP_VERSION;
                $this->writeLog($error, 'ERROR');
                throw new RuntimeException($error);
            }
            $this->writeLog("PHP version check passed");

            // Check file permissions
            $this->writeLog("Checking file permissions...");
            $adminPath = JPATH_ADMINISTRATOR . '/components/com_odoocontacts';
            $sitePath = JPATH_SITE . '/components/com_odoocontacts';
            
            if ($type === 'install' || $type === 'update') {
                $paths = [
                    JPATH_ADMINISTRATOR . '/components',
                    JPATH_SITE . '/components',
                    JPATH_ADMINISTRATOR . '/components/com_odoocontacts',
                    JPATH_SITE . '/components/com_odoocontacts'
                ];
                
                foreach ($paths as $path) {
                    if (file_exists($path) && !is_writable($path)) {
                        $error = "Directory is not writable: {$path}";
                        $this->writeLog($error, 'ERROR');
                        throw new RuntimeException($error);
                    }
                }
            }
            $this->writeLog("File permissions check passed");

            // Check database connection
            $this->writeLog("Checking database connection...");
            try {
                $db = Factory::getDbo();
                $db->getVersion();
                $this->writeLog("Database connection successful");
            } catch (Exception $e) {
                $error = "Database connection failed: " . $e->getMessage();
                $this->writeLog($error, 'ERROR', ['exception' => $e->getTraceAsString()]);
                throw new RuntimeException($error, 0, $e);
            }

            $this->writeLog("=== PREFLIGHT COMPLETED SUCCESSFULLY ===");
            return true;

        } catch (Exception $e) {
            $this->writeLog("PREFLIGHT FAILED: " . $e->getMessage(), 'ERROR', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            // Re-throw so Joomla can display the error
            throw $e;
        }
    }

    /**
     * Method to install the component
     *
     * @param   object  $parent  The class calling this method
     *
     * @return  boolean  True on success
     */
    public function install($parent)
    {
        $this->writeLog("=== INSTALL START ===");

        try {
            // Get manifest
            $this->writeLog("Reading manifest file...");
            $manifest = $parent->getManifest();
            $version = (string) $manifest->version;
            $this->writeLog("Component version: {$version}");

            // Create necessary directories
            $this->writeLog("Creating component directories...");
            $adminPath = JPATH_ADMINISTRATOR . '/components/com_odoocontacts';
            $sitePath = JPATH_SITE . '/components/com_odoocontacts';
            
            if (!is_dir($adminPath)) {
                Folder::create($adminPath);
                $this->writeLog("Created admin directory: {$adminPath}");
            }
            
            if (!is_dir($sitePath)) {
                Folder::create($sitePath);
                $this->writeLog("Created site directory: {$sitePath}");
            }

            // Register component in database
            $this->writeLog("Registering component in database...");
            $this->registerComponent($version);
            $this->writeLog("Component registered successfully");

            // Set default component parameters
            $this->writeLog("Setting default component parameters...");
            $this->setDefaultParameters();
            $this->writeLog("Default parameters set");

            $this->writeLog("=== INSTALL COMPLETED SUCCESSFULLY ===");
            return true;

        } catch (Exception $e) {
            $this->writeLog("INSTALL FAILED: " . $e->getMessage(), 'ERROR', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            // Re-throw so Joomla can display the error
            throw $e;
        }
    }

    /**
     * Method to update the component
     *
     * @param   object  $parent  The class calling this method
     *
     * @return  boolean  True on success
     */
    public function update($parent)
    {
        $this->writeLog("=== UPDATE START ===");

        try {
            $manifest = $parent->getManifest();
            $newVersion = (string) $manifest->version;
            $this->writeLog("Updating to version: {$newVersion}");

            // Get current version
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('manifest_cache'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_odoocontacts'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
            
            $db->setQuery($query);
            $manifestCache = $db->loadResult();
            
            $oldVersion = '0.0.0';
            if ($manifestCache) {
                $manifestData = json_decode($manifestCache, true);
                $oldVersion = $manifestData['version'] ?? '0.0.0';
            }
            
            $this->writeLog("Current version: {$oldVersion}");
            $this->writeLog("New version: {$newVersion}");

            // Update component registration
            $this->writeLog("Updating component registration...");
            $this->registerComponent($newVersion);
            $this->writeLog("Component registration updated");

            // Clear cache
            $this->writeLog("Clearing Joomla cache...");
            $this->clearCache();
            $this->writeLog("Cache cleared");

            $this->writeLog("=== UPDATE COMPLETED SUCCESSFULLY ===");
            return true;

        } catch (Exception $e) {
            $this->writeLog("UPDATE FAILED: " . $e->getMessage(), 'ERROR', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            // Re-throw so Joomla can display the error
            throw $e;
        }
    }

    /**
     * Method to uninstall the component
     *
     * @param   object  $parent  The class calling this method
     *
     * @return  boolean  True on success
     */
    public function uninstall($parent)
    {
        $this->writeLog("=== UNINSTALL START ===");

        try {
            $this->writeLog("Removing component from database...");
            
            // Remove component entry
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_odoocontacts'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
            
            $db->setQuery($query);
            $db->execute();
            $this->writeLog("Component entry removed from database");

            // Remove menu items
            $this->writeLog("Removing menu items...");
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__menu'))
                ->where($db->quoteName('component_id') . ' IN (' .
                    $db->getQuery(true)
                        ->select($db->quoteName('extension_id'))
                        ->from($db->quoteName('#__extensions'))
                        ->where($db->quoteName('element') . ' = ' . $db->quote('com_odoocontacts'))
                . ')');
            
            $db->setQuery($query);
            $db->execute();
            $this->writeLog("Menu items removed");

            // Clear cache
            $this->writeLog("Clearing cache...");
            $this->clearCache();
            $this->writeLog("Cache cleared");

            $this->writeLog("=== UNINSTALL COMPLETED SUCCESSFULLY ===");
            return true;

        } catch (Exception $e) {
            $this->writeLog("UNINSTALL FAILED: " . $e->getMessage(), 'ERROR', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            // Re-throw so Joomla can display the error
            throw $e;
        }
    }

    /**
     * Method to run after an install/update/uninstall method
     *
     * @param   string  $type    The action being performed (install|update|uninstall)
     * @param   object  $parent  The class calling this method
     *
     * @return  boolean  True on success
     */
    public function postflight($type, $parent)
    {
        $this->writeLog("=== POSTFLIGHT START: {$type} ===");

        try {
            // Clear cache
            $this->writeLog("Clearing Joomla cache...");
            $this->clearCache();
            $this->writeLog("Cache cleared");

            // Clear opcache if available
            if (function_exists('opcache_reset')) {
                $this->writeLog("Clearing OPcache...");
                opcache_reset();
                $this->writeLog("OPcache cleared");
            }

            // Verify installation
            if ($type === 'install' || $type === 'update') {
                $this->writeLog("Verifying installation...");
                $this->verifyInstallation();
                $this->writeLog("Installation verified");
            }

            $this->writeLog("=== POSTFLIGHT COMPLETED SUCCESSFULLY ===");
            $this->writeLog("Installation log saved to: {$this->logFile}");
            return true;

        } catch (Exception $e) {
            $this->writeLog("POSTFLIGHT FAILED: " . $e->getMessage(), 'ERROR', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            // Re-throw so Joomla can display the error
            throw $e;
        }
    }

    /**
     * Register component in database
     *
     * @param   string  $version  Component version
     *
     * @return  void
     * @throws  Exception
     */
    private function registerComponent($version)
    {
        try {
            $db = Factory::getDbo();
            $manifest = [
                'name' => 'com_odoocontacts',
                'type' => 'component',
                'creationDate' => '2025-01-27',
                'author' => 'Grimpsa',
                'copyright' => 'Copyright (C) 2025 Grimpsa. All rights reserved.',
                'authorEmail' => 'admin@grimpsa.com',
                'authorUrl' => 'https://grimpsa.com',
                'version' => $version,
                'description' => 'Odoo Contacts Management System',
                'group' => ''
            ];

            // Check if component already exists
            $query = $db->getQuery(true)
                ->select($db->quoteName('extension_id'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_odoocontacts'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
            
            $db->setQuery($query);
            $extensionId = $db->loadResult();

            if ($extensionId) {
                // Update existing entry
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__extensions'))
                    ->set($db->quoteName('manifest_cache') . ' = ' . $db->quote(json_encode($manifest)))
                    ->set($db->quoteName('params') . ' = ' . $db->quote('{}'))
                    ->where($db->quoteName('extension_id') . ' = ' . (int) $extensionId);
                
                $db->setQuery($query);
                $db->execute();
                $this->writeLog("Component entry updated in database (ID: {$extensionId})");
            } else {
                // Insert new entry
                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__extensions'))
                    ->set($db->quoteName('name') . ' = ' . $db->quote('com_odoocontacts'))
                    ->set($db->quoteName('type') . ' = ' . $db->quote('component'))
                    ->set($db->quoteName('element') . ' = ' . $db->quote('com_odoocontacts'))
                    ->set($db->quoteName('folder') . ' = ' . $db->quote(''))
                    ->set($db->quoteName('client_id') . ' = 1')
                    ->set($db->quoteName('enabled') . ' = 1')
                    ->set($db->quoteName('access') . ' = 1')
                    ->set($db->quoteName('protected') . ' = 0')
                    ->set($db->quoteName('manifest_cache') . ' = ' . $db->quote(json_encode($manifest)))
                    ->set($db->quoteName('params') . ' = ' . $db->quote('{}'))
                    ->set($db->quoteName('custom_data') . ' = ' . $db->quote(''))
                    ->set($db->quoteName('system_data') . ' = ' . $db->quote(''))
                    ->set($db->quoteName('checked_out') . ' = 0')
                    ->set($db->quoteName('checked_out_time') . ' = ' . $db->quote($db->getNullDate()))
                    ->set($db->quoteName('ordering') . ' = 0')
                    ->set($db->quoteName('state') . ' = 0');
                
                $db->setQuery($query);
                $db->execute();
                $extensionId = $db->insertid();
                $this->writeLog("Component entry created in database (ID: {$extensionId})");
            }

        } catch (Exception $e) {
            $this->writeLog("Failed to register component: " . $e->getMessage(), 'ERROR', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Set default component parameters
     *
     * @return  void
     * @throws  Exception
     */
    private function setDefaultParameters()
    {
        try {
            $db = Factory::getDbo();
            $defaultParams = [
                'odoo_url' => 'https://grupoimpre.odoo.com/xmlrpc/2/object',
                'odoo_database' => 'grupoimpre',
                'odoo_user_id' => '2',
                'odoo_api_key' => '',
                'contacts_per_page' => '20',
                'enable_debug' => '0',
                'ot_destination_url' => 'https://grimpsa_webserver.grantsolutions.cc/index.php/orden-de-trabajo'
            ];

            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_odoocontacts'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
            
            $db->setQuery($query);
            $existingParams = $db->loadResult();
            
            if ($existingParams) {
                $params = json_decode($existingParams, true);
                if (!is_array($params)) {
                    $params = [];
                }
                // Merge with defaults, keeping existing values
                $params = array_merge($defaultParams, $params);
            } else {
                $params = $defaultParams;
            }

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_odoocontacts'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
            
            $db->setQuery($query);
            $db->execute();
            $this->writeLog("Default parameters set");

        } catch (Exception $e) {
            $this->writeLog("Failed to set default parameters: " . $e->getMessage(), 'ERROR', [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            // Don't throw - parameters are not critical for installation
        }
    }

    /**
     * Clear Joomla cache
     *
     * @return  void
     */
    private function clearCache()
    {
        try {
            $cache = Factory::getCache();
            $cache->clean('com_odoocontacts');
            $cache->clean('_system');
            
            // Also clear cache folders
            $cacheFolders = [
                JPATH_CACHE,
                JPATH_ADMINISTRATOR . '/cache'
            ];
            
            foreach ($cacheFolders as $folder) {
                if (is_dir($folder)) {
                    $files = Folder::files($folder, '.', false, true);
                    foreach ($files as $file) {
                        if (basename($file) !== 'index.html') {
                            File::delete($file);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->writeLog("Cache clearing warning: " . $e->getMessage(), 'WARNING');
            // Don't throw - cache clearing is not critical
        }
    }

    /**
     * Verify installation
     *
     * @return  void
     * @throws  Exception
     */
    private function verifyInstallation()
    {
        try {
            // Check if component is registered
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('com_odoocontacts'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
            
            $db->setQuery($query);
            $count = $db->loadResult();
            
            if ($count == 0) {
                throw new Exception('Component not found in database after installation');
            }

            // Check if admin files exist
            $adminPath = JPATH_ADMINISTRATOR . '/components/com_odoocontacts';
            if (!is_dir($adminPath)) {
                throw new Exception('Admin component directory not found: ' . $adminPath);
            }

            // Check if site files exist
            $sitePath = JPATH_SITE . '/components/com_odoocontacts';
            if (!is_dir($sitePath)) {
                throw new Exception('Site component directory not found: ' . $sitePath);
            }

            // Check if key files exist
            $keyFiles = [
                $adminPath . '/services/provider.php',
                $adminPath . '/src/Extension/OdooContactsComponent.php',
                $sitePath . '/src/Helper/OdooHelper.php'
            ];

            foreach ($keyFiles as $file) {
                if (!file_exists($file)) {
                    $this->writeLog("Warning: Key file not found: {$file}", 'WARNING');
                }
            }

            $this->writeLog("Installation verification passed");

        } catch (Exception $e) {
            $this->writeLog("Installation verification failed: " . $e->getMessage(), 'ERROR', [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }
}

