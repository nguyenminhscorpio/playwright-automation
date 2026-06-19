#!/bin/bash

# ==============================================================================
# Laravel & SQLite Automated Setup Script for Amazon Linux 2023 (AL2023)
# ==============================================================================
# Usage: sudo ./setup-server.sh [your-domain.com]
# ==============================================================================

# Exit immediately if a command exits with a non-zero status
set -e

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Starting Laravel Setup on Amazon Linux 2023 ===${NC}"

# Ensure script is run as root
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}Error: Please run this script as root or with sudo:${NC}"
  echo "sudo $0 [your-domain.com]"
  exit 1
fi

# Get domain name from argument or prompt
DOMAIN=$1
if [ -z "$DOMAIN" ]; then
  echo -e "${YELLOW}No domain specified as argument.${NC}"
  read -p "Enter your domain name (or press Enter / input '_' to use public IP first): " DOMAIN
fi

if [ -z "$DOMAIN" ] || [ "$DOMAIN" = "_" ]; then
  DOMAIN="_"
  echo -e "${YELLOW}No domain specified. Setting up default Nginx block for IP access.${NC}"
  # Attempt to get public IP
  PUBLIC_IP=$(curl -s --connect-timeout 3 http://169.254.169.254/latest/meta-data/public-ipv4 || curl -s --connect-timeout 3 https://ifconfig.me || echo "your-server-ip")
  APP_URL="http://$PUBLIC_IP"
else
  APP_URL="https://$DOMAIN"
fi

APP_PATH=$(pwd)
echo -e "${GREEN}Application Path detected:${NC} $APP_PATH"
echo -e "${GREEN}Target Domain:${NC} $DOMAIN"

# 1. Update Packages & Install System Utilities
echo -e "${YELLOW}Updating packages and installing system utilities...${NC}"
dnf update -y
dnf install -y git unzip shadow-utils

# 2. Install PHP 8.3 and Extensions
echo -e "${YELLOW}Installing PHP 8.3 and required extensions...${NC}"
dnf install -y \
  php8.3 \
  php8.3-cli \
  php8.3-fpm \
  php8.3-common \
  php8.3-pdo \
  php8.3-xml \
  php8.3-mbstring \
  php8.3-gd \
  php8.3-intl \
  php8.3-opcache \
  php8.3-sodium \
  php8.3-process \
  php8.3-bcmath \
  php8.3-zip \
  sqlite

# Verify PHP installation
php -v

# 3. Install Nginx and Node.js
echo -e "${YELLOW}Installing Nginx and Node.js (for asset compilation)...${NC}"
dnf install -y nginx nodejs20

# 4. Configure PHP-FPM for Nginx
echo -e "${YELLOW}Configuring PHP-FPM...${NC}"
# Set FPM user and group to nginx
sed -i 's/user = apache/user = nginx/g' /etc/php-fpm.d/www.conf
sed -i 's/group = apache/group = nginx/g' /etc/php-fpm.d/www.conf

# Set listen socket socket settings
sed -i 's/listen = 127.0.0.1:9000/listen = \/run\/php-fpm\/www.sock/g' /etc/php-fpm.d/www.conf
sed -i 's/;listen.owner = nobody/listen.owner = nginx/g' /etc/php-fpm.d/www.conf
sed -i 's/;listen.group = nobody/listen.group = nginx/g' /etc/php-fpm.d/www.conf
sed -i 's/;listen.mode = 0660/listen.mode = 0660/g' /etc/php-fpm.d/www.conf

# Start and enable PHP-FPM
systemctl daemon-reload
systemctl enable php-fpm --now

# 5. Install Composer Globally
echo -e "${YELLOW}Installing Composer...${NC}"
if [ ! -f /usr/local/bin/composer ]; then
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
  chmod +x /usr/local/bin/composer
fi
composer --version

# 6. Set Up SQLite Database
echo -e "${YELLOW}Setting up SQLite Database...${NC}"
mkdir -p "$APP_PATH/database"
touch "$APP_PATH/database/database.sqlite"

# 7. Configure Laravel Environment File (.env)
echo -e "${YELLOW}Configuring Laravel environment...${NC}"
if [ ! -f "$APP_PATH/.env" ]; then
  cp "$APP_PATH/.env.example" "$APP_PATH/.env"
  echo ".env created from .env.example"
fi

# Update .env configuration using sed
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' "$APP_PATH/.env"
# Remove any default DB_HOST, DB_PORT etc to prevent confusion
sed -i 's/^DB_HOST=/#DB_HOST=/g' "$APP_PATH/.env"
sed -i 's/^DB_PORT=/#DB_PORT=/g' "$APP_PATH/.env"
sed -i 's/^DB_DATABASE=/#DB_DATABASE=/g' "$APP_PATH/.env"
sed -i 's/^DB_USERNAME=/#DB_USERNAME=/g' "$APP_PATH/.env"
sed -i 's/^DB_PASSWORD=/#DB_PASSWORD=/g' "$APP_PATH/.env"

# Add absolute SQLite path to .env
if ! grep -q "DB_DATABASE=" "$APP_PATH/.env"; then
  echo "DB_DATABASE=$APP_PATH/database/database.sqlite" >> "$APP_PATH/.env"
else
  # Replace existing DB_DATABASE
  sed -i "s|^#\?DB_DATABASE=.*|DB_DATABASE=$APP_PATH/database/database.sqlite|g" "$APP_PATH/.env"
fi

# Ensure APP_ENV is production and APP_DEBUG is false
sed -i 's/^APP_ENV=.*/APP_ENV=production/' "$APP_PATH/.env"
sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' "$APP_PATH/.env"
sed -i "s|^APP_URL=.*|APP_URL=$APP_URL|g" "$APP_PATH/.env"

# 8. Install Dependencies & Build Frontend
echo -e "${YELLOW}Installing dependencies and building assets...${NC}"
# Allow Composer to run as root for system-wide deploy setup
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

# Generate App Key if not set
php artisan key:generate --force

# Run database migrations
php artisan migrate --force

# Install npm packages and build frontend assets using Vite
npm install
npm run build

# 9. Set Storage Permissions
echo -e "${YELLOW}Setting permissions for Nginx...${NC}"
chown -R nginx:nginx "$APP_PATH"
find "$APP_PATH" -type f -exec chmod 664 {} \;
find "$APP_PATH" -type d -exec chmod 775 {} \;
# Ensure storage, bootstrap/cache, and database folders are fully writable by nginx
chmod -R 775 "$APP_PATH/storage"
chmod -R 775 "$APP_PATH/bootstrap/cache"
chmod -R 775 "$APP_PATH/database"
chmod 664 "$APP_PATH/database/database.sqlite"

# 10. Generate Nginx Server Block Configuration
echo -e "${YELLOW}Configuring Nginx server block...${NC}"
cat <<EOF > /etc/nginx/conf.d/vibe-coding.conf
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN;
    root $APP_PATH/public;

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

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Start and enable Nginx
systemctl enable nginx --now
systemctl restart nginx

# 11. Install Certbot for Let's Encrypt SSL
echo -e "${YELLOW}Installing Certbot for SSL...${NC}"
dnf install -y python3-pip
python3 -m venv /opt/certbot/
/opt/certbot/bin/pip install --upgrade pip
/opt/certbot/bin/pip install certbot certbot-nginx
if [ ! -f /usr/bin/certbot ]; then
  ln -s /opt/certbot/bin/certbot /usr/bin/certbot
fi

echo -e "${GREEN}=== Setup Completed Successfully! ===${NC}"
if [ "$DOMAIN" = "_" ]; then
  echo -e "${GREEN}The application has been configured to respond directly to the server's public IP address.${NC}"
  echo -e "${YELLOW}To migrate to a custom domain and configure HTTPS later:${NC}"
  echo -e "  1. Point your domain A record to this server's Elastic IP."
  echo -e "  2. Edit ${YELLOW}/etc/nginx/conf.d/vibe-coding.conf${NC} and update 'server_name _;' to 'server_name yourdomain.com;'."
  echo -e "  3. Edit ${YELLOW}$APP_PATH/.env${NC} and update 'APP_URL=http://$PUBLIC_IP' to 'APP_URL=https://yourdomain.com'."
  echo -e "  4. Clear Laravel config cache: ${GREEN}php artisan config:clear${NC}"
  echo -e "  5. Run Certbot to generate and install SSL: ${GREEN}sudo certbot --nginx -d yourdomain.com${NC}"
else
  echo -e "${YELLOW}To obtain your free SSL certificate, please run the following command:${NC}"
  echo -e "  ${GREEN}sudo certbot --nginx -d $DOMAIN${NC}"
  echo -e "${YELLOW}Ensure that your DNS A record is pointed to this server's Elastic IP before running certbot.${NC}"
fi
