# Fix Component File Structure Error

## Error
```
Class "Grimpsa\Component\OdooContacts\Administrator\Extension\OdooContactsComponent" not found
```

## Root Cause
The install script copied files to the wrong location, creating an extra `admin/` directory level.

**Wrong structure:**
```
/administrator/components/com_odoocontacts/admin/src/Extension/OdooContactsComponent.php
```

**Correct structure:**
```
/administrator/components/com_odoocontacts/src/Extension/OdooContactsComponent.php
```

## Quick Fix

### Option 1: Move Files (Recommended)
```bash
cd /var/www/grimpsa_webserver/administrator/components/com_odoocontacts

# If files are in admin/admin/, move them up one level
if [ -d "admin/src" ]; then
    echo "Files are in wrong location, moving..."
    mv admin/* .
    rmdir admin
    echo "Files moved successfully"
fi
```

### Option 2: Re-run Install Script
The install script has been fixed. You can:
1. Remove the component directory
2. Re-run the install script

```bash
# Remove existing installation
sudo rm -rf /var/www/grimpsa_webserver/administrator/components/com_odoocontacts
sudo rm -rf /var/www/grimpsa_webserver/components/com_odoocontacts

# Re-run install script
cd /var/www/grimpsa_webserver
sudo bash install_component.sh
```

### Option 3: Manual Fix
```bash
cd /var/www/grimpsa_webserver/administrator/components/com_odoocontacts

# Check current structure
ls -la

# If you see an 'admin' directory, move its contents up
if [ -d "admin" ]; then
    mv admin/* .
    rmdir admin
fi

# Verify correct structure
ls -la src/Extension/
# Should show: OdooContactsComponent.php
```

## Verify Correct Structure

Run the verification script:
```bash
cd /var/www/grimpsa_webserver
sudo bash verify_component_files.sh
```

This will check:
- ✓ Extension class file exists at correct location
- ✓ Service provider file exists
- ✓ All directories are in place
- ✓ File permissions are correct

## Expected File Structure

```
/administrator/components/com_odoocontacts/
├── src/
│   └── Extension/
│       └── OdooContactsComponent.php  ← CRITICAL FILE
├── services/
│   └── provider.php
├── config.xml
├── src/
│   ├── Controller/
│   ├── View/
│   └── Helper/
└── tmpl/
```

**NOT:**
```
/administrator/components/com_odoocontacts/
└── admin/          ← WRONG! Extra level
    ├── src/
    ├── services/
    └── ...
```

## After Fixing

1. **Clear Joomla cache:**
   - System → Clear Cache

2. **Test component:**
   - Components → COM_ODOOCONTACTS
   - Should load without errors

