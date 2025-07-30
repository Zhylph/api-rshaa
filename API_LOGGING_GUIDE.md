# 📊 API Logging System Documentation

## Overview
Sistem logging API yang komprehensif untuk memantau aktivitas, keamanan, dan performance API medical records. Sistem ini menyediakan 4 channel logging terpisah dan tools untuk analisis serta monitoring real-time.

## 🔧 Log Channels

### 1. **api** - General Activity Logs
- **File**: `storage/logs/api-YYYY-MM-DD.log`
- **Content**: Semua request/response API dengan detail lengkap
- **Retention**: 30 hari

### 2. **api_access** - Access Pattern Logs
- **File**: `storage/logs/api_access-YYYY-MM-DD.log`
- **Content**: Pola akses, autentikasi sukses, penggunaan token
- **Retention**: 60 hari

### 3. **api_security** - Security Event Logs
- **File**: `storage/logs/api_security-YYYY-MM-DD.log`
- **Content**: Percobaan akses ilegal, token expired, autentikasi gagal
- **Retention**: 90 hari

### 4. **api_errors** - Error Logs
- **File**: `storage/logs/api_errors-YYYY-MM-DD.log`
- **Content**: Error 4xx dan 5xx, exception handling
- **Retention**: 60 hari

## 📝 Log Format

### General Activity Log (api channel)
```json
{
  "message": "API Request Completed",
  "context": {
    "request_id": "req_1234567890abcdef",
    "timestamp": "2024-01-15 10:30:45",
    "method": "GET",
    "endpoint": "/api/pegawai",
    "full_url": "http://localhost:8000/api/pegawai?nik=123456",
    "ip": "192.168.1.100",
    "user_agent": "Mozilla/5.0...",
    "parameters": {
      "nik": "123456"
    },
    "headers": {
      "Authorization": "Bearer eyJ...",
      "Accept": "application/json"
    },
    "response_time": 145.67,
    "status_code": 200,
    "response_size": 1024,
    "response_data": {
      "success": true,
      "data": {...}
    }
  }
}
```

### Security Event Log (api_security channel)
```json
{
  "message": "Token has expired",
  "context": {
    "ip": "192.168.1.100",
    "endpoint": "/api/pegawai",
    "expired_at": "2024-01-14 10:30:45",
    "timestamp": "2024-01-15 10:30:45"
  }
}
```

### Access Pattern Log (api_access channel)
```json
{
  "message": "Token authentication successful",
  "context": {
    "ip": "192.168.1.100",
    "endpoint": "/api/pegawai",
    "token_type": "expiring",
    "expires_at": "2024-02-15 10:30:45",
    "timestamp": "2024-01-15 10:30:45"
  }
}
```

## 🛠️ Management Commands

### 1. Analyze API Logs
Menganalisis log API dan menghasilkan statistik penggunaan:

```bash
# Analisis 7 hari terakhir (default)
php artisan api:analyze-logs

# Analisis tanggal spesifik
php artisan api:analyze-logs --date=2024-01-15

# Analisis 30 hari terakhir
php artisan api:analyze-logs --days=30

# Output dalam format JSON
php artisan api:analyze-logs --output=json

# Output dalam format CSV
php artisan api:analyze-logs --output=csv
```

**Output yang dihasilkan:**
- Total requests dan success rate
- Top endpoints yang paling banyak diakses
- Distribusi status code
- Top IP addresses
- Penggunaan token type
- Distribusi per jam
- Average/min/max response time

### 2. Monitor API Logs (Real-time)
Memantau log API secara real-time:

```bash
# Monitor general activity
php artisan api:monitor-logs --channel=api

# Monitor security events
php artisan api:monitor-logs --channel=api_security

# Monitor dengan filter
php artisan api:monitor-logs --channel=api --filter="pegawai"

# Tampilkan 100 baris terakhir
php artisan api:monitor-logs --tail=100
```

### 3. Test API Logging
Menguji sistem logging dengan request simulasi:

```bash
# Test dengan 10 request (default)
php artisan api:test-logging

# Test dengan custom host dan port
php artisan api:test-logging --host=http://192.168.1.10 --port=8080

# Test dengan 20 request, delay 2 detik
php artisan api:test-logging --requests=20 --delay=2
```

### 4. Clear Old Logs
Membersihkan log lama:

```bash
# Hapus log lebih dari 30 hari (default)
php artisan api:clear-logs

# Hapus log channel tertentu
php artisan api:clear-logs --channel=api_errors

# Hapus log lebih dari 7 hari
php artisan api:clear-logs --days=7

# Hapus tanpa konfirmasi
php artisan api:clear-logs --force
```

## 🎯 Use Cases

### 1. Security Monitoring
```bash
# Monitor percobaan akses ilegal
php artisan api:monitor-logs --channel=api_security --filter="Invalid token"

# Cek statistik keamanan
php artisan api:analyze-logs --days=1 | grep -A 10 "Security Events"
```

