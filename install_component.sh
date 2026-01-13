#!/bin/bash
#
# Joomla Component Installation Script
# Copies component files from tmp directory and sets proper permissions
#
# Usage: sudo ./install_component.sh
#

set -e  # Exit on error

# Configuration
BASE_DIR="/var/www/grimpsa_webserver"
TMP_DIR="$BASE_DIR/tmp"
ADMIN_COMPONENT_DIR="$BASE_DIR/administrator/components/com_odoocontacts"
SITE_COMPONENT_DIR="$BASE_DIR/components/com_odoocontacts"
MEDIA_DIR="$BASE_DIR/media/com_odoocontacts"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then 
    print_error "Please run as root or with sudo: sudo $0"
    exit 1
fi

# Find web server user
if [ -f /etc/apache2/apache2.conf ]; then
    WEB_USER=$(grep -i "^User" /etc/apache2/apache2.conf 2>/dev/null | awk '{print $2}' | head -1)
elif [ -f /etc/httpd/httpd.conf ]; then
    WEB_USER=$(grep -i "^User" /etc/httpd/httpd.conf 2>/dev/null | awk '{print $2}' | head -1)
else
    # Try to detect from running process
    WEB_USER=$(ps aux | grep -E "(apache|httpd|nginx)" | grep -v grep | head -1 | awk '{print $1}')
fi

# Default to www-data if not found
if [ -z "$WEB_USER" ]; then
    WEB_USER="www-data"
    print_warning "Could not detect web server user, defaulting to: $WEB_USER"
else
    print_info "Detected web server user: $WEB_USER"
fi

# Find component directory in tmp
print_info "Looking for component files in $TMP_DIR..."

# Check for common tmp extraction patterns
COMPONENT_SOURCE=""
if [ -d "$TMP_DIR/com_odoocontacts" ]; then
    COMPONENT_SOURCE="$TMP_DIR/com_odoocontacts"
elif [ -d "$TMP_DIR/install_"*"/com_odoocontacts" ]; then
    COMPONENT_SOURCE=$(find "$TMP_DIR" -type d -name "com_odoocontacts" -path "*/install_*/*" | head -1)
elif [ -d "$TMP_DIR" ]; then
    # Look for any com_odoocontacts directory
    COMPONENT_SOURCE=$(find "$TMP_DIR" -type d -name "com_odoocontacts" | head -1)
fi

if [ -z "$COMPONENT_SOURCE" ] || [ ! -d "$COMPONENT_SOURCE" ]; then
    print_error "Component directory not found in $TMP_DIR"
    print_info "Please ensure you have extracted the component zip file in the tmp directory"
    print_info "Expected locations:"
    print_info "  - $TMP_DIR/com_odoocontacts/"
    print_info "  - $TMP_DIR/install_*/com_odoocontacts/"
    exit 1
fi

print_info "Found component source: $COMPONENT_SOURCE"

# Verify component structure
if [ ! -f "$COMPONENT_SOURCE/odoocontacts.xml" ]; then
    print_error "Component manifest file (odoocontacts.xml) not found in $COMPONENT_SOURCE"
    exit 1
fi

print_info "Component structure verified"

# Create destination directories
print_info "Creating destination directories..."
mkdir -p "$ADMIN_COMPONENT_DIR"
mkdir -p "$SITE_COMPONENT_DIR"
mkdir -p "$MEDIA_DIR"

# Copy admin files
if [ -d "$COMPONENT_SOURCE/admin" ]; then
    print_info "Copying admin files..."
    cp -r "$COMPONENT_SOURCE/admin"/* "$ADMIN_COMPONENT_DIR/"
    print_info "Admin files copied"
else
    print_warning "Admin directory not found in source"
fi

# Copy site files
if [ -d "$COMPONENT_SOURCE/site" ]; then
    print_info "Copying site files..."
    cp -r "$COMPONENT_SOURCE/site"/* "$SITE_COMPONENT_DIR/"
    print_info "Site files copied"
else
    print_warning "Site directory not found in source"
fi

# Copy media files
if [ -d "$COMPONENT_SOURCE/media" ]; then
    print_info "Copying media files..."
    cp -r "$COMPONENT_SOURCE/media"/* "$MEDIA_DIR/"
    print_info "Media files copied"
else
    print_warning "Media directory not found in source"
fi

# Set ownership
print_info "Setting ownership to $WEB_USER:$WEB_USER..."
chown -R "$WEB_USER:$WEB_USER" "$ADMIN_COMPONENT_DIR"
chown -R "$WEB_USER:$WEB_USER" "$SITE_COMPONENT_DIR"
chown -R "$WEB_USER:$WEB_USER" "$MEDIA_DIR"
print_info "Ownership set"

# Set permissions
print_info "Setting permissions..."
# Directories: 755
find "$ADMIN_COMPONENT_DIR" -type d -exec chmod 755 {} \;
find "$SITE_COMPONENT_DIR" -type d -exec chmod 755 {} \;
find "$MEDIA_DIR" -type d -exec chmod 755 {} \;

# Files: 644
find "$ADMIN_COMPONENT_DIR" -type f -exec chmod 644 {} \;
find "$SITE_COMPONENT_DIR" -type f -exec chmod 644 {} \;
find "$MEDIA_DIR" -type f -exec chmod 644 {} \;

# Make script.php executable
if [ -f "$ADMIN_COMPONENT_DIR/admin/script.php" ]; then
    chmod 644 "$ADMIN_COMPONENT_DIR/admin/script.php"
fi

print_info "Permissions set"

# Verify installation
print_info "Verifying installation..."
if [ -f "$ADMIN_COMPONENT_DIR/admin/script.php" ]; then
    print_info "✓ Installer script found"
else
    print_warning "✗ Installer script not found (may be in different location)"
fi

if [ -f "$ADMIN_COMPONENT_DIR/admin/config.xml" ]; then
    print_info "✓ Config file found"
else
    print_warning "✗ Config file not found"
fi

if [ -d "$SITE_COMPONENT_DIR/src" ]; then
    print_info "✓ Site source directory found"
else
    print_warning "✗ Site source directory not found"
fi

# Summary
echo ""
print_info "=========================================="
print_info "Installation Complete!"
print_info "=========================================="
print_info "Files installed to:"
print_info "  Admin: $ADMIN_COMPONENT_DIR"
print_info "  Site:  $SITE_COMPONENT_DIR"
print_info "  Media: $MEDIA_DIR"
echo ""
print_info "Next steps:"
print_info "1. Go to Joomla Admin → Extensions → Manage → Install"
print_info "2. Install the component (it will register in database)"
print_info "3. Or manually register in database if needed"
echo ""

