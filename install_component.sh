#!/bin/bash
#
# Joomla Component Installation Script
# Copies component files from tmp directory and sets proper permissions
#
# Usage: sudo ./install_component.sh
#

# Self-check validation
SCRIPT_PATH="${BASH_SOURCE[0]}"
SCRIPT_DIR="$(cd "$(dirname "$SCRIPT_PATH")" && pwd)"
SCRIPT_NAME="$(basename "$SCRIPT_PATH")"
FULL_SCRIPT_PATH="$SCRIPT_DIR/$SCRIPT_NAME"

# Check if script file exists
if [ ! -f "$FULL_SCRIPT_PATH" ]; then
    echo "ERROR: Script file not found: $FULL_SCRIPT_PATH"
    echo "Current directory: $(pwd)"
    echo "Script name: $SCRIPT_NAME"
    echo ""
    echo "Please ensure:"
    echo "1. The script file exists in the current directory"
    echo "2. You're running it from the correct location"
    echo "3. The file name is correct: install_component.sh"
    exit 1
fi

# Check if script is readable
if [ ! -r "$FULL_SCRIPT_PATH" ]; then
    echo "ERROR: Script file is not readable: $FULL_SCRIPT_PATH"
    echo "Please check file permissions"
    exit 1
fi

# Check if script has execute permission
if [ ! -x "$FULL_SCRIPT_PATH" ]; then
    echo "WARNING: Script does not have execute permission"
    echo "Attempting to fix..."
    chmod +x "$FULL_SCRIPT_PATH" 2>/dev/null || {
        echo "ERROR: Cannot add execute permission. Please run: chmod +x $FULL_SCRIPT_PATH"
        exit 1
    }
    echo "Execute permission added"
fi

# Check for Windows line endings (CRLF)
if file "$FULL_SCRIPT_PATH" | grep -q "CRLF"; then
    echo "WARNING: Script has Windows line endings (CRLF)"
    echo "This may cause 'No such file or directory' errors"
    echo "Please convert to Unix line endings: dos2unix $FULL_SCRIPT_PATH"
    echo ""
    echo "Attempting to continue anyway..."
fi

# Verify bash is available
if ! command -v bash &> /dev/null; then
    echo "ERROR: bash is not available on this system"
    exit 1
fi

# Test if script can be executed
if ! bash -n "$FULL_SCRIPT_PATH" 2>/dev/null; then
    echo "ERROR: Script has syntax errors"
    bash -n "$FULL_SCRIPT_PATH"
    exit 1
fi

set -e  # Exit on error

# Configuration
BASE_DIR="/var/www/grimpsa_webserver"
TMP_DIR="$BASE_DIR/tmp"
ADMIN_COMPONENT_DIR="$BASE_DIR/administrator/components/com_odoocontacts"
SITE_COMPONENT_DIR="$BASE_DIR/components/com_odoocontacts"
MEDIA_DIR="$BASE_DIR/media/com_odoocontacts"

# Display script information
echo "=========================================="
echo "Joomla Component Installation Script"
echo "=========================================="
echo "Script location: $FULL_SCRIPT_PATH"
echo "Script directory: $SCRIPT_DIR"
echo "Current working directory: $(pwd)"
echo "=========================================="
echo ""

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

# Check if tmp directory exists
if [ ! -d "$TMP_DIR" ]; then
    print_error "TMP directory does not exist: $TMP_DIR"
    exit 1
fi

# Find component directory in tmp
print_info "Looking for component files in $TMP_DIR..."

# List what's in tmp for debugging
print_info "Contents of $TMP_DIR:"
ls -la "$TMP_DIR" 2>/dev/null | head -20 || true
echo ""

# Check for common tmp extraction patterns
COMPONENT_SOURCE=""

# First, check if com_odoocontacts is directly in tmp (most common case)
if [ -d "$TMP_DIR/com_odoocontacts" ]; then
    COMPONENT_SOURCE="$TMP_DIR/com_odoocontacts"
    print_info "Found component in: $COMPONENT_SOURCE"
