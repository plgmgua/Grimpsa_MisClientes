# Diagnostic Information Needed

To diagnose why the installer script is not executing, please provide the following information:

## Quick Test (Do This First!)

1. **Upload `test_installer_script.php` to your Joomla root directory**
2. **Access it via browser:** `https://your-site.com/test_installer_script.php`
3. **Copy and paste the ENTIRE output here**
4. **DELETE the test file after!**

This will tell us immediately if:
- The script file exists
- The script can be loaded
- The class can be instantiated
- Log files are being created
- Directory permissions are correct

## 1. Joomla Version
- Go to **System → System Information → System Information**
- What is the **Joomla Version** shown?

## 2. PHP Version
- Go to **System → System Information → PHP Information**
- What is the **PHP Version** shown?

## 3. Check if Script File Exists After Installation Attempt
After trying to install (even if it fails), check via FTP or file manager:
- Does this file exist? `/administrator/components/com_odoocontacts/admin/script.php`
- If yes, what is the file size?
- If no, the zip extraction might be failing

## 4. Check Zip File Structure
When you extract the zip file manually on your computer:
- Is there a file: `com_odoocontacts/admin/script.php`?
- What is its size?

## 5. Check Joomla Error Logs
- Go to **System → System Information → System Information**
- Look for **Path to Log Folder**
- Check that folder for any error files
- Look for files with today's date

## 6. Check PHP Error Log Location
- In **System → System Information → PHP Information**
- Search for "error_log"
- What is the path shown?

## 7. Check File Permissions
- Go to **System → System Information → Folder Permissions**
- Are these folders **Writable**?
  - `/administrator/components`
  - `/components`
  - `/tmp`
  - `/administrator/logs`

## 8. Check Database
Run this SQL query in phpMyAdmin:
```sql
SELECT * FROM `joomla_extensions` 
WHERE `element` = 'com_odoocontacts' 
AND `type` = 'component';
```
- Does it return any rows?
- If yes, what is the `manifest_cache` value?

## 9. Installation Error Details
When you try to install:
- What **exact error message** appears?
- Is it just "Error installing component" or is there more text?
- Does the error appear immediately or after some time?

## 10. Check Apache/PHP Error Logs
If you have access to server logs:
- Check Apache error log (usually `/var/log/apache2/error.log` or similar)
- Check PHP error log
- Look for any entries with "odoocontacts" or "script.php"

## 11. Test Manual Extraction
1. Download the zip file
2. Extract it on your computer
3. Check if `com_odoocontacts/admin/script.php` exists
4. Open it in a text editor - does it look like valid PHP code?

## 12. Check Manifest File
After extraction, check:
- Does `com_odoocontacts/odoocontacts.xml` exist?
- Open it and search for "script.php"
- Is it listed in the `<files folder="admin">` section?

Please provide as much of this information as possible to help diagnose the issue.

