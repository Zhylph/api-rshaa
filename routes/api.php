<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MedicalRecordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public route to generate token
Route::post('/token/generate', [MedicalRecordController::class, 'generateToken']);

// Public route to check token validity
Route::get('/token/check', [MedicalRecordController::class, 'checkToken']);

// Protected API routes - require token authentication
Route::middleware('api.token')->group(function () {
    // Pegawai endpoint - requires NIK parameter
    Route::get('/pegawai', [MedicalRecordController::class, 'getPegawai']);
    
    // Rawat Inap Dokter endpoint - requires bulan and tahun parameters
    Route::get('/rawat-inap-dr', [MedicalRecordController::class, 'getRawatInapDr']);
    
    // Rawat Jalan Dokter endpoint - requires bulan and tahun parameters
    Route::get('/rawat-jl-dr', [MedicalRecordController::class, 'getRawatJlDr']);
    
    // Jenis Perawatan Inap endpoint - no parameters required
    Route::get('/jns-perawatan-inap', [MedicalRecordController::class, 'getJnsPerawatanInap']);
    
    // Jenis Perawatan endpoint - no parameters required
    Route::get('/jns-perawatan', [MedicalRecordController::class, 'getJnsPerawatan']);
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => now(),
        'database' => 'Connected to: ' . config('database.connections.mysql.database')
    ]);
});
