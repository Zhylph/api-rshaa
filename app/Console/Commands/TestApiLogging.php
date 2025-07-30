<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestApiLogging extends Command
{
    protected $signature = 'api:test-logging 
                            {--host=http://localhost : API host URL}
                            {--port=8000 : API port}
                            {--requests=10 : Number of test requests}
                            {--delay=1 : Delay between requests in seconds}';

    protected $description = 'Test API logging system with sample requests';

    public function handle()
    {
        $host = $this->option('host');
        $port = $this->option('port');
        $requests = (int) $this->option('requests');
        $delay = (int) $this->option('delay');

        $baseUrl = "{$host}:{$port}";

        $this->info("🧪 Testing API logging system");
        $this->info("🌐 Base URL: {$baseUrl}");
        $this->info("📊 Test requests: {$requests}");
        $this->newLine();

        // First, generate a token
        $this->info("1️⃣ Generating API token...");
        $tokenResponse = $this->generateToken($baseUrl);
        
        if (!$tokenResponse) {
            $this->error("Failed to generate token. Make sure the API server is running.");
            return 1;
        }

        $token = $tokenResponse['token'];
        $this->info("✅ Token generated: " . substr($token, 0, 20) . "...");
        $this->newLine();

        // Test scenarios
        $scenarios = [
            ['method' => 'GET', 'endpoint' => '/api/health', 'auth' => false, 'description' => 'Health check'],
            ['method' => 'GET', 'endpoint' => '/api/token/check', 'auth' => true, 'description' => 'Token validation'],
            ['method' => 'GET', 'endpoint' => '/api/pegawai?nik=123456', 'auth' => true, 'description' => 'Employee lookup'],
            ['method' => 'GET', 'endpoint' => '/api/rawat-inap-dr?bulan=12&tahun=2024', 'auth' => true, 'description' => 'Inpatient records'],
            ['method' => 'GET', 'endpoint' => '/api/rawat-jl-dr?bulan=12&tahun=2024', 'auth' => true, 'description' => 'Outpatient records'],
            ['method' => 'GET', 'endpoint' => '/api/jns-perawatan-inap', 'auth' => true, 'description' => 'Inpatient care types'],
            ['method' => 'GET', 'endpoint' => '/api/jns-perawatan', 'auth' => true, 'description' => 'Care types'],
            ['method' => 'GET', 'endpoint' => '/api/invalid-endpoint', 'auth' => true, 'description' => 'Invalid endpoint (404)'],
            ['method' => 'GET', 'endpoint' => '/api/pegawai', 'auth' => true, 'description' => 'Missing parameter (400)'],
            ['method' => 'GET', 'endpoint' => '/api/pegawai?nik=invalid', 'auth' => false, 'description' => 'No token (401)'],
        ];

        $this->info("2️⃣ Running test scenarios...");
        $this->newLine();

        foreach ($scenarios as $index => $scenario) {
            if ($index >= $requests) break;

            $num = $index + 1;
            $this->info("🔄 [{$num}/{$requests}] {$scenario['description']}");
            
            $this->makeTestRequest($baseUrl, $scenario, $token);
            
            if ($delay > 0 && $index < $requests - 1) {
                $this->line("   ⏳ Waiting {$delay} second(s)...");
                sleep($delay);
            }
        }

        $this->newLine();
        $this->info("3️⃣ Testing complete! Check logs with these commands:");
        $this->line("   <fg=cyan>php artisan api:monitor-logs --channel=api</>");
        $this->line("   <fg=cyan>php artisan api:monitor-logs --channel=api_security</>");
        $this->line("   <fg=cyan>php artisan api:analyze-logs</>");
        $this->newLine();

        // Show log file locations
        $this->info("📁 Log files location:");
        $logFiles = [
            'api' => storage_path('logs/api-' . now()->format('Y-m-d') . '.log'),
            'api_access' => storage_path('logs/api_access-' . now()->format('Y-m-d') . '.log'),
            'api_security' => storage_path('logs/api_security-' . now()->format('Y-m-d') . '.log'),
            'api_errors' => storage_path('logs/api_errors-' . now()->format('Y-m-d') . '.log'),
        ];

        foreach ($logFiles as $channel => $file) {
            $exists = file_exists($file) ? '✅' : '❌';
            $this->line("   {$exists} {$channel}: {$file}");
        }
    }

    private function generateToken($baseUrl)
    {
        try {
            $response = Http::timeout(10)->post("{$baseUrl}/api/token/generate");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            $this->error("Token generation failed: " . $response->body());
            return null;
        } catch (\Exception $e) {
            $this->error("Token generation error: " . $e->getMessage());
            return null;
        }
    }

    private function makeTestRequest($baseUrl, $scenario, $token)
    {
        try {
            $headers = [];
            if ($scenario['auth']) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $startTime = microtime(true);
            $response = Http::timeout(10)->withHeaders($headers)->get($baseUrl . $scenario['endpoint']);
            $endTime = microtime(true);
            
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            $statusCode = $response->status();
            
            // Determine status color
            $statusColor = match(true) {
                $statusCode >= 200 && $statusCode < 300 => 'green',
                $statusCode >= 400 && $statusCode < 500 => 'yellow',
                $statusCode >= 500 => 'red',
                default => 'white'
            };

            $this->line("   📤 {$scenario['method']} {$scenario['endpoint']}");
            $this->line("   📥 <fg={$statusColor}>{$statusCode}</> | {$responseTime}ms | " . strlen($response->body()) . " bytes");
            
            if (!$response->successful()) {
                $this->line("   ⚠️  " . substr($response->body(), 0, 100) . "...");
            }
            
        } catch (\Exception $e) {
            $this->line("   ❌ Request failed: " . $e->getMessage());
        }

        $this->newLine();
    }
}