### 2. Performance Analysis
```bash
# Analisis response time
php artisan api:analyze-logs --output=json | jq '.avg_response_time'

# Monitor endpoint lambat
php artisan api:monitor-logs --channel=api --filter="response_time"
```

### 3. Usage Analytics
```bash
# Top endpoints minggu ini
php artisan api:analyze-logs --days=7

# Pola akses per jam
php artisan api:analyze-logs --output=csv
```

### 4. Troubleshooting
```bash
# Monitor error real-time
php artisan api:monitor-logs --channel=api_errors

# Analisis error pattern
php artisan api:analyze-logs --days=1 | grep -A 5 "Status Code"
```

## 📊 Log Rotation & Maintenance

### Automatic Rotation
- **Daily**: Log files dibuat per hari dengan format `channel-YYYY-MM-DD.log`
- **Size Limit**: Auto-rotate jika file > 100MB
- **Compression**: File lama otomatis di-compress

### Manual Maintenance
```bash
# Setup cron job untuk cleanup otomatis (tambahkan ke crontab)
0 2 * * * cd /path/to/api && php artisan api:clear-logs --days=30 --force

# Backup log penting sebelum cleanup
tar -czf api_logs_backup_$(date +%Y%m%d).tar.gz storage/logs/api*.log
```

## 🔍 Log Analysis Queries

### Common Queries

#### 1. Most Active IPs
```bash
cat storage/logs/api-$(date +%Y-%m-%d).log | jq -r '.context.ip' | sort | uniq -c | sort -nr | head -10
```

#### 2. Error Rate by Endpoint
```bash
grep '"status_code":4[0-9][0-9]\|"status_code":5[0-9][0-9]' storage/logs/api-$(date +%Y-%m-%d).log | jq -r '.context.endpoint' | sort | uniq -c
```

#### 3. Average Response Time by Endpoint
```bash
cat storage/logs/api-$(date +%Y-%m-%d).log | jq -r '"\(.context.endpoint) \(.context.response_time)"' | awk '{sum[$1]+=$2; count[$1]++} END {for(i in sum) print i, sum[i]/count[i]}'
```

#### 4. Token Expiry Events
```bash
grep "Token has expired" storage/logs/api_security-$(date +%Y-%m-%d).log | wc -l
```

## 🚨 Alerts & Monitoring

### Setting Up Alerts

#### 1. High Error Rate Alert
```bash
#!/bin/bash
ERROR_COUNT=$(grep -c '"status_code":5[0-9][0-9]' storage/logs/api-$(date +%Y-%m-%d).log)
if [ $ERROR_COUNT -gt 10 ]; then
    echo "High error rate detected: $ERROR_COUNT errors today" | mail -s "API Alert" admin@example.com
fi
```

#### 2. Security Alert
```bash
#!/bin/bash
SECURITY_EVENTS=$(grep -c "Invalid token\|Token has expired" storage/logs/api_security-$(date +%Y-%m-%d).log)
if [ $SECURITY_EVENTS -gt 50 ]; then
    echo "High security events: $SECURITY_EVENTS events today" | mail -s "Security Alert" security@example.com
fi
```

## 💡 Best Practices

### 1. Log Level Management
- **Production**: Set log level ke `info` atau `warning`
- **Development**: Gunakan `debug` untuk detail maksimal
- **Testing**: Gunakan dedicated log channels

### 2. Performance Optimization
- Enable log caching untuk environment production
- Gunakan asynchronous logging untuk high-traffic APIs
- Implement log aggregation untuk multiple servers

### 3. Security Considerations
- Jangan log sensitive data (password, full token)
- Encrypt log files yang berisi PII
- Implement log access controls
- Regular audit log access

### 4. Storage Management
- Monitor disk space secara berkala
- Implement automated cleanup policies
- Backup critical logs sebelum deletion
- Use compressed storage untuk archived logs

## 🔧 Configuration

### Environment Variables
```env
# Log Levels
LOG_LEVEL=info
LOG_DEPRECATIONS_CHANNEL=null

# Custom API Logging
API_LOG_ENABLED=true
API_LOG_DETAILED=true
API_LOG_SENSITIVE_DATA=false

# Log Retention (days)
API_LOG_RETENTION_GENERAL=30
API_LOG_RETENTION_ACCESS=60
API_LOG_RETENTION_SECURITY=90
API_LOG_RETENTION_ERRORS=60
```

### Custom Log Configuration
Edit `config/logging.php` untuk mengubah format atau destination logs sesuai kebutuhan.

---

## 📞 Support

Jika ada pertanyaan atau masalah dengan sistem logging:

1. **Check log files**: Periksa file log untuk error messages
2. **Run diagnostics**: `php artisan api:test-logging`
3. **Monitor real-time**: `php artisan api:monitor-logs`
4. **Analyze patterns**: `php artisan api:analyze-logs`

**Log file locations:**
- General: `storage/logs/api-YYYY-MM-DD.log`
- Access: `storage/logs/api_access-YYYY-MM-DD.log`
- Security: `storage/logs/api_security-YYYY-MM-DD.log`
- Errors: `storage/logs/api_errors-YYYY-MM-DD.log`
