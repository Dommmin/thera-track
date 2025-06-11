#!/bin/bash

set -e
set -o pipefail

APP_USER="deployer"
APP_GROUP="www-data"
APP_BASE="/home/$APP_USER/symfony"
RELEASES_DIR="$APP_BASE/releases"
SHARED_DIR="$APP_BASE/shared"
CURRENT_LINK="$APP_BASE/current"
NOW=$(date +%Y-%m-%d-%H%M%S)-$(openssl rand -hex 3)
RELEASE_DIR="$RELEASES_DIR/$NOW"
ARCHIVE_NAME="release.tar.gz"

# Function to rollback in case of error
rollback() {
    echo "❌ Deployment failed. Starting rollback..."
    if [ -L "$CURRENT_LINK.backup" ]; then
        mv "$CURRENT_LINK.backup" "$CURRENT_LINK"
        echo "✅ Rollback completed"
    fi
    exit 1
}

# Set trap for error handling
trap rollback ERR

echo "▶️ Starting deployment: $NOW"

echo "▶️ Create directories..."
mkdir -p "$RELEASES_DIR" "$SHARED_DIR/var/cache" "$SHARED_DIR/var/log" "$SHARED_DIR/var/sessions" "$SHARED_DIR/public/uploads"

# Verify archive exists
if [ ! -f "$APP_BASE/$ARCHIVE_NAME" ]; then
    echo "❌ Archive $APP_BASE/$ARCHIVE_NAME not found!"
    exit 1
fi

echo "▶️ Unpacking release..."
mkdir -p "$RELEASE_DIR"
if ! tar -xzf "$APP_BASE/$ARCHIVE_NAME" -C "$RELEASE_DIR"; then
    echo "❌ Failed to extract release archive"
    exit 1
fi
rm -f "$APP_BASE/$ARCHIVE_NAME"

# Verify .env exists in shared directory
if [ ! -f "$SHARED_DIR/.env" ]; then
    echo "❌ .env file not found in shared directory: $SHARED_DIR/.env"
    echo "Please ensure .env file is uploaded to shared directory first"
    exit 1
fi

# Link shared files and directories
echo "▶️ Linking shared files..."
ln -sf "$SHARED_DIR/.env" "$RELEASE_DIR/.env"

# Verify .env link was created
if [ ! -L "$RELEASE_DIR/.env" ]; then
    echo "❌ Failed to create .env symlink"
    exit 1
fi

# Handle var directory
if [ -d "$RELEASE_DIR/var" ]; then
    rm -rf "$RELEASE_DIR/var"
fi
ln -sf "$SHARED_DIR/var" "$RELEASE_DIR/var"

# Handle uploads directory
mkdir -p "$RELEASE_DIR/public"
if [ -d "$RELEASE_DIR/public/uploads" ]; then
    rm -rf "$RELEASE_DIR/public/uploads"
fi
ln -sf "$SHARED_DIR/public/uploads" "$RELEASE_DIR/public/uploads"

# Verify we can access the release directory
cd "$RELEASE_DIR" || {
    echo "❌ Cannot access release directory: $RELEASE_DIR"
    exit 1
}

# Verify Symfony structure
if [ ! -f "bin/console" ]; then
    echo "❌ Symfony console not found in release"
    exit 1
fi

# Make console executable
chmod +x bin/console

# Verify PHP can access the application
echo "▶️ Verifying Symfony installation..."
if ! php bin/console --version >/dev/null 2>&1; then
    echo "❌ Symfony application is not working properly"
    exit 1
fi

# Clear cache and warmup with proper environment
echo "▶️ Clearing cache..."
php bin/console cache:clear --env=prod --no-debug --no-warmup
php bin/console cache:warmup --env=prod --no-debug

# Check if database is available before running migrations
echo "▶️ Checking database connection..."
if php bin/console doctrine:query:sql "SELECT 1" --env=prod >/dev/null 2>&1; then
    echo "▶️ Running database migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction --env=prod
else
    echo "⚠️ Database not available, skipping migrations"
fi

# Backup current symlink BEFORE changing it
echo "▶️ Backing up current symlink..."
if [ -L "$CURRENT_LINK" ]; then
    CURRENT_TARGET=$(readlink "$CURRENT_LINK" 2>/dev/null || echo "unknown")
    echo "Current deployment: $CURRENT_TARGET"
    cp -P "$CURRENT_LINK" "$CURRENT_LINK.backup" 2>/dev/null || true
    echo "✅ Current deployment backed up"
