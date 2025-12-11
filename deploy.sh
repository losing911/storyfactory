#!/bin/bash

# Anxipunk Auto-Deployment Script
# Run this on your Ubuntu server

# 1. Variables
REPO_URL="https://github.com/losing911/storyfactory.git"
APP_DIR="/var/www/anxipunk"
DB_NAME="anxy_story"
DB_USER="anxi_admin"
DB_PASS='J8@^JCFca5pntgmX'

echo "🚀 Starting Deployment..."

# 2. System Updates & Dependencies
echo "📦 Installing Dependencies..."
sudo apt update
sudo apt install -y git zip unzip curl nginx mysql-server php8.2 php8.2-fpm php8.2-mysql php8.2-curl php8.2-xml php8.2-mbstring php8.2-zip

# Install Composer
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

# 3. Clone Repository
if [ -d "$APP_DIR" ]; then
    echo "📂 Project exists, pulling latest changes..."
    cd $APP_DIR
    git pull origin main
else
    echo "📂 Cloning repository..."
    git clone $REPO_URL $APP_DIR
    cd $APP_DIR
fi

# 4. Permissions
echo "🔒 Fixing Permissions..."
sudo chown -R www-data:www-data $APP_DIR
sudo chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

# 5. Environment Setup
echo "⚙️ Configuring Environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    # Update .env programmatically
    sed -i "s|APP_URL=http://localhost|APP_URL=http://anxipunk.icu|g" .env
    sed -i "s|DB_DATABASE=laravel|DB_DATABASE=$DB_NAME|g" .env
    sed -i "s|DB_USERNAME=root|DB_USERNAME=$DB_USER|g" .env
    sed -i "s|DB_PASSWORD=|DB_PASSWORD=$DB_PASS|g" .env
    
    # Set default key if empty
    php artisan key:generate
fi

# 6. Install Dependencies
echo "📥 Installing Composer Packages..."
composer install --optimize-autoloader --no-dev

# 7. Database Setup
echo "🗄️ Setting up Database..."
# Create DB if not exists (Requires root access usually, skipping auth if possible or assuming configured)
sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# Run Migrations
php artisan migrate --force

# 8. Web Server (Nginx)
echo "🌐 Configuring Nginx..."
NGINX_CONF="/etc/nginx/sites-available/anxipunk"
sudo tee $NGINX_CONF > /dev/null <<EOF
server {
    listen 80;
    server_name anxipunk.icu www.anxipunk.icu;
    root $APP_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable Site
sudo ln -sf $NGINX_CONF /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

echo "✅ DEPLOYMENT COMPLETE! Visit http://anxipunk.icu"
