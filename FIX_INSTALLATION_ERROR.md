# Fix Installation Error: Copy Failed

## Error Message
```
Joomla\Filesystem\File::copy([ROOT][TMP]/install_69664a76ddbb2/admin/script.php, 
[ROOT]/administrator/components/com_odoocontacts/admin/script.php): Copy failed
```

## What This Means
The installation is **working** - files are being extracted from the zip, but Joomla **cannot copy** them to the final location because of **permissions**.

## Root Cause
The web server user doesn't have **write permission** to:
- `/var/www/grimpsa_webserver/administrator/components/` (parent directory)
- `/var/www/grimpsa_webserver/administrator/components/com_odoocontacts/` (component directory - may not exist yet)

## Solution

### Step 1: Check Current Permissions
```bash
ls -ld /var/www/grimpsa_webserver/administrator/components
```

### Step 2: Fix Permissions on Components Directory
```bash
# Make sure the directory is writable
sudo chmod 755 /var/www/grimpsa_webserver/administrator/components

# Set correct owner (replace www-data with your web server user)
sudo chown www-data:www-data /var/www/grimpsa_webserver/administrator/components
```

### Step 3: Check if Component Directory Exists
```bash
ls -ld /var/www/grimpsa_webserver/administrator/components/com_odoocontacts
```

If it doesn't exist, create it:
```bash
sudo mkdir -p /var/www/grimpsa_webserver/administrator/components/com_odoocontacts
sudo chmod 755 /var/www/grimpsa_webserver/administrator/components/com_odoocontacts
sudo chown www-data:www-data /var/www/grimpsa_webserver/administrator/components/com_odoocontacts
```

### Step 4: Also Check Site Components Directory
```bash
# Check
ls -ld /var/www/grimpsa_webserver/components

# Fix if needed
sudo chmod 755 /var/www/grimpsa_webserver/components
sudo chown www-data:www-data /var/www/grimpsa_webserver/components
```

### Step 5: Find Your Web Server User
```bash
# For Apache
ps aux | grep apache | head -1 | awk '{print $1}'

# Or check Apache config
grep -i "^User" /etc/apache2/apache2.conf
# or
grep -i "^User" /etc/httpd/httpd.conf
```

Common users:
- `www-data` (Debian/Ubuntu)
- `apache` (CentOS/RHEL)
- `httpd` (some systems)

## Complete Fix Script

Replace `www-data` with your actual web server user:

```bash
#!/bin/bash
# Fix permissions for Joomla component installation

WEB_USER="www-data"  # Change this to your web server user
BASE_DIR="/var/www/grimpsa_webserver"

# Fix administrator/components
sudo chmod 755 "$BASE_DIR/administrator/components"
sudo chown $WEB_USER:$WEB_USER "$BASE_DIR/administrator/components"

# Fix components
sudo chmod 755 "$BASE_DIR/components"
sudo chown $WEB_USER:$WEB_USER "$BASE_DIR/components"

# Create component directory if it doesn't exist
sudo mkdir -p "$BASE_DIR/administrator/components/com_odoocontacts"
sudo chmod 755 "$BASE_DIR/administrator/components/com_odoocontacts"
sudo chown $WEB_USER:$WEB_USER "$BASE_DIR/administrator/components/com_odoocontacts"

# Also create site component directory
sudo mkdir -p "$BASE_DIR/components/com_odoocontacts"
sudo chmod 755 "$BASE_DIR/components/com_odoocontacts"
sudo chown $WEB_USER:$WEB_USER "$BASE_DIR/components/com_odoocontacts"

echo "Permissions fixed. Try installing again."
```

## After Fixing Permissions

1. **Try installing the component again**
2. The installation should now succeed
3. Files should be copied to:
   - `/var/www/grimpsa_webserver/administrator/components/com_odoocontacts/`
   - `/var/www/grimpsa_webserver/components/com_odoocontacts/`

## Verify Installation

After installation, check:
```bash
# Check if files were installed
ls -la /var/www/grimpsa_webserver/administrator/components/com_odoocontacts/admin/script.php

# Should show the file exists and is readable
```

## If Still Failing

If it still fails after fixing permissions:

1. **Check SELinux** (if enabled):
   ```bash
   getenforce
   # If "Enforcing", you may need to set SELinux context
   sudo chcon -R -t httpd_sys_rw_content_t /var/www/grimpsa_webserver/administrator/components
   ```

2. **Check disk space**:
   ```bash
   df -h /var/www/grimpsa_webserver
   ```

3. **Check for any existing partial installation**:
   ```bash
   ls -la /var/www/grimpsa_webserver/administrator/components/com_odoocontacts
   # If exists, remove it and try again
   sudo rm -rf /var/www/grimpsa_webserver/administrator/components/com_odoocontacts
   ```

