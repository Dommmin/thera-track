#!/bin/bash

set -e
set -o pipefail

APP_USER="deployer"
APP_GROUP="www-data"
APP_BASE="/home/$APP_USER/laravel"
RELEASES_DIR="$APP_BASE/releases"
SHARED_DIR="$APP_BASE/shared"
CURRENT_LINK="$APP_BASE/current"
NOW=$(date +%Y-%m-%d-%H%M%S)-$(openssl rand -hex 3)
RELEASE_DIR="$RELEASES_DIR/$NOW"
ARCHIVE_NAME="release.tar.gz"

echo "▶️ Create directories..."
mkdir -p "$RELEASES_DIR" "$SHARED_DIR/var"

echo "▶️ Unpacking release..."
mkdir -p "$RELEASE_DIR"
tar -xzf "$APP_BASE/$ARCHIVE_NAME" -C "$RELEASE_DIR"
rm -f "$APP_BASE/$ARCHIVE_NAME"

ln -sf "$SHARED_DIR/.env" "$RELEASE_DIR/.env"

echo "▶️ Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Update symlink first
echo "▶️ Updating current symlink..."
ln -sfn "$RELEASE_DIR" "$CURRENT_LINK"

echo "▶️ Restarting PHP-FPM to apply new code..."
if sudo systemctl restart php8.3-fpm; then
    echo "✅ PHP-FPM restarted successfully"
else
    echo "❌ Failed to restart PHP-FPM!"
    exit 1
fi

echo "▶️ Cleaning old releases (keeping 5 latest)..."
cd "$RELEASES_DIR"
ls -dt */ | tail -n +6 | xargs -r rm -rf

echo "▶️ Restarting Supervisor services..."
sudo supervisorctl restart all

echo "✅ Deployment successful: $NOW"
exit 0
