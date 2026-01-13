# Joomla 5.3.4 Installation Notes

## Your System Information
- **Joomla Version:** 5.3.4 Stable
- **Database:** MySQL 8.0.44
- **PHP Version:** 8.3.6
- **Database Collation:** utf8mb4_0900_ai_ci

## Important Notes for Joomla 5.3.4

### Schema Differences from Joomla 4
1. **No `system_data` column** in `extensions` table
2. **No `version` column** in `extensions` table (version stored in `manifest_cache` JSON)
3. **Strict datetime mode** - Use `NULL` instead of `'0000-00-00 00:00:00'`

### MySQL 8.0 Considerations
- **Strict SQL mode** is enabled by default
- **Invalid datetime values** are rejected
- Use `NULL` for empty datetime fields

## Installation Steps

### Step 1: Verify Table Structure
Run `verify_tables_schema.sql` to see your actual table structure:
- Shows all columns in `extensions`, `menu`, and `assets` tables
- Checks if component already exists
- Provides summary

### Step 2: Run Installation Script
Run `manual_install_com_odoocontacts.sql`:
- Already fixed for Joomla 5.3.4
- Uses `NULL` for datetime fields
- No `system_data` or `version` columns
- Compatible with MySQL 8.0 strict mode

## Common Issues

### Issue: "Unknown column" errors
**Solution:** Run `verify_tables_schema.sql` first to see actual column names

### Issue: "Incorrect datetime value" errors
**Solution:** Already fixed - script uses `NULL` instead of `'0000-00-00 00:00:00'`

### Issue: "Table doesn't exist"
**Solution:** Check your table prefix in `configuration.php` and update the script

## After Installation

1. **Clear Joomla cache:**
   - System → Clear Cache
   - Or delete `/cache` directory contents

2. **Verify in Admin:**
   - Extensions → Manage → Manage
   - Look for "COM_ODOOCONTACTS"
   - Should show as "Enabled"

3. **Access component:**
   - Components → COM_ODOOCONTACTS

