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

echo "▶️ Create directories..."
mkdir -p "$RELEASES_DIR" "$SHARED_DIR/var/cache" "$SHARED_DIR/var/log" "$SHARED_DIR/var/sessions" "$SHARED_DIR/public/uploads"

echo "▶️ Unpacking release..."
mkdir -p "$RELEASE_DIR"
tar -xzf "$APP_BASE/$ARCHIVE_NAME" -C "$RELEASE_DIR"
rm -f "$APP_BASE/$ARCHIVE_NAME"

# Link shared files and directories
echo "▶️ Linking shared files..."
ln -sf "$SHARED_DIR/.env" "$RELEASE_DIR/.env"

if [ -d "$RELEASE_DIR/var" ]; then
    rm -rf "$RELEASE_DIR/var"
fi
ln -sf "$SHARED_DIR/var" "$RELEASE_DIR/var"

mkdir -p "$RELEASE_DIR/public"
if [ -d "$RELEASE_DIR/public/uploads" ]; then
    rm -rf "$RELEASE_DIR/public/uploads"
fi
ln -sf "$SHARED_DIR/public/uploads" "$RELEASE_DIR/public/uploads"

# Clear cache and warmup with proper environment
echo "▶️ Clearing cache..."
cd "$RELEASE_DIR"

# Upewnij się, że bin/console jest wykonywalny
chmod +x bin/console

# Wyczyść cache z właściwym środowiskiem
php bin/console cache:clear --env=prod --no-debug --no-warmup
php bin/console cache:warmup --env=prod --no-debug

# Set permissions on newly created cache
chown -R $APP_USER:$APP_GROUP "$SHARED_DIR/var/cache"
chmod -R 775 "$SHARED_DIR/var/cache"

# Check if database is available before running migrations
echo "▶️ Checking database connection..."
if php bin/console doctrine:query:sql "SELECT 1" --env=prod >/dev/null 2>&1; then
    echo "▶️ Running database migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction --env=prod
else
    echo "⚠️ Database not available, skipping migrations"
fi

# Atomic symlink update
echo "▶️ Updating current symlink..."
TEMP_LINK="$CURRENT_LINK.tmp.$$"
ln -sf "$RELEASE_DIR" "$TEMP_LINK"
mv "$TEMP_LINK" "$CURRENT_LINK"

#echo "▶️ Restarting PHP-FPM to apply new code..."
#if sudo systemctl restart php8.3-fpm; then
#    echo "✅ PHP-FPM restarted successfully"
#else
#    echo "❌ Failed to restart PHP-FPM!"
#    exit 1
#fi

# Backup current symlink for potential rollback
cp -P "$CURRENT_LINK" "$CURRENT_LINK.backup" 2>/dev/null || true

echo "▶️ Cleaning old releases (keeping 5 latest)..."
cd "$RELEASES_DIR"
if ls -dt */ >/dev/null 2>&1; then
    ls -dt */ | tail -n +6 | xargs -r rm -rf
fi

# Restart Supervisor services if supervisor is installed
echo "▶️ Restarting Supervisor services..."
if command -v supervisorctl >/dev/null 2>&1; then
    sudo supervisorctl restart all || echo "⚠️ Failed to restart supervisor services"
else
    echo "⚠️ Supervisor not found, skipping restart"
fi

echo "✅ Deployment successful: $NOW"
echo "📍 New release deployed to: $RELEASE_DIR"
echo "🔗 Current symlink points to: $(readlink $CURRENT_LINK)"

exit 0
