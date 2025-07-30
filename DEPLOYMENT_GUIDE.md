# 🚀 Deploy RSAZ Medical Records API to CentOS with aaPanel

Panduan lengkap untuk deploy Laravel API ke server CentOS menggunakan aaPanel sebagai control panel.

## 📋 Prerequisites

### Server Requirements
- **OS**: CentOS 7/8 atau Rocky Linux 8/9
- **RAM**: Minimal 2GB (Recommended 4GB+)
- **Storage**: Minimal 20GB free space
- **PHP**: 8.1 atau 8.2
- **MySQL**: 8.0+
- **Web Server**: Nginx atau Apache
- **SSL**: Let's Encrypt (Recommended)

### Local Requirements
- Git untuk upload code
- FTP/SFTP client (FileZilla, WinSCP)
- SSH client (PuTTY, Terminal)

## 🔧 Step 1: Server Setup & aaPanel Installation

### 1.1 Connect to Server
```bash
ssh root@your-server-ip
```

### 1.2 Update System
```bash
# CentOS/RHEL
yum update -y

# Rocky Linux/AlmaLinux
dnf update -y
```

### 1.3 Install aaPanel
```bash
# Download and install aaPanel
yum install -y wget && wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh && bash install.sh aapanel
```

### 1.4 aaPanel Setup
1. Setelah instalasi selesai, catat:
   - **Panel URL**: `http://your-server-ip:7800`
   - **Username**: (tampil di terminal)
   - **Password**: (tampil di terminal)

2. Login ke aaPanel dashboard
3. Install LNMP Stack:
   - **Nginx**: Latest version
   - **MySQL**: 8.0
   - **PHP**: 8.1 atau 8.2
   - **phpMyAdmin**: Latest (optional)

## 🗄️ Step 2: Database Setup

### 2.1 Create Database via aaPanel
1. Go to **Database** → **MySQL**
2. Click **Add Database**
3. Fill database details:
   - **Database Name**: `rsaz_api_production`
   - **Username**: `rsaz_user`
   - **Password**: `secure_password_here`
   - **Access**: `localhost` or `%` (untuk remote access)

### 2.2 Import Database Structure
```bash
# Connect to MySQL
mysql -u rsaz_user -p rsaz_api_production

# Or use phpMyAdmin via aaPanel
```

**Option 1: Via phpMyAdmin**
1. Access phpMyAdmin through aaPanel
2. Select `rsaz_api_production` database
3. Import database schema jika ada

**Option 2: Via SSH**
```bash
# If you have SQL dump
mysql -u rsaz_user -p rsaz_api_production < database_backup.sql
```

### 2.3 Configure Remote Database Access (if needed)
Jika database ada di server terpisah (192.168.0.3:3939):
1. Pastikan firewall allow port 3939
2. Configure MySQL untuk accept remote connections
3. Test connection dari server API

## 🌐 Step 3: Domain & SSL Setup

### 3.1 Add Domain
1. Go to **Website** → **Site**
2. Click **Add Site**
3. Fill details:
   - **Domain**: `api.yourdomain.com`
   - **Document Root**: `/www/wwwroot/api.yourdomain.com`
   - **PHP Version**: 8.1 atau 8.2

### 3.2 SSL Certificate
1. Go to **Website** → **Site** → **Settings** (domain Anda)
2. Go to **SSL** tab
3. Choose **Let's Encrypt**
4. Click **Apply**
5. Enable **Force HTTPS**

## 📦 Step 4: Deploy Laravel Application

### 4.1 Upload Code
**Option 1: Via Git (Recommended)**
```bash
# SSH to server
ssh root@your-server-ip

# Navigate to web directory
cd /www/wwwroot/api.yourdomain.com

# Remove default files
rm -rf *

# Clone repository
git clone https://github.com/yourusername/rsaz-medical-api.git .

# Or upload via FTP/SFTP
```

**Option 2: Via File Manager**
1. Use aaPanel **File Manager**
2. Navigate to `/www/wwwroot/api.yourdomain.com`
3. Upload project files via zip upload

### 4.2 Set Permissions
```bash
# Set ownership to web user
chown -R www:www /www/wwwroot/api.yourdomain.com

# Set proper permissions
find /www/wwwroot/api.yourdomain.com -type f -exec chmod 644 {} \;
find /www/wwwroot/api.yourdomain.com -type d -exec chmod 755 {} \;

# Laravel specific permissions
chmod -R 775 /www/wwwroot/api.yourdomain.com/storage
chmod -R 775 /www/wwwroot/api.yourdomain.com/bootstrap/cache
```

