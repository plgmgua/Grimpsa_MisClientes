# Ubuntu Server Installation Guide

## Your Server Setup
- **OS:** Ubuntu Server
- **Web Server:** Apache
- **Web Server User:** `www-data` (Ubuntu default)
- **Joomla Path:** `/var/www/grimpsa_webserver`

## Quick Installation Steps

### Step 1: Install Required Tools (if needed)
```bash
sudo apt-get update
sudo apt-get install dos2unix
```

### Step 2: Download/Copy the Script
Download `install_component.sh` to your server at:
```
/var/www/grimpsa_webserver/install_component.sh
```

### Step 3: Fix Line Endings (if downloaded from Windows)
```bash
cd /var/www/grimpsa_webserver
dos2unix install_component.sh
```

Or if you don't have dos2unix:
```bash
sed -i 's/\r$//' install_component.sh
```

### Step 4: Make Script Executable
```bash
chmod +x install_component.sh
```

### Step 5: Run the Script
**IMPORTANT: Always run with `bash` explicitly on Ubuntu:**
```bash
sudo bash install_component.sh
```

**NOT:**
```bash
./install_component.sh  # Might use /bin/sh instead of bash
```

## Verify Apache User

On Ubuntu, Apache runs as `www-data`. Verify:
```bash
# Check Apache user
grep "^User" /etc/apache2/apache2.conf

# Or check running processes
ps aux | grep apache2 | head -1

# Should show: www-data
```

## Verify File Permissions

After running the script, verify:
```bash
# Check component directories
ls -ld /var/www/grimpsa_webserver/administrator/components/com_odoocontacts
ls -ld /var/www/grimpsa_webserver/components/com_odoocontacts

# Should show owner: www-data www-data
# Should show permissions: drwxr-xr-x (755)
```

## Troubleshooting

### Issue: "Bad substitution" error
**Solution:** Run with `bash` explicitly:
```bash
sudo bash install_component.sh
```

### Issue: "No such file or directory" errors
**Solution:** Fix line endings:
```bash
dos2unix install_component.sh
```

### Issue: Permission denied
**Solution:** 
```bash
sudo chmod +x install_component.sh
sudo bash install_component.sh
```

### Issue: www-data user not found
**Solution:** This shouldn't happen on Ubuntu, but if it does:
```bash
# Verify user exists
id www-data

# If not, create it (shouldn't be necessary)
sudo useradd -r -s /bin/false www-data
```

## After Installation

1. **Files should be copied to:**
   - `/var/www/grimpsa_webserver/administrator/components/com_odoocontacts/`
   - `/var/www/grimpsa_webserver/components/com_odoocontacts/`
   - `/var/www/grimpsa_webserver/media/com_odoocontacts/`

2. **Ownership should be:** `www-data:www-data`

3. **Then install via Joomla Admin:**
   - Go to **Extensions → Manage → Install**
   - The component should install successfully (files are already in place)

## Ubuntu-Specific Notes

- Apache configuration: `/etc/apache2/apache2.conf`
- Apache user: `www-data` (default)
- Service management: `sudo systemctl restart apache2`
- Logs: `/var/log/apache2/error.log`

