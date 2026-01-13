# Fix Zip Structure for Joomla Installation

## Error
```
JInstaller: :Install: Can't find Joomla XML setup file.
```

## Root Cause
Joomla expects the manifest XML file (`odoocontacts.xml`) to be at the **root level** of the zip file, not inside a folder.

**Correct zip structure:**
```
com_odoocontacts_v1.2.6.zip
├── odoocontacts.xml          ← Must be at root
├── admin/
├── site/
└── media/
```

**Wrong zip structure:**
```
com_odoocontacts_v1.2.6.zip
└── com_odoocontacts/         ← Extra folder wrapper
    ├── odoocontacts.xml
    ├── admin/
    └── ...
```

## Solution

### Option 1: Create Zip from Inside Component Directory

When creating the zip file, make sure you're **inside** the `com_odoocontacts` directory:

```bash
cd com_odoocontacts
zip -r ../com_odoocontacts_v1.2.7.zip . -x "*.git*" "*.DS_Store" "*__MACOSX*" "*.zip"
```

**NOT:**
```bash
# This creates wrong structure
cd ..
zip -r com_odoocontacts_v1.2.7.zip com_odoocontacts/
```

### Option 2: Verify Zip Structure

Check the zip structure:
```bash
unzip -l com_odoocontacts_v1.2.6.zip | head -5
```

Should show:
```
Archive:  com_odoocontacts_v1.2.6.zip
  Length      Date    Time    Name
---------  ---------- -----   ----
     4462  ...        odoocontacts.xml    ← At root level
        0  ...        admin/
```

**NOT:**
```
     4462  ...        com_odoocontacts/odoocontacts.xml    ← Inside folder
```

### Option 3: Fix Existing Zip

If you have a zip with wrong structure, extract and re-zip:

```bash
# Extract
unzip com_odoocontacts_v1.2.6.zip -d temp_extract

# Check structure
ls -la temp_extract/

# If you see com_odoocontacts/ folder, move contents up
if [ -d "temp_extract/com_odoocontacts" ]; then
    mv temp_extract/com_odoocontacts/* temp_extract/
    rmdir temp_extract/com_odoocontacts
fi

# Re-zip from inside
cd temp_extract
zip -r ../com_odoocontacts_v1.2.7.zip . -x "*.git*" "*.DS_Store" "*__MACOSX*"
cd ..
rm -rf temp_extract
```

## Verification

After creating the zip, verify:
```bash
# Check first few entries
unzip -l com_odoocontacts_v1.2.7.zip | head -10

# Should show odoocontacts.xml at root, not in a subfolder
```

## Updated Zip Creation Script

I'll update the zip creation to ensure correct structure.

