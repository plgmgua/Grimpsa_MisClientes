# Manual Database Installation Guide

## Overview

If the component files are already in place (via the shell script), you just need to register the component in Joomla's database.

## Prerequisites

1. ✅ Component files are installed (via `install_component.sh`)
2. ✅ You have access to phpMyAdmin or database CLI
3. ✅ You know your Joomla table prefix (default: `joomla_`)

## Installation Steps

### Step 1: Open phpMyAdmin

1. Log into phpMyAdmin
2. Select your Joomla database
3. Click on the **SQL** tab

### Step 2: Update Table Prefix

**IMPORTANT:** Before running the SQL script, replace `joomla_` with your actual table prefix if different.

To find your table prefix:
- Check `configuration.php` file: `public $dbprefix = 'joomla_';`
- Or look at your database tables - they should all start with the same prefix

### Step 3: Run the SQL Script

1. Open `manual_install_com_odoocontacts.sql`
2. Copy the entire contents
3. Paste into phpMyAdmin SQL tab
4. **Replace `joomla_` with your actual prefix** (if different)
5. Click **Go** or **Execute**

### Step 4: Verify Installation

The script includes verification queries at the end. You should see:

1. **Component Extension** - Should show the component with `enabled = 1`
2. **Menu Item** - Should show a menu item in the admin menu
3. **Asset** - Should show the component asset entry

## What the Script Does

1. **Inserts component into `extensions` table**
   - Registers the component
   - Sets default parameters
   - Marks as enabled

2. **Creates admin menu item**
   - Adds "COM_ODOOCONTACTS" to the admin menu
   - Links to the component

3. **Creates asset entry**
   - Sets up permissions structure
   - Links to component

## After Installation

1. **Clear Joomla cache:**
   - Go to **System → Clear Cache**
   - Or delete `/cache` directory contents

2. **Verify in Joomla Admin:**
   - Go to **Extensions → Manage → Manage**
   - Look for "COM_ODOOCONTACTS" in the list
   - Should show as "Enabled"

3. **Access the component:**
   - Go to **Components → COM_ODOOCONTACTS**
   - Should open the component dashboard

## Troubleshooting

### Issue: "Duplicate entry" errors
**Solution:** The component might already be partially installed. Run the cleanup script first:
```sql
-- Run cleanup_com_odoocontacts.sql first
-- Then run manual_install_com_odoocontacts.sql
```

### Issue: Component not showing in admin
**Solution:**
1. Clear Joomla cache
2. Check if component is enabled in Extensions → Manage
3. Verify menu item exists in Menus → Main Menu (Administrator)

### Issue: "Table doesn't exist" errors
**Solution:** Check your table prefix. The script uses `joomla_` by default. Update all occurrences if your prefix is different.

### Issue: Component shows but gives 404 error
**Solution:** 
1. Verify files are in correct locations:
   - `/administrator/components/com_odoocontacts/`
   - `/components/com_odoocontacts/`
2. Check file permissions (should be readable by web server)
3. Clear Joomla cache

## Alternative: Use Joomla Installer

If you prefer, you can also:
1. Create a zip file with just the manifest (`odoocontacts.xml`)
2. Install via **Extensions → Manage → Install**
3. Joomla will detect files are already in place and just register in database

## Rollback

If something goes wrong, run the cleanup script:
```sql
-- Run cleanup_com_odoocontacts.sql
```

