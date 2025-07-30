<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestNewTokenApi extends Command
{
    protected $signature = 'test:new-token';
    protected $description = 'Test new token API endpoints';

    public function handle()
    {
        $baseUrl = 'http://apirshaa.test/api';
        
        $this->info('🧪 Testing New Token API...');
        $this->info('Base URL: ' . $baseUrl);
        
        // Test 1: Generate new token
        $this->info("\n1️⃣ Testing token generation...");
        try {
            $response = Http::post($baseUrl . '/token/generate', [
                'admin_key' => 'rsaz_admin_2025'
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ Token generated successfully');
                $this->line('   Message: ' . $data['message']);
                $this->line('   Expires: ' . $data['expires_at']);
                $this->line('   Duration: ' . $data['expires_in']);
                
                $token = $data['token'];
                $this->line('   Token: ' . substr($token, 0, 50) . '...');
                
                // Test 2: Check token status
                $this->info("\n2️⃣ Testing token check...");
                $checkResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token
                ])->get($baseUrl . '/token/check');
                
                if ($checkResponse->successful()) {
                    $checkData = $checkResponse->json();
                    $this->info('✅ Token check successful');
                    $this->line('   Valid: ' . ($checkData['valid'] ? 'Yes' : 'No'));
                    $this->line('   Type: ' . $checkData['type']);
                    $this->line('   Expires: ' . $checkData['expires_at']);
                    $this->line('   Time remaining: ' . $checkData['time_remaining']);
                } else {
                    $this->error('❌ Token check failed: ' . $checkResponse->status());
                }
                
                // Test 3: Use token for protected endpoint
                $this->info("\n3️⃣ Testing protected endpoint...");
                $protectedResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token
                ])->get($baseUrl . '/jns-perawatan-inap');
                
                if ($protectedResponse->successful()) {
                    $protectedData = $protectedResponse->json();
                    $this->info('✅ Protected endpoint accessible');
                    $this->line('   Records: ' . $protectedData['total_records']);
                } else {
                    $this->error('❌ Protected endpoint failed: ' . $protectedResponse->status());
                }
                
            } else {
                $this->error('❌ Token generation failed: ' . $response->status());
                $this->error('   Response: ' . $response->body());
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
        
        return 0;
    }
}
