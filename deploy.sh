#!/bin/bash

# RSAZ Medical API Deployment Script for CentOS with aaPanel
# Usage: bash deploy.sh

set -e

echo "🚀 RSAZ Medical API Deployment Script"
echo "===================================="

# Configuration
DOMAIN="api.yourdomain.com"
PROJECT_DIR="/www/wwwroot/$DOMAIN"
PHP_VERSION="81"
DB_NAME="rsaz_api_production"
DB_USER="rsaz_user"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root (use sudo)"
    exit 1
fi

print_status "Starting deployment process..."

# Step 1: Check if aaPanel is installed
print_status "Checking aaPanel installation..."
if ! command -v bt &> /dev/null; then
    print_error "aaPanel not found. Please install aaPanel first."
    echo "Run: yum install -y wget && wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh && bash install.sh aapanel"
    exit 1
fi

# Step 2: Create project directory
print_status "Creating project directory: $PROJECT_DIR"
mkdir -p $PROJECT_DIR
cd $PROJECT_DIR

# Step 3: Backup existing files (if any)
if [ "$(ls -A $PROJECT_DIR)" ]; then
    print_warning "Directory not empty. Creating backup..."
    mv $PROJECT_DIR /www/backup/$(basename $PROJECT_DIR)_backup_$(date +%Y%m%d_%H%M%S)
    mkdir -p $PROJECT_DIR
    cd $PROJECT_DIR
fi

# Step 4: Clone or copy project files
print_status "Deploying application files..."
if [ -n "$1" ] && [ "$1" = "git" ]; then
    read -p "Enter Git repository URL: " REPO_URL
    git clone $REPO_URL .
else
    print_warning "Please upload your project files to $PROJECT_DIR manually"
    read -p "Press Enter after uploading files..."
fi

# Step 5: Install Composer dependencies
print_status "Installing Composer dependencies..."
if ! command -v composer &> /dev/null; then
    print_status "Installing Composer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm composer-setup.php
fi

composer install --optimize-autoloader --no-dev

# Step 6: Set up environment file
print_status "Setting up environment configuration..."
if [ ! -f .env ]; then
    if [ -f .env.production ]; then
        cp .env.production .env
        print_status "Copied .env.production to .env"
    else
        print_warning "No .env file found. Creating from template..."
        cat > .env << EOF
APP_NAME="RSAZ Medical API"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://$DOMAIN

DB_CONNECTION=mysql
DB_HOST=192.168.0.3
DB_PORT=3939
DB_DATABASE=rsaz_sik
DB_USERNAME=herd
DB_PASSWORD=HewlettPackard11@@

API_TOKEN_SECRET=

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

LOG_CHANNEL=daily
LOG_LEVEL=error
EOF
    fi
fi

# Step 7: Generate application key
print_status "Generating application key..."
php artisan key:generate --force

# Step 8: Generate API token
print_status "Generating secure API token..."
if php artisan list | grep -q "generate:api-token"; then
    API_TOKEN=$(php artisan generate:api-token --length=64 --quiet)
    sed -i "s/API_TOKEN_SECRET=.*/API_TOKEN_SECRET=$API_TOKEN/" .env
    print_status "API token generated and saved to .env"
else
    print_warning "API token generator not found. Please set API_TOKEN_SECRET manually."
fi

# Step 9: Set proper permissions
print_status "Setting file permissions..."
chown -R www:www $PROJECT_DIR
find $PROJECT_DIR -type f -exec chmod 644 {} \;
find $PROJECT_DIR -type d -exec chmod 755 {} \;
chmod -R 775 $PROJECT_DIR/storage
chmod -R 775 $PROJECT_DIR/bootstrap/cache
chmod 600 $PROJECT_DIR/.env

# Step 10: Clear and cache Laravel configurations
print_status "Optimizing Laravel..."
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan view:clear

# Step 11: Test database connection
print_status "Testing database connection..."
if php artisan list | grep -q "test:db"; then
    if php artisan test:db; then
        print_status "Database connection successful!"
    else
        print_warning "Database connection failed. Please check your configuration."
    fi
else
    print_warning "Database test command not found. Please test manually."
fi

