<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearApiLogs extends Command
{
    protected $signature = 'api:clear-logs 
                            {--channel= : Clear specific channel logs (api, api_errors, api_access, api_security)}
                            {--days=30 : Clear logs older than specified days}
                            {--force : Force deletion without confirmation}';

    protected $description = 'Clear old API logs';

    public function handle()
    {
        $channel = $this->option('channel');
        $days = (int) $this->option('days');
        $force = $this->option('force');

        $this->info("🧹 Clearing API logs...");
        
        if ($channel) {
            $this->info("📂 Channel: {$channel}");
        } else {
            $this->info("📂 All API channels");
        }
        
        $this->info("📅 Older than: {$days} days");
        $this->newLine();

        $logDir = storage_path('logs');
        $cutoffDate = now()->subDays($days);
        
        // Find log files to delete
        $filesToDelete = [];
        $channels = $channel ? [$channel] : ['api', 'api_access', 'api_security', 'api_errors'];
        
        foreach ($channels as $ch) {
            $pattern = "{$logDir}/{$ch}-*.log";
            $files = glob($pattern);
            
            foreach ($files as $file) {
                $fileDate = $this->extractDateFromLogFile($file);
                if ($fileDate && $fileDate->lt($cutoffDate)) {
                    $filesToDelete[] = $file;
                }
            }
        }

        if (empty($filesToDelete)) {
            $this->info("✅ No log files found older than {$days} days.");
            return 0;
        }

        // Display files to be deleted
        $this->info("📋 Files to be deleted:");
        $totalSize = 0;
        
        foreach ($filesToDelete as $file) {
            $size = File::size($file);
            $totalSize += $size;
            $sizeFormatted = $this->formatBytes($size);
            $relativePath = str_replace($logDir . DIRECTORY_SEPARATOR, '', $file);
            $this->line("   🗑️  {$relativePath} ({$sizeFormatted})");
        }
        
        $this->newLine();
        $this->info("📊 Total files: " . count($filesToDelete));
        $this->info("💾 Total size: " . $this->formatBytes($totalSize));
        $this->newLine();

        // Confirm deletion
        if (!$force) {
            if (!$this->confirm('Are you sure you want to delete these log files?')) {
                $this->info("❌ Operation cancelled.");
                return 0;
            }
        }

        // Delete files
        $deleted = 0;
        $errors = 0;

        foreach ($filesToDelete as $file) {
            try {
                if (File::delete($file)) {
                    $deleted++;
                    $this->line("✅ Deleted: " . basename($file));
                } else {
                    $errors++;
                    $this->line("<fg=red>❌ Failed to delete: " . basename($file) . "</>");
                }
            } catch (\Exception $e) {
                $errors++;
                $this->line("<fg=red>❌ Error deleting " . basename($file) . ": " . $e->getMessage() . "</>");
            }
        }

        $this->newLine();
        $this->info("📈 Summary:");
        $this->line("   ✅ Deleted: {$deleted} files");
        if ($errors > 0) {
            $this->line("   <fg=red>❌ Errors: {$errors} files</>");
        }
        $this->line("   💾 Space freed: " . $this->formatBytes($totalSize));
        
        $this->newLine();
        $this->info("🎉 Log cleanup complete!");
    }

    private function extractDateFromLogFile($filePath)
    {
        $filename = basename($filePath);
        
        // Extract date from filename like "api-2024-01-15.log"
        if (preg_match('/(\d{4}-\d{2}-\d{2})\.log$/', $filename, $matches)) {
            try {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $matches[1]);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
