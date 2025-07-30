<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;

class TestDatabaseConnection extends Command
{
    protected $signature = 'test:db';
    protected $description = 'Test database connection and API endpoints';

    public function handle()
    {
        $this->info('Testing database connection...');
        
        try {
            // Test basic connection
            DB::connection()->getPdo();
            $this->info('✅ Database connection successful!');
            
            // Test if tables exist
            $tables = ['pegawai', 'rawat_inap_dr', 'rawat_jl_dr', 'jns_perawatan_inap', 'jns_perawatan'];
            
            foreach ($tables as $table) {
                try {
                    $count = DB::table($table)->count();
                    $this->info("✅ Table '{$table}' exists with {$count} records");
                } catch (\Exception $e) {
                    $this->error("❌ Table '{$table}' error: " . $e->getMessage());
                }
            }
            
            // Test model connections
            try {
                $pegawaiCount = Pegawai::count();
                $this->info("✅ Pegawai model working with {$pegawaiCount} records");
            } catch (\Exception $e) {
                $this->error("❌ Pegawai model error: " . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Database connection failed: ' . $e->getMessage());
            $this->error('Please check your database configuration in .env file');
        }
    }
}
