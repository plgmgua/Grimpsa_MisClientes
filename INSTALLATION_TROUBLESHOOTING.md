# Installation Troubleshooting Guide

## Problem: Component Installation Fails for All Versions

If even previously working versions (like v1.1.8) fail to install, this indicates a **system-level issue**, not a code problem.

## Step-by-Step Solution

### Step 1: Clean Database (CRITICAL)

1. Access your database (phpMyAdmin, MySQL client, etc.)
2. Run the SQL script: `cleanup_com_odoocontacts.sql`
   - Replace `#__` with your Joomla table prefix (usually `jos_` or similar)
   - This removes all leftover component entries

### Step 2: Clear All Joomla Cache

1. Go to **System → Clear Cache**
2. Click **Clear All**
3. Or manually delete:
   - `/cache/` folder contents
   - `/administrator/cache/` folder contents
   - `/tmp/` folder contents (if exists)

### Step 3: Check File Permissions

Ensure these directories are writable (755 or 775):
- `/administrator/components/`
- `/components/`
- `/cache/`
- `/administrator/cache/`

### Step 4: Manual Installation (Bypass Installer)

If the installer still fails, use manual installation:

1. **Extract the zip file** to your computer
2. **Upload via FTP/SFTP:**
   - Upload `com_odoocontacts` folder to `/administrator/components/`
   - Upload `com_odoocontacts` folder to `/components/`
3. **Discover and Install:**
   - Go to **Extensions → Manage → Discover**
   - Click **Discover** button
   - Find "Odoo Contacts" in the list
   - Click the checkbox and click **Install**

### Step 5: Verify Installation

1. Go to **Components → Odoo Contacts**
2. If it appears, installation was successful
3. If not, check for PHP errors in browser console (F12)

## Common Causes

1. **Database Corruption**: Leftover entries blocking installation
2. **Cache Issues**: Corrupted cache preventing installation
3. **File Permissions**: Server can't write files
4. **PHP Errors**: Check PHP error log on server
5. **Joomla Version**: Ensure you're using Joomla 5.0+

## Alternative: Fresh Component Entry

If manual installation doesn't work, you can manually insert the component entry:

```sql
INSERT INTO `#__extensions` 
(`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) 
VALUES 
('com_odoocontacts', 'component', 'com_odoocontacts', '', 1, 1, 1, 0, '{"name":"com_odoocontacts","type":"component","creationDate":"2025-01-27","author":"Grimpsa","copyright":"Copyright (C) 2025 Grimpsa","authorEmail":"admin@grimpsa.com","authorUrl":"https://grimpsa.com","version":"1.2.2","description":"Odoo Contacts Management System","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0);
```

Then use the Discover method above.

## Still Not Working?

1. Check PHP error logs on your server
2. Check Joomla error logs in `/administrator/logs/`
3. Try installing a different component to verify the installer works
4. Contact your hosting provider about PHP/MySQL issues

