#!/bin/bash

# Anxipunk CyberPanel Deployment Script
# Run this via SSH

APP_DIR="/home/anxipunk.icu/public_html"
REPO_URL="https://github.com/losing911/storyfactory.git"
USER_GROUP="anxip7694:anxip7694" # Assuming this is the user. If root, use www-data or check owner.

# CyberPanel PHP Fix
export PATH=/usr/local/lsws/lsphp83/bin:$PATH

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
    php artisan key:generate
fi

# Force Update Environment Variables (Ensure MySQL is used)
echo "🔧 Enforcing DB/App Configuration..."
sed -i "s|^APP_URL=.*|APP_URL=http://anxipunk.icu|g" .env
sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|g" .env
sed -i "s|^DB_HOST=.*|DB_HOST=127.0.0.1|g" .env
sed -i "s|^DB_PORT=.*|DB_PORT=3306|g" .env
sed -i "s|^DB_DATABASE=.*|DB_DATABASE=anxi_story|g" .env
sed -i "s|^DB_USERNAME=.*|DB_USERNAME=anxi_admin|g" .env
sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD='J8@^JCFca5pntgmX'|g" .env

# Prompt for API Key (Secure Way)
echo "----------------------------------------------------------------"
echo "⚠️ GÜVENLİK UYARISI: Önceki API Anahtarınız sızdırıldığı için iptal edildi."
echo "Lütfen https://aistudio.google.com/app/apikey adresinden YENİ bir anahtar alın."
echo "----------------------------------------------------------------"
read -p "Lütfen YENİ Gemini API Anahtarınızı yapıştırın ve Enter'a basın: " USER_GEMINI_KEY

if [ -n "$USER_GEMINI_KEY" ]; then
    # Update or Append Key
    if grep -q "GEMINI_API_KEY=" .env; then
        sed -i "s|^GEMINI_API_KEY=.*|GEMINI_API_KEY='$USER_GEMINI_KEY'|g" .env
    else
        echo "GEMINI_API_KEY='$USER_GEMINI_KEY'" >> .env
    fi
    echo "✅ Yeni API Anahtarı Kaydedildi."
fi

echo "----------------------------------------------------------------"
echo "🌐 OpenRouter API (Yedek Zeka)"
echo "Gemini kotası dolarsa Mistral/DeepSeek kullanmak için gereklidir."
echo "Anahtar Alın: https://openrouter.ai/keys"
echo "----------------------------------------------------------------"
read -p "OpenRouter API Anahtarınızı yapıştırın (Varsa): " USER_OPENROUTER_KEY

if [ -n "$USER_OPENROUTER_KEY" ]; then
    if grep -q "OPENROUTER_API_KEY=" .env; then
        sed -i "s|^OPENROUTER_API_KEY=.*|OPENROUTER_API_KEY='$USER_OPENROUTER_KEY'|g" .env
    else
        echo "OPENROUTER_API_KEY='$USER_OPENROUTER_KEY'" >> .env
    fi
    echo "✅ OpenRouter Anahtarı Kaydedildi."
fi

if ! grep -q "DISCORD_WEBHOOK_URL=" .env; then
    echo "DISCORD_WEBHOOK_URL=''" >> .env
fi

echo "----------------------------------------------------------------"
echo "📸 Instagram Entegrasyonu"
echo "Eğer kullanacaksanız bilgileri girin, yoksa Enter ile geçin."
echo "----------------------------------------------------------------"
read -p "Instagram Access Token: " USER_INSTA_TOKEN
read -p "Instagram Business Account ID: " USER_INSTA_ID

if [ -n "$USER_INSTA_TOKEN" ]; then
    if grep -q "INSTAGRAM_ACCESS_TOKEN=" .env; then
        sed -i "s|^INSTAGRAM_ACCESS_TOKEN=.*|INSTAGRAM_ACCESS_TOKEN='$USER_INSTA_TOKEN'|g" .env
    else
        echo "INSTAGRAM_ACCESS_TOKEN='$USER_INSTA_TOKEN'" >> .env
    fi
     if grep -q "INSTAGRAM_BUSINESS_ACCOUNT_ID=" .env; then
        sed -i "s|^INSTAGRAM_BUSINESS_ACCOUNT_ID=.*|INSTAGRAM_BUSINESS_ACCOUNT_ID='$USER_INSTA_ID'|g" .env
    else
        echo "INSTAGRAM_BUSINESS_ACCOUNT_ID='$USER_INSTA_ID'" >> .env
    fi
    echo "✅ Instagram Bilgileri Kaydedildi."
fi

# 4. Permissions (Critical for CyberPanel)
echo "🔒 Fixing Permissions..."
# Try to detect owner of public_html
OWNER=$(stat -c '%U:%G' $APP_DIR)
chown -R $OWNER $APP_DIR
chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

# 5. Install Dependencies
echo "📥 Installing Composer Packages..."
# CyberPanel PHP path might vary, usually /usr/local/lsws/lsphp82/bin/php or just php
composer install --optimize-autoloader --no-dev

# 6. App Key & Configuration
echo "🔑 configuring Application Key..."
php artisan config:clear
php artisan cache:clear

if ! grep -q "APP_KEY=base64" .env; then
    echo "Key missing. Generating..."
    php artisan key:generate --force
fi

php artisan config:clear

# 7. Database Migration
echo "🗄️ Migrating Database..."
php artisan migrate --force

# 7. Storage Link
php artisan storage:link

# 8. Admin Tasks
echo "🗺️ Generating Sitemap..."
php artisan sitemap:generate

# 8. Create Admin User (Security)
echo "👤 Ensuring Admin User Exists..."
php artisan tinker --execute="
\$u = App\Models\User::firstOrNew(['email' => 'admin@anxipunk.icu']);
\$u->name = 'AnxiPunk Prime';
\$u->password = Hash::make('CyberPunk2077!');
\$u->save();
"

# 9. Fix Public Folder Redirect & Force HTTPS & Timeouts
echo "🔧 Setting up Root .htaccess & Timeouts..."

# Create .user.ini for LiteSpeed/PHP adjustments
echo "max_execution_time = 300" > $APP_DIR/.user.ini
echo "upload_max_filesize = 16M" >> $APP_DIR/.user.ini
echo "post_max_size = 16M" >> $APP_DIR/.user.ini
echo "memory_limit = 256M" >> $APP_DIR/.user.ini

cat > $APP_DIR/.htaccess <<EOF
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Increase Timeout
    <IfModule mod_php.c>
        php_value max_execution_time 300
    </IfModule>
    
    # Force HTTPS
    RewriteCond %{HTTPS} !=on
    RewriteRule ^(.*)$ https://%{HTTP_HOST}/\$1 [R=301,L]

    # Redirect to Public
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/\$1 [L,QSA]
</IfModule>
EOF

echo "✅ CyberPanel Deployment Complete!"