### 4.3 Install Composer Dependencies
```bash
# Install Composer if not available
cd /www/wwwroot/api.yourdomain.com
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Install dependencies
composer install --optimize-autoloader --no-dev
```

## ⚙️ Step 5: Laravel Configuration

### 5.1 Environment Configuration
```bash
# Copy environment file
cp .env.production .env

# Or create new .env file
nano .env
```

**Production .env Configuration:**
```env
APP_NAME="RSAZ Medical API"
APP_ENV=production
APP_KEY=base64:GENERATE_NEW_KEY_HERE
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=192.168.0.3
DB_PORT=3939
DB_DATABASE=rsaz_sik
DB_USERNAME=herd
DB_PASSWORD=HewlettPackard11@@

# API Token (Generate secure random string)
API_TOKEN_SECRET=GENERATE_SECURE_64_CHAR_RANDOM_STRING_HERE

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error
```

### 5.2 Generate Application Key
```bash
php artisan key:generate
```

### 5.3 Generate Secure API Token
```bash
# Generate secure API token
php artisan generate:api-token --length=64

# Copy the generated token to .env file
```

### 5.4 Cache Configuration
```bash
# Clear and cache config
php artisan config:clear
php artisan config:cache

# Cache routes (optional)
php artisan route:cache

# Clear views cache
php artisan view:clear
```

### 5.5 Test Database Connection
```bash
php artisan test:db
```

## 🌐 Step 6: Web Server Configuration

