# Installer Script Debugging Guide

## Problem: No Log File Created

If `com_odoocontacts_install.log` is not being created, it means the installer script might not be executing at all.

## Check These Locations for Logs

The installer script now writes to **multiple locations** as a fallback:

1. **Primary location:**
   - `/administrator/logs/com_odoocontacts_install.log`

2. **Fallback locations:**
   - `/com_odoocontacts_install.log` (Joomla root)
   - `/tmp/com_odoocontacts_install.log` (system temp)
   - PHP error log (check Apache/PHP error logs)

## Verify Script is Being Called

### Method 1: Check PHP Error Log
The script writes to PHP error log immediately when loaded. Check:
- Apache error log
- PHP error log
- Look for: `com_odoocontacts installer script loaded`

### Method 2: Check if Script File Exists
After installation attempt, check if the script file was extracted:
- `/administrator/components/com_odoocontacts/admin/script.php`

### Method 3: Enable Joomla Debug Mode
1. Go to **System → Global Configuration → System**
2. Enable **Debug System**: **Yes**
3. Try installing again
4. Check for any error messages on screen

## Common Issues

### Issue 1: Script Not Loaded
**Symptom:** No log file created anywhere
**Cause:** Joomla might not be finding/loading the script
**Solution:** 
- Verify `script.php` is in the manifest: `<filename>script.php</filename>`
- Check file is in correct location: `admin/script.php`

### Issue 2: Class Name Mismatch
**Symptom:** Script loads but methods not called
**Cause:** Class name doesn't match Joomla's expectations
**Current class name:** `Com_OdoocontactsInstallerScript`
**Expected format:** `Com_ComponentnameInstallerScript` (where Componentname = odoocontacts)

### Issue 3: Script Fails Before Logging
**Symptom:** Script loads but no logs
**Cause:** Fatal error before logging can occur
**Solution:** Check PHP error logs for fatal errors

## Manual Test

To verify the script works, you can manually test it:

1. **Extract the zip file**
2. **Upload `admin/script.php` to your server**
3. **Create a test file** `test_installer.php` in Joomla root:
```php
<?php
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);
require_once JPATH_BASE . '/administrator/components/com_odoocontacts/admin/script.php';

$script = new Com_OdoocontactsInstallerScript();
echo "Script loaded successfully!";
```

4. **Run:** `your-site.com/test_installer.php`
5. **Check for log files** in all locations
6. **DELETE test_installer.php after testing!**

## Next Steps

If no log file appears in ANY location:
1. The script is not being executed by Joomla
2. Check Joomla error logs
3. Check Apache/PHP error logs
4. Verify the manifest includes the script file
5. Try manual installation method instead

