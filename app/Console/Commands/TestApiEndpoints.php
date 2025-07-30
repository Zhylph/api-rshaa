<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestApiEndpoints extends Command
{
    protected $signature = 'test:api';
    protected $description = 'Test all API endpoints';

    public function handle()
    {
        $baseUrl = 'http://apirshaa.test/api';
        $token = env('API_TOKEN_SECRET');
        
        $this->info('Testing API endpoints...');
        $this->info('Base URL: ' . $baseUrl);
        $this->info('Token: ' . $token);
        
        // Test 1: Health check
        $this->testEndpoint('GET', $baseUrl . '/health', [], false);
        
        // Test 2: Generate Token
        $this->testEndpoint('POST', $baseUrl . '/token/generate', [
            'admin_key' => 'rsaz_admin_2025'
        ], false);
        
        // Test 3: Get first employee
        $this->info('Getting first employee NIK...');
        try {
            $firstEmployee = \App\Models\Pegawai::first();
            if ($firstEmployee) {
                $this->info('Found employee NIK: ' . $firstEmployee->nik);
                $this->testEndpoint('GET', $baseUrl . '/pegawai?nik=' . $firstEmployee->nik, [], true, $token);
            } else {
                $this->error('No employees found in database');
            }
        } catch (\Exception $e) {
            $this->error('Error getting employee: ' . $e->getMessage());
        }
        
        // Test 4: Get rawat inap dr (July 2025)
        $this->testEndpoint('GET', $baseUrl . '/rawat-inap-dr?bulan=7&tahun=2025', [], true, $token);
        
        // Test 5: Get rawat jl dr (July 2025) 
        $this->testEndpoint('GET', $baseUrl . '/rawat-jl-dr?bulan=7&tahun=2025', [], true, $token);
        
        // Test 6: Get jns perawatan inap
        $this->testEndpoint('GET', $baseUrl . '/jns-perawatan-inap', [], true, $token);
        
        // Test 7: Get jns perawatan
        $this->testEndpoint('GET', $baseUrl . '/jns-perawatan', [], true, $token);
    }
    
    private function testEndpoint($method, $url, $data = [], $requiresAuth = false, $token = null)
    {
        $this->info("\n🧪 Testing {$method} {$url}");
        
        try {
            $headers = [];
            if ($requiresAuth && $token) {
                $headers['Authorization'] = 'Bearer ' . $token;
            }
            
            $response = Http::withHeaders($headers);
            
            if ($method === 'POST') {
                $response = $response->post($url, $data);
            } else {
                $response = $response->get($url);
            }
            
            $statusCode = $response->status();
            $body = $response->json();
            
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->info("✅ Status: {$statusCode}");
                if (isset($body['success'])) {
                    $this->info("   Success: " . ($body['success'] ? 'true' : 'false'));
                }
                if (isset($body['message'])) {
                    $this->info("   Message: " . $body['message']);
                }
                if (isset($body['total_records'])) {
                    $this->info("   Records: " . $body['total_records']);
                }
            } else {
                $this->error("❌ Status: {$statusCode}");
                if (isset($body['message'])) {
                    $this->error("   Error: " . $body['message']);
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
        }
    }
}
