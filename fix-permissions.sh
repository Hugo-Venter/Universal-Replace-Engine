#!/bin/bash

# Fix Universal Replace Engine Plugin Permissions
# Run as: sudo bash fix-permissions.sh

PLUGIN_DIR="/var/www/clients/client1/web491/web/wp-content/plugins/universal-replace-engine"

echo "Fixing ownership for Universal Replace Engine plugin..."

# Change ownership to web491:client1
sudo chown -R web491:client1 "$PLUGIN_DIR"

echo "Setting correct file permissions..."

# Directories: 755 (rwxr-xr-x)
find "$PLUGIN_DIR" -type d -exec chmod 755 {} \;

# Files: 644 (rw-r--r--)
find "$PLUGIN_DIR" -type f -exec chmod 644 {} \;

echo "Verifying permissions..."
ls -la "$PLUGIN_DIR"
echo ""
echo "Checking includes directory..."
ls -la "$PLUGIN_DIR/includes"
echo ""
echo "Checking templates directory..."
ls -la "$PLUGIN_DIR/templates"

echo ""
echo "✓ Permissions fixed!"
echo "  - Owner: web491:client1"
echo "  - Directories: 755"
echo "  - Files: 644"
