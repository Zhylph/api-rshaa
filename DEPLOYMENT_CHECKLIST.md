# 📋 RSAZ Medical API - Deployment Checklist

## Pre-Deployment Checklist ✅

### Server Requirements
- [ ] CentOS 7/8 atau Rocky Linux 8/9
- [ ] RAM minimal 2GB (recommended 4GB+)
- [ ] Storage minimal 20GB free space
- [ ] Root access ke server
- [ ] Domain name ready (contoh: api.yourdomain.com)

### Local Preparation
- [ ] Project code ready di local
- [ ] Database credentials confirmed
- [ ] FTP/SSH client installed
- [ ] Git repository setup (optional)

---

## Phase 1: Server Setup ⚙️

### aaPanel Installation
- [ ] Connect to server via SSH
- [ ] Update system packages
- [ ] Install aaPanel
- [ ] Note aaPanel login credentials
- [ ] Access aaPanel dashboard

### LNMP Stack Installation
- [ ] Install Nginx (latest)
- [ ] Install MySQL 8.0
- [ ] Install PHP 8.1 atau 8.2
- [ ] Install phpMyAdmin (optional)

---

## Phase 2: Database Configuration 🗄️

### Database Setup
- [ ] Create database: `rsaz_api_production`
- [ ] Create database user: `rsaz_user`
- [ ] Set secure password
- [ ] Grant proper permissions
- [ ] Test database connection

### Remote Database (if applicable)
- [ ] Configure firewall for remote access
- [ ] Test connection to 192.168.0.3:3939
- [ ] Verify database rsaz_sik accessible

---

## Phase 3: Domain & SSL 🌐

### Domain Configuration
- [ ] Add domain to aaPanel
- [ ] Set document root: `/www/wwwroot/api.yourdomain.com`
- [ ] Select PHP version 8.1/8.2
- [ ] Configure DNS A record

### SSL Certificate
- [ ] Generate Let's Encrypt certificate
- [ ] Enable Force HTTPS
- [ ] Test SSL certificate validity
- [ ] Verify HTTPS redirect works

---

## Phase 4: Application Deployment 📦

### Code Upload
- [ ] Upload via Git clone OR
- [ ] Upload via FTP/SFTP OR
- [ ] Upload via aaPanel File Manager
- [ ] Verify all files uploaded correctly

### Dependencies & Configuration
- [ ] Install Composer (if not available)
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Copy `.env.production` to `.env`
- [ ] Generate application key: `php artisan key:generate`
- [ ] Generate secure API token: `php artisan generate:api-token`
- [ ] Update .env with production settings

### File Permissions
- [ ] Set ownership: `chown -R www:www /www/wwwroot/api.yourdomain.com`
- [ ] Set file permissions: `find . -type f -exec chmod 644 {} \;`
- [ ] Set directory permissions: `find . -type d -exec chmod 755 {} \;`
- [ ] Set Laravel permissions: `chmod -R 775 storage bootstrap/cache`
- [ ] Secure .env file: `chmod 600 .env`

---

## Phase 5: Web Server Configuration 🌐

### PHP Configuration
- [ ] Install PHP extensions: mysqli, pdo_mysql, curl, json, mbstring, openssl, tokenizer, xml, zip
- [ ] Configure PHP.ini: memory_limit=256M, upload_max_filesize=50M
- [ ] Verify PHP-FPM running

### Nginx Configuration
- [ ] Configure Laravel-friendly Nginx config
- [ ] Add security headers
- [ ] Configure CORS for API endpoints
- [ ] Hide sensitive files (.env, .git)
- [ ] Test Nginx configuration syntax
- [ ] Reload Nginx

---

## Phase 6: Security Setup 🔒

### Firewall Configuration
- [ ] Enable firewalld
- [ ] Allow port 80 (HTTP)
- [ ] Allow port 443 (HTTPS)
- [ ] Allow port 22 (SSH)
- [ ] Optional: Allow custom aaPanel port
- [ ] Reload firewall rules

### aaPanel Security
- [ ] Change default aaPanel port
- [ ] Enable BasicAuth for aaPanel
- [ ] Set allowed IP addresses (optional)
- [ ] Disable unused services

### Application Security
- [ ] Verify .env file permissions (600)
- [ ] Check sensitive directories are protected
- [ ] Validate API token security
- [ ] Test unauthorized access blocked

---

