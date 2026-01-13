# Fix "Can't find Joomla XML setup file" Error

## Error
```
JInstaller: :Install: Can't find Joomla XML setup file.
```

## Root Cause
Joomla looks for the manifest XML file **before** running the installer script. If it can't find it, the installation fails immediately.

## Diagnosis
The logs show:
- ✅ Preflight is being called (script is found)
- ❌ Joomla can't find the manifest XML

This means:
1. The zip file is being extracted
2. The script.php is being found
3. But the manifest XML is not where Joomla expects it

## Solution

### Check 1: Manifest File Name
Joomla looks for the manifest XML file. For component `com_odoocontacts`, it should be:
- `odoocontacts.xml` ✅ (current)
- OR `com_odoocontacts.xml` (sometimes required)

### Check 2: Zip Structure
The manifest MUST be at the **root level** of the zip file:

**Correct:**
```
com_odoocontacts_v1.2.8.zip
├── odoocontacts.xml          ← At root
├── admin/
├── site/
└── media/
```

**Wrong:**
```
com_odoocontacts_v1.2.8.zip
└── com_odoocontacts/         ← Extra folder
    ├── odoocontacts.xml
    └── ...
```

### Check 3: Verify Zip Contents
```bash
unzip -l com_odoocontacts_v1.2.8.zip | head -10
```

Should show `odoocontacts.xml` as one of the first files, not nested.

### Check 4: Try Alternative Manifest Name
Some Joomla versions require the manifest to match the component element name exactly. Try renaming:
- From: `odoocontacts.xml`
- To: `com_odoocontacts.xml`

## Manual Test
Extract the zip manually and check:
```bash
cd /tmp
unzip /path/to/com_odoocontacts_v1.2.8.zip -d test_extract
ls -la test_extract/
# Should show odoocontacts.xml at root level
```

## Alternative: Use Manual Installation
Since the files are already on the server (via shell script), you can:
1. Use the manual database installation script
2. Skip the Joomla installer entirely
3. The component will work once registered in the database

