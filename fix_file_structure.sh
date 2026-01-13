#!/bin/bash
#
# Fix Component File Structure
# Moves files from wrong location (admin/admin/) to correct location
#

BASE_DIR="/var/www/grimpsa_webserver"
ADMIN_COMPONENT_DIR="$BASE_DIR/administrator/components/com_odoocontacts"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

echo "=========================================="
echo "Fix Component File Structure"
echo "=========================================="
echo ""

# Check if component directory exists
if [ ! -d "$ADMIN_COMPONENT_DIR" ]; then
    print_error "Component directory not found: $ADMIN_COMPONENT_DIR"
    exit 1
fi

print_info "Component directory: $ADMIN_COMPONENT_DIR"
echo ""

# Check current structure
print_info "Checking current file structure..."
if [ -d "$ADMIN_COMPONENT_DIR/admin/src" ]; then
    print_warning "Files are in wrong location: admin/admin/src/"
    print_info "Expected location: src/"
    echo ""
    
    # Check if correct location already has files
    if [ -d "$ADMIN_COMPONENT_DIR/src" ]; then
        print_warning "Both locations exist! Checking which one to use..."
        if [ -f "$ADMIN_COMPONENT_DIR/src/Extension/OdooContactsComponent.php" ]; then
            print_info "✓ Correct location already has Extension class"
            print_info "Removing duplicate admin/ directory..."
            rm -rf "$ADMIN_COMPONENT_DIR/admin"
            print_info "Duplicate removed"
        else
            print_info "Moving files from admin/ to root..."
            mv "$ADMIN_COMPONENT_DIR/admin"/* "$ADMIN_COMPONENT_DIR/"
            rmdir "$ADMIN_COMPONENT_DIR/admin" 2>/dev/null
            print_info "Files moved"
        fi
    else
        print_info "Moving files from admin/ to root level..."
        mv "$ADMIN_COMPONENT_DIR/admin"/* "$ADMIN_COMPONENT_DIR/"
        rmdir "$ADMIN_COMPONENT_DIR/admin" 2>/dev/null
        print_info "Files moved successfully"
    fi
else
    print_info "File structure looks correct"
fi

echo ""
print_info "Verifying correct structure..."
echo ""

# Verify critical files are in correct locations
CRITICAL_FILES=(
    "$ADMIN_COMPONENT_DIR/src/Extension/OdooContactsComponent.php"
    "$ADMIN_COMPONENT_DIR/services/provider.php"
    "$ADMIN_COMPONENT_DIR/config.xml"
)

ALL_GOOD=true
for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        print_info "✓ $(basename $(dirname $file))/$(basename $file)"
    else
        print_error "✗ MISSING: $(basename $(dirname $file))/$(basename $file)"
        print_error "  Expected: $file"
        ALL_GOOD=false
    fi
done

echo ""
if [ "$ALL_GOOD" = true ]; then
    print_info "=========================================="
    print_info "File structure is now correct!"
    print_info "=========================================="
    echo ""
    print_info "Next steps:"
    print_info "1. Clear Joomla cache: System → Clear Cache"
    print_info "2. Test component: Components → COM_ODOOCONTACTS"
else
    print_error "Some files are still missing. Please check manually."
fi
echo ""