## Phase 7: Testing & Verification 🧪

### Basic Functionality Tests
- [ ] Health check: `curl https://api.yourdomain.com/api/health`
- [ ] Token generation: `POST /api/token/generate`
- [ ] Token validation: `GET /api/token/check`
- [ ] Database connection: `php artisan test:db`

### API Endpoint Tests
- [ ] Test pegawai endpoint with NIK parameter
- [ ] Test rawat-inap-dr with bulan/tahun parameters
- [ ] Test rawat-jl-dr with bulan/tahun parameters
- [ ] Test jns-perawatan-inap endpoint
- [ ] Test jns-perawatan endpoint
- [ ] Verify authentication required for protected endpoints

### Documentation Access
- [ ] API documentation: `https://api.yourdomain.com/api-documentation.html`
- [ ] Postman collection: `https://api.yourdomain.com/RSAZ-Medical-API.postman_collection.json`

---

## Phase 8: Performance & Monitoring 📊

### Laravel Optimizations
- [ ] Cache configuration: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Cache views: `php artisan view:cache`
- [ ] Optimize Composer autoloader

### PHP Optimizations
- [ ] Enable OPcache
- [ ] Configure opcache settings
- [ ] Monitor PHP-FPM processes

### Monitoring Setup
- [ ] Configure log rotation
- [ ] Set up monitoring alerts via aaPanel
- [ ] Monitor disk space usage
- [ ] Monitor database performance

---

## Phase 9: Backup & Maintenance 💾

### Backup Configuration
- [ ] Configure automatic file backups (weekly)
- [ ] Configure database backups (daily)
- [ ] Test backup restoration
- [ ] Set backup retention policy (30 days)

### Maintenance Tasks
- [ ] Schedule log cleanup
- [ ] Plan dependency updates
- [ ] Document maintenance procedures
- [ ] Create monitoring checklist

---

## Phase 10: Production Readiness 🚀

### Final Verification
- [ ] All endpoints responding correctly
- [ ] SSL certificate valid and auto-renewing
- [ ] Performance acceptable under load
- [ ] Error handling working properly
- [ ] Logs being written correctly

### Documentation & Handover
- [ ] Update API documentation with production URLs
- [ ] Provide client access credentials
- [ ] Document maintenance procedures
- [ ] Create user guide for token management

### Go-Live Checklist
- [ ] Notify stakeholders of go-live
- [ ] Monitor initial traffic
- [ ] Verify all integrations working
- [ ] Confirm backup systems operational

---

## Post-Deployment Tasks 📋

### Week 1
- [ ] Monitor error logs daily
- [ ] Check performance metrics
- [ ] Verify SSL certificate status
- [ ] Test backup procedures

### Month 1
- [ ] Review security logs
- [ ] Update dependencies if needed
- [ ] Performance optimization review
- [ ] User feedback collection

### Quarterly
- [ ] Security audit
- [ ] Performance review
- [ ] Dependency updates
- [ ] Backup testing

---

## Emergency Contacts & Information 📞

### Server Information
- **Server IP**: _____________
- **Domain**: api.yourdomain.com
- **aaPanel URL**: https://server-ip:custom-port/
- **SSH Access**: root@server-ip

### Application Information
- **Project Path**: /www/wwwroot/api.yourdomain.com
- **Log Files**: /www/wwwroot/api.yourdomain.com/storage/logs/
- **Database**: rsaz_sik (192.168.0.3:3939)

### Important Commands
```bash
# Restart services
systemctl restart nginx
systemctl restart php-fpm81

# Clear Laravel cache
cd /www/wwwroot/api.yourdomain.com
php artisan config:clear
php artisan cache:clear

# Check logs
tail -f storage/logs/laravel.log
tail -f /www/wwwroot/logs/api.yourdomain.com.error.log

# Generate new API token
php artisan generate:api-token

# Test database connection
php artisan test:db
```

---

## Sign-off ✍️

### Technical Team
- [ ] **Developer**: _________________ Date: _______
- [ ] **DevOps**: _________________ Date: _______
- [ ] **QA**: _________________ Date: _______

### Business Team
- [ ] **Project Manager**: _________________ Date: _______
- [ ] **Stakeholder**: _________________ Date: _______

**🎉 Deployment Completed Successfully!**

**API Base URL**: `https://api.yourdomain.com/api`
**Documentation**: `https://api.yourdomain.com/api-documentation.html`
