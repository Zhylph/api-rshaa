<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateApiToken extends Command
{
    protected $signature = 'generate:api-token {--length=64}';
    protected $description = 'Generate a secure random API token';

    public function handle()
    {
        $length = $this->option('length');
        
        $token = Str::random($length);
        
        $this->info('🔐 Generated API Token:');
        $this->line('');
        $this->line($token);
        $this->line('');
        $this->info('📝 Add this to your .env file:');
        $this->line('API_TOKEN_SECRET=' . $token);
        $this->line('');
        $this->warn('⚠️  Keep this token secure and do not share it!');
        $this->warn('⚠️  Update your API documentation with the new token.');
        
        return 0;
    }
}
