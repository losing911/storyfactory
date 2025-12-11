#!/bin/bash

# Anxipunk CyberPanel Deployment Script
# Run this via SSH

APP_DIR="/home/anxipunk.icu/public_html"
REPO_URL="https://github.com/losing911/storyfactory.git"
USER_GROUP="anxip7694:anxip7694" # Assuming this is the user. If root, use www-data or check owner.

echo "🚀 Starting CyberPanel Deployment..."

# 1. Cleanup Default Files (index.html etc)
if [ -f "$APP_DIR/index.html" ]; then
    echo "🧹 Removing default index.html..."
    rm "$APP_DIR/index.html"
fi

# 2. Clone/Pull Repo
if [ -d "$APP_DIR/.git" ]; then
    echo "📂 Pulling latest changes..."
    cd $APP_DIR
    git reset --hard
    git pull origin main
else
    echo "📂 Cloning repository..."
    # Backup existing public_html content just in case
    # mv $APP_DIR $APP_DIR_BAK 
    # git clone $REPO_URL $APP_DIR
    # CyberPanel creates the dir, so we might need to clone into dot and mv
    git clone $REPO_URL $APP_DIR/temp_clone
    mv $APP_DIR/temp_clone/* $APP_DIR/
    mv $APP_DIR/temp_clone/.* $APP_DIR/ 2>/dev/null
    rm -rf $APP_DIR/temp_clone
    cd $APP_DIR
fi

# 3. Environment Setup
echo "⚙️ Configuring .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    # Database (CyberPanel DB info required here)
    # We use sed to replace placeholders. Ideally user edits this manually or we prompt.
    sed -i "s|APP_URL=http://localhost|APP_URL=http://anxipunk.icu|g" .env
    sed -i "s|DB_DATABASE=laravel|DB_DATABASE=anxy_story|g" .env
    sed -i "s|DB_USERNAME=root|DB_USERNAME=anxi_admin|g" .env
    sed -i "s|DB_PASSWORD=|DB_PASSWORD=dElfin2015d|g" .env # Password provided by user
    
    php artisan key:generate
fi

# 4. Permissions (Critical for CyberPanel)
echo "🔒 Fixing Permissions..."
# Try to detect owner of public_html
OWNER=$(stat -c '%U:%G' $APP_DIR)
chown -R $OWNER $APP_DIR
chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

# 5. Install Dependencies
echo "📥 Installing Composer Packages..."
composer install --optimize-autoloader --no-dev

# 6. Ensure App Key Exists (Fix for MissingAppKeyException)
if ! grep -q "APP_KEY=base64" .env; then
    echo "🔑 Generating Application Key..."
    php artisan key:generate --force
fi

# Clear Cache to ensure .env is read
php artisan config:clear
php artisan cache:clear

# 7. Database Migration
echo "🗄️ Migrating Database..."
php artisan migrate --force

# 7. Storage Link
php artisan storage:link

# 8. Fix Public Folder Redirect (CyberPanel 404 Fix)
echo "🔧 Setting up Root .htaccess..."
cat > $APP_DIR/.htaccess <<EOF
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/\$1 [L,QSA]
</IfModule>
EOF

echo "✅ CyberPanel Deployment Complete!"
