# Files Not Extracted - Troubleshooting Guide

## Problem Identified
The test script shows that **no component files are being extracted** during installation. This means the installation is failing **before** file extraction happens.

## Root Cause Analysis

If `/administrator/components/com_odoocontacts/admin/script.php` doesn't exist after installation, it means:

1. **Installation fails before extraction** - Most likely cause
2. **Database errors** - Component can't be registered
3. **Manifest XML errors** - Joomla rejects the package
4. **Permissions issues** - Can't write to directories
5. **Zip file corruption** - File is damaged

## Diagnostic Steps

### Step 1: Check Installation Error Message
When you try to install:
- What **exact error message** appears?
- Does it say "Error installing component" or something more specific?
- Is there any error text at all, or does it just fail silently?

### Step 2: Enable Joomla Debug Mode
1. Edit `configuration.php` via FTP/file manager
2. Find: `public $debug = false;`
3. Change to: `public $debug = true;`
4. Save and try installing again
5. **Copy the full error message** that appears

### Step 3: Check Database
Run this SQL in phpMyAdmin:
```sql
-- Check if component exists in extensions table
SELECT * FROM `joomla_extensions` 
WHERE `element` = 'com_odoocontacts' 
AND `type` = 'component';

-- Check for any installation errors in extensions table
SELECT * FROM `joomla_extensions` 
WHERE `element` LIKE '%odoocontacts%';
```

### Step 4: Check File Permissions
**Your Joomla path:** `/var/www/grimpsa_webserver`

Via SSH or file manager, check these directories are **writable**:
- `/var/www/grimpsa_webserver/tmp` (Joomla temp directory)
- `/var/www/grimpsa_webserver/administrator/components`
- `/var/www/grimpsa_webserver/components`
- `/var/www/grimpsa_webserver/administrator/logs`

**Quick check via SSH:**
```bash
ls -ld /var/www/grimpsa_webserver/tmp
ls -ld /var/www/grimpsa_webserver/administrator/components
ls -ld /var/www/grimpsa_webserver/components
ls -ld /var/www/grimpsa_webserver/administrator/logs
```

All should show permissions like `drwxrwxr-x` (755) or `drwxrwxrwx` (777) and be owned by your web server user (usually `www-data` or `apache`).

**See `CHECK_PERMISSIONS.md` for detailed permission checking guide.**

### Step 5: Check Joomla Logs
1. Go to **System → System Information → System Information**
2. Note the **Path to Log Folder**
3. Check that folder for files with today's date
4. Look for any errors related to installation

### Step 6: Check PHP Error Log
1. Go to **System → System Information → PHP Information**
2. Search for "error_log"
3. Check that log file for PHP errors during installation

### Step 7: Manual Zip Extraction Test
1. Download the zip file to your computer
2. Extract it manually
3. Verify the structure:
   - `com_odoocontacts/odoocontacts.xml` exists
   - `com_odoocontacts/admin/script.php` exists
   - All folders are present

### Step 8: Check Zip File Integrity
Try opening the zip file on your computer:
- Does it open without errors?
- Can you see all files inside?
- Is `odoocontacts.xml` at the root level?

## Common Solutions

### Solution 1: Clean Database First
Run the cleanup SQL script (`cleanup_com_odoocontacts.sql`) to remove any partial installations.

### Solution 2: Check Manifest XML
The manifest must be valid XML. We've verified it's valid, but check:
- No special characters in field values
- All tags properly closed
- UTF-8 encoding

### Solution 3: Try Manual Installation
Instead of using Joomla's installer:
1. Extract the zip file on your computer
2. Upload the entire `com_odoocontacts` folder to `/administrator/components/`
3. Upload the `com_odoocontacts` folder to `/components/`
4. Run the database registration manually (this is complex, not recommended)

### Solution 4: Check PHP Settings
In **System → System Information → PHP Information**, check:
- `upload_max_filesize` - Should be at least 2MB
- `post_max_size` - Should be at least 2MB
- `memory_limit` - Should be at least 128MB
- `max_execution_time` - Should be at least 30 seconds

### Solution 5: Check Server Logs
If you have access to server logs:
- Check Apache error log
- Check PHP error log
- Look for errors during installation attempt

## What Information We Need

Please provide:
1. **Exact error message** when installation fails
2. **Joomla version** (System → System Information)
3. **PHP version** (System → System Information → PHP Information)
4. **Result of Step 3** (database query results)
5. **Result of Step 5** (any log files found)
6. **File permissions** for `/tmp` and `/administrator/components`

## Next Steps

Once we have the error message from debug mode, we can identify the exact cause and fix it.