### 6.1 Nginx Configuration (via aaPanel)
1. Go to **Website** → **Site** → **Settings** (your domain)
2. Go to **Config Files** → **Nginx**
3. Replace configuration:

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name api.yourdomain.com;
    index index.php index.html index.htm default.php default.htm default.html;
    root /www/wwwroot/api.yourdomain.com/public;
    
    # SSL Configuration (auto-generated by aaPanel)
    ssl_certificate /www/server/panel/vhost/cert/api.yourdomain.com/fullchain.pem;
    ssl_certificate_key /www/server/panel/vhost/cert/api.yourdomain.com/privkey.pem;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # Laravel configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/tmp/php-cgi-81.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }
    
    # Hide sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ /\.env {
        deny all;
    }
    
    # API rate limiting (optional)
    location /api/ {
        limit_req zone=api burst=100 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # CORS headers for API
    location /api {
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization' always;
        
        if ($request_method = 'OPTIONS') {
            add_header 'Access-Control-Max-Age' 1728000;
            add_header 'Content-Type' 'text/plain; charset=utf-8';
            add_header 'Content-Length' 0;
            return 204;
        }
        
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

### 6.2 PHP Configuration
1. Go to **Software Store** → **PHP 8.1** → **Settings**
2. **Install Extensions:**
   - `mysqli`
   - `pdo_mysql`
   - `curl`
   - `json`
   - `mbstring`
   - `openssl`
   - `tokenizer`
   - `xml`
   - `zip`

3. **PHP.ini Configuration:**
```ini
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
max_input_vars = 3000
```

## 🔒 Step 7: Security Configuration

### 7.1 Firewall Setup
```bash
# Install firewalld if not installed
systemctl start firewalld
systemctl enable firewalld

# Allow necessary ports
firewall-cmd --permanent --add-port=80/tcp
firewall-cmd --permanent --add-port=443/tcp
firewall-cmd --permanent --add-port=7800/tcp  # aaPanel (optional, bisa diblok setelah setup)
firewall-cmd --permanent --add-port=22/tcp    # SSH

# Reload firewall
firewall-cmd --reload
```

### 7.2 aaPanel Security
1. Go to **Panel Settings** → **Security**
2. Change default port from 7800 ke port custom
3. Enable **BasicAuth** untuk extra security
4. Set **Allowed IP** jika akses terbatas

### 7.3 Application Security
```bash
# Set proper file permissions (repeat if needed)
chmod -R 755 /www/wwwroot/api.yourdomain.com
chmod -R 775 /www/wwwroot/api.yourdomain.com/storage
chmod -R 775 /www/wwwroot/api.yourdomain.com/bootstrap/cache
chmod 600 /www/wwwroot/api.yourdomain.com/.env
```

## 🧪 Step 8: Testing & Verification

### 8.1 Basic API Test
```bash
# Test health endpoint
curl https://api.yourdomain.com/api/health

# Expected response:
{
    "success": true,
    "message": "API is running",
    "timestamp": "2025-07-30T12:00:00.000000Z",
    "database": "Connected to: rsaz_sik"
}
```

### 8.2 Token Generation Test
```bash
# Generate token
curl -X POST https://api.yourdomain.com/api/token/generate \
  -H "Content-Type: application/json" \
  -d '{"admin_key": "rsaz_admin_2025"}'
```

### 8.3 Protected Endpoint Test
```bash
# Test with generated token
curl https://api.yourdomain.com/api/jns-perawatan \
  -H "Authorization: Bearer YOUR_GENERATED_TOKEN"
```

### 8.4 Laravel Artisan Commands
```bash
# Test database connection
php artisan test:db

# Test API endpoints
php artisan test:api

# Generate new API token
php artisan generate:api-token
```

## 📊 Step 9: Monitoring & Maintenance

### 9.1 Log Monitoring
```bash
# Laravel logs
tail -f /www/wwwroot/api.yourdomain.com/storage/logs/laravel.log

# Nginx access logs
tail -f /www/wwwroot/logs/api.yourdomain.com.log

# Nginx error logs  
tail -f /www/wwwroot/logs/api.yourdomain.com.error.log
```

### 9.2 Performance Monitoring via aaPanel
1. Go to **Monitoring** → **Load Status**
2. Monitor CPU, RAM, Disk usage
3. Check **Process Manager** untuk PHP processes

### 9.3 Backup Setup
1. Go to **Backup** → **Site Backup**
2. Set automatic backup schedule:
   - **Files**: Weekly
   - **Database**: Daily
   - **Retention**: 30 days

## 🚀 Step 10: Production Optimizations

### 10.1 PHP OPcache
```bash
# Enable OPcache via aaPanel
# Go to Software Store → PHP 8.1 → Settings → Install Extensions → OPcache
```

### 10.2 Laravel Optimizations
```bash
# Cache everything for production
php artisan config:cache
php artisan route:cache  
php artisan view:cache

# Optimize composer autoloader
composer dump-autoload --optimize
```

### 10.3 Database Optimizations
1. Via aaPanel **Database** → **MySQL** → **Performance**
2. Enable query cache
3. Optimize innodb_buffer_pool_size

## 📱 Step 11: API Documentation Deployment

### 11.1 Make Documentation Accessible
```bash
# Documentation will be available at:
https://api.yourdomain.com/api-documentation.html

# Postman collection at:
https://api.yourdomain.com/RSAZ-Medical-API.postman_collection.json
```

### 11.2 Custom Documentation Domain (Optional)
Create subdomain `docs.yourdomain.com` pointing to documentation files.

## 🔧 Troubleshooting

### Common Issues:

#### 1. **Permission Denied Errors**
```bash
chown -R www:www /www/wwwroot/api.yourdomain.com
chmod -R 775 storage bootstrap/cache
```

#### 2. **Database Connection Error**
- Check .env database credentials
- Verify database server is accessible
- Test with `php artisan test:db`

#### 3. **SSL Certificate Issues**
- Renew via aaPanel SSL section
- Check domain DNS settings
- Verify firewall allows port 443

#### 4. **API Returns 500 Error**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Enable debug mode temporarily
APP_DEBUG=true in .env (remember to disable after)
```

#### 5. **Token Generation Fails**
- Check API_TOKEN_SECRET in .env
- Verify admin_key in request
- Check PHP extensions (openssl, json)

## 📞 Support & Maintenance

### Regular Maintenance Tasks:
- **Weekly**: Check logs for errors
- **Monthly**: Update dependencies (`composer update`)
- **Quarterly**: Review security settings
- **Annually**: Renew SSL certificates (auto with Let's Encrypt)

### Monitoring URLs:
- **API Health**: `https://api.yourdomain.com/api/health`
- **Documentation**: `https://api.yourdomain.com/api-documentation.html`
- **aaPanel**: `https://your-server-ip:custom-port`

---

## ✅ Deployment Checklist

- [ ] Server setup dengan aaPanel
- [ ] Database created dan configured
- [ ] Domain dan SSL configured
- [ ] Laravel code uploaded
- [ ] Composer dependencies installed
- [ ] .env file configured dengan production settings
- [ ] Application key generated
- [ ] API token generated
- [ ] File permissions set correctly
- [ ] Nginx/Apache configured
- [ ] PHP extensions installed
- [ ] Firewall configured
- [ ] Health check API working
- [ ] Token generation working
- [ ] Protected endpoints working
- [ ] Documentation accessible
- [ ] Backup configured
- [ ] Monitoring setup

**Deployment Complete! 🎉**

Your RSAZ Medical Records API is now live at: `https://api.yourdomain.com`
