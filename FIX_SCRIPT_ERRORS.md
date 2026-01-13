# Fix Script Execution Errors

## Errors You're Seeing

1. `: not foundl.sh: 2:` - CRLF line ending issue
2. `./newinstall.sh: 12: Bad substitution` - Script running with `/bin/sh` instead of `/bin/bash`

## Quick Fix

### Step 1: Convert Line Endings

If you have `dos2unix` installed:
```bash
dos2unix install_component.sh
```

If you don't have `dos2unix`, install it:
```bash
# Debian/Ubuntu
sudo apt-get install dos2unix

# CentOS/RHEL
sudo yum install dos2unix
```

Or use `sed` to fix it:
```bash
sed -i 's/\r$//' install_component.sh
```

### Step 2: Ensure Script Runs with Bash

**Always run with bash explicitly:**
```bash
bash install_component.sh
```

Or:
```bash
sudo bash install_component.sh
```

**NOT:**
```bash
./install_component.sh  # This might use /bin/sh
sh install_component.sh  # This will fail
```

### Step 3: Make Script Executable
```bash
chmod +x install_component.sh
```

## Updated Script

The updated `install_component.sh` now:
- ✅ Automatically detects and fixes CRLF line endings
- ✅ Ensures it runs with bash
- ✅ Shows helpful error messages

## After Downloading Updated Script

1. **Download the latest version** from the repository
2. **Convert line endings:**
   ```bash
   dos2unix install_component.sh
   ```
3. **Make executable:**
   ```bash
   chmod +x install_component.sh
   ```
4. **Run with bash:**
   ```bash
   sudo bash install_component.sh
   ```

## Alternative: Create Script Directly on Server

If downloading/editing is causing line ending issues, create the script directly on the server:

```bash
cd /var/www/grimpsa_webserver
nano install_component.sh
# Paste the script content
# Save with Ctrl+X, then Y, then Enter
chmod +x install_component.sh
sudo bash install_component.sh
```

