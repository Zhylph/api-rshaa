# 🏥 RSAZ Medical Records API

API untuk mengakses data rekam medis RSAZ dengan sistem autentikasi token.

## 📋 Overview

API ini menyediakan akses ke 5 tabel utama dalam database rekam medis:
- **Pegawai** - Data pegawai (dengan parameter NIK)
- **Rawat Inap Dokter** - Data perawatan rawat inap (dengan parameter bulan & tahun)
- **Rawat Jalan Dokter** - Data perawatan rawat jalan (dengan parameter bulan & tahun)
- **Jenis Perawatan Inap** - Master data jenis perawatan inap (tanpa parameter)
- **Jenis Perawatan** - Master data jenis perawatan (tanpa parameter)

## 🔧 Setup & Installation

### Prerequisites
- PHP 8.1+
- Laravel 11
- MySQL Database
- Composer

### Database Configuration
Database Server: `192.168.0.3:3939`
Database Name: `rsaz_sik`
Username: `herd`
Password: `HewlettPackard11@@`

### Installation Steps

1. **Clone dan Install Dependencies**
```bash
cd "d:\Herd Project\apirshaa"
composer install
```

2. **Configure Environment**
File `.env` sudah dikonfigurasi dengan:
```env
DB_CONNECTION=mysql
DB_HOST=192.168.0.3
DB_PORT=3939
DB_DATABASE=rsaz_sik
DB_USERNAME=herd
DB_PASSWORD=HewlettPackard11@@
API_TOKEN_SECRET=your_secret_key_here_change_this_in_production_rsaz_api_2025
```

3. **Test Database Connection**
```bash
php artisan test:db
```

4. **Clear Configuration Cache**
```bash
php artisan config:clear
```

5. **Start Development Server**
```bash
php artisan serve
```

## 🔐 Authentication

API menggunakan Bearer Token authentication. Token harus disertakan dalam header:
```
Authorization: Bearer your_secret_key_here_change_this_in_production_rsaz_api_2025
```

### Generate Token
```bash
POST /api/token/generate
Content-Type: application/json

{
    "admin_key": "rsaz_admin_2025"
}
```

## 📡 API Endpoints

### Base URL
```
http://apirshaa.test/api
```

### 1. Health Check
```bash
GET /api/health
```
*No authentication required*

### 2. Pegawai (Employee Data)
```bash
GET /api/pegawai?nik={nik}
Authorization: Bearer {token}
```
**Parameters:**
- `nik` (required): Employee identification number

### 3. Rawat Inap Dokter (Inpatient Care)
```bash
GET /api/rawat-inap-dr?bulan={month}&tahun={year}
Authorization: Bearer {token}
```
**Parameters:**
- `bulan` (required): Month (1-12)
- `tahun` (required): Year (e.g., 2025)

### 4. Rawat Jalan Dokter (Outpatient Care)
```bash
GET /api/rawat-jl-dr?bulan={month}&tahun={year}
Authorization: Bearer {token}
```
**Parameters:**
- `bulan` (required): Month (1-12)  
- `tahun` (required): Year (e.g., 2025)

### 5. Jenis Perawatan Inap (Inpatient Care Types)
```bash
GET /api/jns-perawatan-inap
Authorization: Bearer {token}
```
*No parameters required*

### 6. Jenis Perawatan (Care Types)
```bash
GET /api/jns-perawatan
Authorization: Bearer {token}
```
*No parameters required*

## 📚 Documentation

### HTML Documentation
Buka file dokumentasi lengkap di browser:
```
http://apirshaa.test/api-documentation.html
```

### Postman Collection
Import collection file untuk testing:
```
public/RSAZ-Medical-API.postman_collection.json
```

**Setup Postman:**
1. Import collection file
2. Set collection variables:
   - `base_url`: `http://apirshaa.test/api`
   - `token`: `your_secret_key_here_change_this_in_production_rsaz_api_2025`
3. Set collection authorization to Bearer Token dengan `{{token}}`

## 🧪 Testing

### Test Database Connection
```bash
php artisan test:db
```

### Test API Endpoints
```bash
php artisan test:api
```

## 📊 Database Statistics

Saat ini database berisi:
- **Pegawai**: 372 records
- **Rawat Inap Dokter**: 5,964 records  
- **Rawat Jalan Dokter**: 4,938 records
- **Jenis Perawatan Inap**: 1,118 records
- **Jenis Perawatan**: 2,756 records

## 🔒 Security

- Semua endpoint (kecuali health check dan generate token) memerlukan authentication
- Token disimpan dalam environment variable
- Validasi parameter untuk mencegah injection
- Error handling yang aman tanpa expose informasi sensitif

## 📝 Response Format

Semua response menggunakan format JSON standar:

**Success Response:**
```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [...],
    "total_records": 100
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Error description"
}
```

## 🚨 Error Codes

- `400` - Bad Request (missing parameters)
- `401` - Unauthorized (invalid/missing token)
- `404` - Not Found (data not found)
- `500` - Internal Server Error (database/server issues)

## 📞 Support

Untuk bantuan atau pertanyaan mengenai API ini, silakan hubungi tim development.

---

**Version:** 1.0  
**Last Updated:** July 30, 2025  
**Database:** rsaz_sik (192.168.0.3:3939)
