<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestTokenGeneration extends Command
{
    protected $signature = 'test:token';
    protected $description = 'Test new token generation and validation';

    public function handle()
    {
        $this->info('🧪 Testing Token Generation...');
        
        // Test token generation
        $timestamp = now()->addMonth()->timestamp;
        $randomString = Str::random(32);
        $secret = env('API_TOKEN_SECRET');
        $token = base64_encode($timestamp . '|' . $randomString . '|' . $secret);
        
        $this->info('✅ Generated Token:');
        $this->line($token);
        $this->line('');
        
        $this->info('📅 Token Details:');
        $this->line('Expires: ' . date('Y-m-d H:i:s', $timestamp));
        $this->line('Current: ' . date('Y-m-d H:i:s', time()));
        $this->line('Valid for: ' . round(($timestamp - time()) / 86400, 1) . ' days');
        $this->line('');
        
        // Test token validation
        $this->info('🔍 Testing Token Validation...');
        try {
            $decodedToken = base64_decode($token);
            $parts = explode('|', $decodedToken);
            
            if (count($parts) === 3) {
                [$tokenTimestamp, $tokenRandomString, $tokenSecret] = $parts;
                
                $this->info('✅ Token Format: Valid');
                $this->info('✅ Secret Match: ' . ($tokenSecret === $secret ? 'Yes' : 'No'));
                $this->info('✅ Expiry Check: ' . (time() < $tokenTimestamp ? 'Not Expired' : 'Expired'));
            } else {
                $this->error('❌ Invalid token format');
            }
        } catch (\Exception $e) {
            $this->error('❌ Token validation error: ' . $e->getMessage());
        }
        
        // Show usage example
        $this->line('');
        $this->info('💡 Usage Example:');
        $this->line('Authorization: Bearer ' . $token);
        
        return 0;
    }
}
