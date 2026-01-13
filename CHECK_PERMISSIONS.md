# File Permissions Check Guide

## Your Joomla Installation Path
**Base Directory:** `/var/www/grimpsa_webserver`

## Directories to Check

### 1. Joomla Temp Directory
**Path:** `/var/www/grimpsa_webserver/tmp`

**Check:**
```bash
ls -ld /var/www/grimpsa_webserver/tmp
```

**Should show:**
- Owner: Usually `www-data` or `apache` or your web server user
- Permissions: `drwxrwxr-x` or `drwxrwxrwx` (755 or 777)
- Must be **writable** by web server

**Fix if needed:**
```bash
sudo chmod 755 /var/www/grimpsa_webserver/tmp
sudo chown www-data:www-data /var/www/grimpsa_webserver/tmp
```
(Replace `www-data` with your web server user if different)

### 2. Administrator Components Directory
**Path:** `/var/www/grimpsa_webserver/administrator/components`

**Check:**
```bash
ls -ld /var/www/grimpsa_webserver/administrator/components
```

**Should show:**
- Permissions: `drwxr-xr-x` or `drwxrwxr-x` (755 or 775)
- Must be **writable** by web server

**Fix if needed:**
```bash
sudo chmod 755 /var/www/grimpsa_webserver/administrator/components
sudo chown www-data:www-data /var/www/grimpsa_webserver/administrator/components
```

### 3. Site Components Directory
**Path:** `/var/www/grimpsa_webserver/components`

**Check:**
```bash
ls -ld /var/www/grimpsa_webserver/components
```

**Should show:**
- Permissions: `drwxr-xr-x` or `drwxrwxr-x` (755 or 775)
- Must be **writable** by web server

**Fix if needed:**
```bash
sudo chmod 755 /var/www/grimpsa_webserver/components
sudo chown www-data:www-data /var/www/grimpsa_webserver/components
```

### 4. Administrator Logs Directory
**Path:** `/var/www/grimpsa_webserver/administrator/logs`

**Check:**
```bash
ls -ld /var/www/grimpsa_webserver/administrator/logs
```

**Should show:**
- Permissions: `drwxrwxr-x` or `drwxrwxrwx` (775 or 777)
- Must be **writable** by web server

**Fix if needed:**
```bash
sudo chmod 775 /var/www/grimpsa_webserver/administrator/logs
sudo chown www-data:www-data /var/www/grimpsa_webserver/administrator/logs
```

### 5. Root Directory (for fallback logs)
**Path:** `/var/www/grimpsa_webserver`

**Check:**
```bash
ls -ld /var/www/grimpsa_webserver
```

**Should show:**
- Permissions: `drwxr-xr-x` (755)
- Should be **writable** by web server (at least for logs)

## Quick Check Script

Create a file `check_permissions.php` in your Joomla root and run it:

```php
<?php
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);

$base = '/var/www/grimpsa_webserver';
$dirs = [
    $base . '/tmp',
    $base . '/administrator/components',
    $base . '/components',
    $base . '/administrator/logs',
    $base
];

echo "<h1>Permission Check</h1><pre>";
foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $writable = is_writable($dir) ? 'YES' : 'NO';
        $owner = posix_getpwuid(fileowner($dir));
        echo "{$dir}\n";
        echo "  Exists: YES\n";
        echo "  Permissions: {$perms}\n";
        echo "  Writable: {$writable}\n";
        echo "  Owner: {$owner['name']}\n";
    } else {
        echo "{$dir}\n";
        echo "  Exists: NO\n";
    }
    echo "\n";
}
echo "</pre>";
```

## Via Joomla Admin Panel

1. Go to **System → System Information → Folder Permissions**
2. Look for these folders and check if they show **"Writable"**:
   - `/tmp`
   - `/administrator/components`
   - `/components`
   - `/administrator/logs`

## Common Issues

### Issue 1: Wrong Owner
**Symptom:** Directories exist but not writable
**Solution:** Change owner to web server user
```bash
sudo chown -R www-data:www-data /var/www/grimpsa_webserver/administrator/components
sudo chown -R www-data:www-data /var/www/grimpsa_webserver/components
```

### Issue 2: Wrong Permissions
**Symptom:** Directories exist but not writable
**Solution:** Set correct permissions
```bash
sudo chmod -R 755 /var/www/grimpsa_webserver/administrator/components
sudo chmod -R 755 /var/www/grimpsa_webserver/components
```

### Issue 3: SELinux (if enabled)
**Symptom:** Permissions look correct but still can't write
**Solution:** Check SELinux context
```bash
# Check SELinux status
getenforce

# If enabled, set correct context
sudo chcon -R -t httpd_sys_rw_content_t /var/www/grimpsa_webserver/administrator/components
sudo chcon -R -t httpd_sys_rw_content_t /var/www/grimpsa_webserver/components
```

## Find Your Web Server User

To find which user your web server runs as:

```bash
# For Apache
ps aux | grep apache | head -1

# For Nginx
ps aux | grep nginx | head -1

# Or check Apache config
grep -i "^User" /etc/apache2/apache2.conf
# or
grep -i "^User" /etc/httpd/httpd.conf
```

Common web server users:
- `www-data` (Debian/Ubuntu)
- `apache` (CentOS/RHEL)
- `httpd` (some systems)
- `www` (some systems)

## After Fixing Permissions

1. Try installing the component again
2. Check if files are now extracted to:
   - `/var/www/grimpsa_webserver/administrator/components/com_odoocontacts/`
   - `/var/www/grimpsa_webserver/components/com_odoocontacts/`