# Check for install_* subdirectories (Joomla auto-extraction)
elif [ -d "$TMP_DIR" ]; then
    # Look for install_* directories that might contain the component
    for install_dir in "$TMP_DIR"/install_*; do
        if [ -d "$install_dir" ] && [ -d "$install_dir/com_odoocontacts" ]; then
            COMPONENT_SOURCE="$install_dir/com_odoocontacts"
            print_info "Found component in: $COMPONENT_SOURCE"
            break
        fi
    done
    
    # If still not found, look for any com_odoocontacts directory
    if [ -z "$COMPONENT_SOURCE" ]; then
        for dir in "$TMP_DIR"/*; do
            if [ -d "$dir" ] && [ -f "$dir/odoocontacts.xml" ]; then
                COMPONENT_SOURCE="$dir"
                print_info "Found component by manifest file: $COMPONENT_SOURCE"
                break
            fi
        done
    fi
fi

if [ -z "$COMPONENT_SOURCE" ] || [ ! -d "$COMPONENT_SOURCE" ]; then
    print_error "Component directory not found in $TMP_DIR"
    print_info ""
    print_info "Please ensure you have extracted the component zip file in the tmp directory"
    print_info ""
    print_info "Expected locations:"
    print_info "  - $TMP_DIR/com_odoocontacts/"
    print_info "  - $TMP_DIR/install_*/com_odoocontacts/"
    print_info ""
    print_info "Current contents of $TMP_DIR:"
    ls -la "$TMP_DIR" 2>/dev/null || print_error "Cannot list $TMP_DIR"
    exit 1
fi

print_info "Found component source: $COMPONENT_SOURCE"

# Verify component structure
print_info "Verifying component structure..."
if [ ! -f "$COMPONENT_SOURCE/odoocontacts.xml" ]; then
    print_error "Component manifest file (odoocontacts.xml) not found in $COMPONENT_SOURCE"
    print_info "Files in component directory:"
    ls -la "$COMPONENT_SOURCE" 2>/dev/null || true
    exit 1
fi
print_info "✓ Manifest file found: $COMPONENT_SOURCE/odoocontacts.xml"

# Check for required directories
if [ -d "$COMPONENT_SOURCE/admin" ]; then
    print_info "✓ Admin directory found"
else
    print_warning "Admin directory not found (may be optional)"
fi

if [ -d "$COMPONENT_SOURCE/site" ]; then
    print_info "✓ Site directory found"
else
    print_warning "Site directory not found (may be optional)"
fi

if [ -d "$COMPONENT_SOURCE/media" ]; then
    print_info "✓ Media directory found"
else
    print_warning "Media directory not found (may be optional)"
fi

print_info "Component structure verified"

# Create destination directories
print_info "Creating destination directories..."
mkdir -p "$ADMIN_COMPONENT_DIR"
mkdir -p "$SITE_COMPONENT_DIR"
mkdir -p "$MEDIA_DIR"

# Copy admin files
if [ -d "$COMPONENT_SOURCE/admin" ]; then
    print_info "Copying admin files from $COMPONENT_SOURCE/admin..."
    if [ ! -d "$ADMIN_COMPONENT_DIR" ]; then
        mkdir -p "$ADMIN_COMPONENT_DIR"
    fi
    cp -r "$COMPONENT_SOURCE/admin"/* "$ADMIN_COMPONENT_DIR/" 2>&1
    if [ $? -eq 0 ]; then
        print_info "Admin files copied successfully"
    else
        print_error "Failed to copy admin files"
        exit 1
    fi
else
    print_warning "Admin directory not found in source: $COMPONENT_SOURCE/admin"
fi

# Copy site files
if [ -d "$COMPONENT_SOURCE/site" ]; then
    print_info "Copying site files from $COMPONENT_SOURCE/site..."
    if [ ! -d "$SITE_COMPONENT_DIR" ]; then
        mkdir -p "$SITE_COMPONENT_DIR"
    fi
    cp -r "$COMPONENT_SOURCE/site"/* "$SITE_COMPONENT_DIR/" 2>&1
    if [ $? -eq 0 ]; then
        print_info "Site files copied successfully"
    else
        print_error "Failed to copy site files"
        exit 1
    fi
else
    print_warning "Site directory not found in source: $COMPONENT_SOURCE/site"
fi

# Copy media files
if [ -d "$COMPONENT_SOURCE/media" ]; then
    print_info "Copying media files from $COMPONENT_SOURCE/media..."
    if [ ! -d "$MEDIA_DIR" ]; then
        mkdir -p "$MEDIA_DIR"
    fi
    cp -r "$COMPONENT_SOURCE/media"/* "$MEDIA_DIR/" 2>&1
    if [ $? -eq 0 ]; then
        print_info "Media files copied successfully"
    else
        print_error "Failed to copy media files"
        exit 1
    fi
else
    print_warning "Media directory not found in source: $COMPONENT_SOURCE/media"
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

