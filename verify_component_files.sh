#!/bin/bash
#
# Verify Component Files Installation
# Checks if all required files are in the correct locations
#

BASE_DIR="/var/www/grimpsa_webserver"
ADMIN_COMPONENT_DIR="$BASE_DIR/administrator/components/com_odoocontacts"
SITE_COMPONENT_DIR="$BASE_DIR/components/com_odoocontacts"

echo "=========================================="
echo "Component Files Verification"
echo "=========================================="
echo ""

# Check critical admin files
echo "Checking Admin Files..."
echo "----------------------------------------"

CRITICAL_FILES=(
    "$ADMIN_COMPONENT_DIR/admin/src/Extension/OdooContactsComponent.php"
    "$ADMIN_COMPONENT_DIR/admin/services/provider.php"
    "$ADMIN_COMPONENT_DIR/admin/config.xml"
    "$ADMIN_COMPONENT_DIR/admin/src/Controller/DisplayController.php"
    "$ADMIN_COMPONENT_DIR/admin/src/View/Dashboard/HtmlView.php"
    "$ADMIN_COMPONENT_DIR/admin/tmpl/dashboard/default.php"
)

for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✓ $(basename $file)"
    else
        echo "✗ MISSING: $(basename $file)"
        echo "  Expected: $file"
    fi
done

echo ""
echo "Checking Directory Structure..."
echo "----------------------------------------"

# Check directory structure
DIRS=(
    "$ADMIN_COMPONENT_DIR/admin/src/Extension"
    "$ADMIN_COMPONENT_DIR/admin/src/Controller"
    "$ADMIN_COMPONENT_DIR/admin/src/View"
    "$ADMIN_COMPONENT_DIR/admin/services"
    "$ADMIN_COMPONENT_DIR/admin/tmpl"
    "$SITE_COMPONENT_DIR/src"
)

for dir in "${DIRS[@]}"; do
    if [ -d "$dir" ]; then
        echo "✓ $(basename $dir)/"
    else
        echo "✗ MISSING: $(basename $dir)/"
        echo "  Expected: $dir"
    fi
done

echo ""
echo "Checking Component Extension Class..."
echo "----------------------------------------"

EXTENSION_FILE="$ADMIN_COMPONENT_DIR/admin/src/Extension/OdooContactsComponent.php"
if [ -f "$EXTENSION_FILE" ]; then
    echo "✓ Extension class file exists"
    echo "  Location: $EXTENSION_FILE"
    
    # Check if file has content
    if [ -s "$EXTENSION_FILE" ]; then
        echo "✓ File is not empty"
        
        # Check namespace
        if grep -q "namespace Grimpsa\\\\Component\\\\OdooContacts\\\\Administrator\\\\Extension" "$EXTENSION_FILE"; then
            echo "✓ Namespace is correct"
        else
            echo "✗ Namespace might be incorrect"
        fi
        
        # Check class name
        if grep -q "class OdooContactsComponent" "$EXTENSION_FILE"; then
            echo "✓ Class name is correct"
        else
            echo "✗ Class name might be incorrect"
        fi
    else
        echo "✗ File is empty!"
    fi
else
    echo "✗ Extension class file NOT FOUND!"
    echo "  This is the critical file causing the error"
fi

echo ""
echo "Checking Service Provider..."
echo "----------------------------------------"

PROVIDER_FILE="$ADMIN_COMPONENT_DIR/admin/services/provider.php"
if [ -f "$PROVIDER_FILE" ]; then
    echo "✓ Service provider file exists"
    if grep -q "OdooContactsComponent" "$PROVIDER_FILE"; then
        echo "✓ References OdooContactsComponent class"
    else
        echo "✗ Does not reference OdooContactsComponent"
    fi
else
    echo "✗ Service provider file NOT FOUND!"
fi

echo ""
echo "File Permissions Check..."
echo "----------------------------------------"

if [ -f "$EXTENSION_FILE" ]; then
    PERMS=$(stat -c "%a" "$EXTENSION_FILE" 2>/dev/null || stat -f "%OLp" "$EXTENSION_FILE" 2>/dev/null)
    OWNER=$(stat -c "%U:%G" "$EXTENSION_FILE" 2>/dev/null || stat -f "%Su:%Sg" "$EXTENSION_FILE" 2>/dev/null)
    echo "Extension file permissions: $PERMS"
    echo "Extension file owner: $OWNER"
    
    if [ -r "$EXTENSION_FILE" ]; then
        echo "✓ File is readable"
    else
        echo "✗ File is NOT readable!"
    fi
fi

echo ""
echo "=========================================="
echo "Summary"
echo "=========================================="
echo ""
echo "If the Extension class file is missing or in wrong location,"
echo "the component will not work. Check the file structure above."
echo ""

