#!/bin/bash
#
# Diagnostic script to find component files
#

BASE_DIR="/var/www/grimpsa_webserver"
TMP_DIR="$BASE_DIR/tmp"

echo "=========================================="
echo "Component File Finder"
echo "=========================================="
echo ""

# Check if base directory exists
if [ ! -d "$BASE_DIR" ]; then
    echo "ERROR: Base directory does not exist: $BASE_DIR"
    exit 1
fi
echo "✓ Base directory exists: $BASE_DIR"
echo ""

# Check if tmp directory exists
if [ ! -d "$TMP_DIR" ]; then
    echo "ERROR: TMP directory does not exist: $TMP_DIR"
    echo ""
    echo "Please create it or check the path:"
    echo "  sudo mkdir -p $TMP_DIR"
    echo "  sudo chmod 755 $TMP_DIR"
    exit 1
fi
echo "✓ TMP directory exists: $TMP_DIR"
echo ""

# List contents of tmp
echo "Contents of $TMP_DIR:"
echo "----------------------------------------"
ls -la "$TMP_DIR" 2>/dev/null | head -30
echo ""

# Search for component files
echo "Searching for component files..."
echo "----------------------------------------"

# Look for odoocontacts.xml
echo "Looking for odoocontacts.xml..."
FOUND_XML=$(find "$TMP_DIR" -name "odoocontacts.xml" -type f 2>/dev/null)
if [ -n "$FOUND_XML" ]; then
    echo "✓ Found manifest file(s):"
    echo "$FOUND_XML" | while read -r file; do
        echo "  - $file"
        echo "    Directory: $(dirname "$file")"
    done
else
    echo "✗ No odoocontacts.xml found"
fi
echo ""

# Look for com_odoocontacts directory
echo "Looking for com_odoocontacts directory..."
FOUND_DIR=$(find "$TMP_DIR" -type d -name "com_odoocontacts" 2>/dev/null)
if [ -n "$FOUND_DIR" ]; then
    echo "✓ Found com_odoocontacts directory(ies):"
    echo "$FOUND_DIR" | while read -r dir; do
        echo "  - $dir"
        if [ -f "$dir/odoocontacts.xml" ]; then
            echo "    ✓ Contains odoocontacts.xml"
        fi
        if [ -d "$dir/admin" ]; then
            echo "    ✓ Contains admin/ directory"
        fi
        if [ -d "$dir/site" ]; then
            echo "    ✓ Contains site/ directory"
        fi
    done
else
    echo "✗ No com_odoocontacts directory found"
fi
echo ""

# Look for any zip files
echo "Looking for zip files..."
FOUND_ZIP=$(find "$TMP_DIR" -name "*.zip" -type f 2>/dev/null)
if [ -n "$FOUND_ZIP" ]; then
    echo "✓ Found zip file(s):"
    echo "$FOUND_ZIP" | while read -r file; do
        echo "  - $file"
        echo "    Size: $(du -h "$file" | cut -f1)"
    done
    echo ""
    echo "You may need to extract the zip file first:"
    echo "  cd $TMP_DIR"
    echo "  unzip com_odoocontacts_v*.zip"
else
    echo "✗ No zip files found"
fi
echo ""

# Summary
echo "=========================================="
echo "Summary"
echo "=========================================="
if [ -n "$FOUND_XML" ] || [ -n "$FOUND_DIR" ]; then
    echo "✓ Component files found!"
    echo ""
    echo "You can now run the install script:"
    echo "  sudo ./install_component.sh"
else
    echo "✗ Component files not found"
    echo ""
    echo "Please:"
    echo "1. Extract the component zip file in $TMP_DIR"
    echo "2. Or upload the extracted com_odoocontacts folder to $TMP_DIR"
    echo "3. Then run this script again to verify"
fi
echo ""