# Step 12: Create Nginx configuration
print_status "Creating Nginx configuration template..."
cat > /tmp/nginx_api_config.txt << 'EOF'
server {
    listen 80;
    listen 443 ssl http2;
    server_name DOMAIN_PLACEHOLDER;
    index index.php index.html;
    root PROJECT_DIR_PLACEHOLDER/public;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/tmp/php-cgi-PHP_VERSION_PLACEHOLDER.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.(env|git) {
        deny all;
    }
    
    location /api {
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'Authorization, Content-Type' always;
        
        if ($request_method = 'OPTIONS') {
            add_header 'Access-Control-Max-Age' 1728000;
            add_header 'Content-Length' 0;
            return 204;
        }
        
        try_files $uri $uri/ /index.php?$query_string;
    }
}
EOF

sed -i "s/DOMAIN_PLACEHOLDER/$DOMAIN/g" /tmp/nginx_api_config.txt
sed -i "s|PROJECT_DIR_PLACEHOLDER|$PROJECT_DIR|g" /tmp/nginx_api_config.txt
sed -i "s/PHP_VERSION_PLACEHOLDER/$PHP_VERSION/g" /tmp/nginx_api_config.txt

print_status "Nginx configuration saved to /tmp/nginx_api_config.txt"
print_warning "Please apply this configuration in aaPanel manually."

# Step 13: Create systemd service for queue worker (optional)
print_status "Creating queue worker service..."
cat > /etc/systemd/system/rsaz-api-worker.service << EOF
[Unit]
Description=RSAZ API Queue Worker
After=network.target

[Service]
Type=simple
User=www
WorkingDirectory=$PROJECT_DIR
ExecStart=/usr/bin/php $PROJECT_DIR/artisan queue:work --sleep=3 --tries=3 --timeout=60
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
print_status "Queue worker service created (not started automatically)"

# Step 14: Create backup script
print_status "Creating backup script..."
cat > /usr/local/bin/rsaz-api-backup.sh << EOF
#!/bin/bash
BACKUP_DIR="/www/backup/rsaz-api"
PROJECT_DIR="$PROJECT_DIR"
DATE=\$(date +%Y%m%d_%H%M%S)

mkdir -p \$BACKUP_DIR

# Backup files
tar -czf \$BACKUP_DIR/files_\$DATE.tar.gz -C \$(dirname \$PROJECT_DIR) \$(basename \$PROJECT_DIR)

# Backup database (if local)
# mysqldump -u root -p$DB_NAME > \$BACKUP_DIR/database_\$DATE.sql

# Keep only last 7 backups
find \$BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
find \$BACKUP_DIR -name "*.sql" -mtime +7 -delete

echo "Backup completed: \$DATE"
EOF

chmod +x /usr/local/bin/rsaz-api-backup.sh
print_status "Backup script created at /usr/local/bin/rsaz-api-backup.sh"

# Step 15: Final tests and information
print_status "Running final tests..."

# Test API health endpoint
if curl -f -s http://localhost/api/health > /dev/null 2>&1; then
    print_status "Health endpoint responding!"
else
    print_warning "Health endpoint not responding. Check Nginx configuration."
fi

# Display deployment information
echo ""
echo "🎉 Deployment completed!"
echo "======================="
echo "Project Directory: $PROJECT_DIR"
echo "Domain: $DOMAIN"
echo "API Base URL: https://$DOMAIN/api"
echo "Documentation: https://$DOMAIN/api-documentation.html"
echo ""
echo "📋 Next Steps:"
echo "1. Configure domain and SSL in aaPanel"
echo "2. Apply Nginx configuration from /tmp/nginx_api_config.txt"
echo "3. Test API endpoints"
echo "4. Set up monitoring and backups"
echo ""
echo "🔧 Useful Commands:"
echo "Test database: cd $PROJECT_DIR && php artisan test:db"
echo "Generate token: cd $PROJECT_DIR && php artisan generate:api-token"
echo "View logs: tail -f $PROJECT_DIR/storage/logs/laravel.log"
echo "Create backup: /usr/local/bin/rsaz-api-backup.sh"
echo ""
echo "📱 API Endpoints:"
echo "Health Check: https://$DOMAIN/api/health"
echo "Generate Token: POST https://$DOMAIN/api/token/generate"
echo "Check Token: GET https://$DOMAIN/api/token/check"
echo ""

print_status "Deployment script completed successfully! 🚀"