elif [ -e "$CURRENT_LINK" ]; then
    echo "⚠️ $CURRENT_LINK exists but is not a symlink!"
    ls -la "$CURRENT_LINK"
    echo "❌ Current link is not a symlink - this needs manual intervention"
    exit 1
else
    echo "ℹ️ No current symlink found (first deployment?)"
fi

# Debug filesystem and permissions
echo "▶️ Checking filesystem and permissions..."
echo "App base directory permissions:"
ls -la "$APP_BASE/"
echo "Current user: $(whoami)"
echo "Current groups: $(groups)"
echo "Available disk space:"
df -h "$APP_BASE"

# Atomic symlink update with verification
echo "▶️ Updating current symlink..."
echo "Current symlink points to: $(readlink $CURRENT_LINK 2>/dev/null || echo 'none')"
echo "Will update to: $RELEASE_DIR"

TEMP_LINK="$CURRENT_LINK.tmp.$"

# Remove temporary link if it exists
rm -f "$TEMP_LINK"

# Create temporary symlink
if ! ln -sf "$RELEASE_DIR" "$TEMP_LINK"; then
    echo "❌ Failed to create temporary symlink"
    exit 1
fi

# Verify temporary symlink points to correct location
TEMP_TARGET=$(readlink "$TEMP_LINK" 2>/dev/null || echo "FAILED")
if [ "$TEMP_TARGET" != "$RELEASE_DIR" ]; then
    echo "❌ Temporary symlink verification failed"
    echo "Expected: $RELEASE_DIR"
    echo "Actual: $TEMP_TARGET"
    rm -f "$TEMP_LINK"
    exit 1
fi

echo "✅ Temporary symlink created successfully"

# Remove current symlink first (this ensures mv will work)
if [ -L "$CURRENT_LINK" ] || [ -e "$CURRENT_LINK" ]; then
    echo "▶️ Removing current symlink..."
    rm -f "$CURRENT_LINK"
fi

# Move temporary symlink to final location
if ! mv "$TEMP_LINK" "$CURRENT_LINK"; then
    echo "❌ Failed to move temporary symlink to final location"
    # Try to restore if we have a backup
    if [ -L "$CURRENT_LINK.backup" ]; then
        cp -P "$CURRENT_LINK.backup" "$CURRENT_LINK" 2>/dev/null || true
    fi
    rm -f "$TEMP_LINK"
    exit 1
fi

# Final verification
ACTUAL_TARGET=$(readlink "$CURRENT_LINK" 2>/dev/null || echo "FAILED")
if [ "$ACTUAL_TARGET" != "$RELEASE_DIR" ]; then
    echo "❌ Symlink verification failed!"
    echo "Expected: $RELEASE_DIR"
    echo "Actual: $ACTUAL_TARGET"
    echo "Filesystem info:"
    ls -la "$CURRENT_LINK" || true
    ls -la "$APP_BASE/" | grep current || true
    rollback
fi

echo "✅ Symlink updated successfully"

# Optional: Test the deployment
echo "▶️ Testing deployment..."
if ! php "$CURRENT_LINK/bin/console" --version >/dev/null 2>&1; then
    echo "❌ Deployment test failed - application not working"
    rollback
fi

echo "▶️ Cleaning old releases (keeping 3 latest)..."
cd "$RELEASES_DIR"
if ls -dt */ >/dev/null 2>&1; then
    # Keep 3 latest releases
    ls -dt */ | tail -n +4 | while read -r dir; do
        if [ -d "$dir" ]; then
            echo "Removing old release: $dir"
            rm -rf "$dir"
        fi
    done
fi

# Restart Supervisor services if supervisor is installed
echo "▶️ Restarting Supervisor services..."
if command -v supervisorctl >/dev/null 2>&1; then
    if sudo supervisorctl restart all; then
        echo "✅ Supervisor services restarted"
    else
        echo "⚠️ Failed to restart some supervisor services (non-critical)"
    fi
else
    echo "⚠️ Supervisor not found, skipping restart"
fi

# Clean up backup if everything succeeded
rm -f "$CURRENT_LINK.backup"

echo ""
echo "🎉 Deployment successful!"
echo "📍 Release: $NOW"
echo "📂 Deployed to: $RELEASE_DIR"
echo "🔗 Current symlink: $(readlink $CURRENT_LINK)"
echo "🕐 Completed at: $(date)"

exit 0
