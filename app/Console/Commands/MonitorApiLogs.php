<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MonitorApiLogs extends Command
{
    protected $signature = 'api:monitor-logs 
                            {--channel=api : Log channel to monitor (api, api_errors, api_access, api_security)}
                            {--filter= : Filter logs containing specific text}
                            {--tail=50 : Number of recent lines to show initially}';

    protected $description = 'Monitor API logs in real-time';

    private $lastPosition = 0;

    public function handle()
    {
        $channel = $this->option('channel');
        $filter = $this->option('filter');
        $tail = (int) $this->option('tail');

        $logFile = storage_path("logs/{$channel}-" . now()->format('Y-m-d') . '.log');

        if (!File::exists($logFile)) {
            $this->error("Log file does not exist: {$logFile}");
            $this->info("Available log files:");
            $logFiles = glob(storage_path('logs/api*.log'));
            foreach ($logFiles as $file) {
                $this->line('  ' . basename($file));
            }
            return 1;
        }

        $this->info("🔍 Monitoring API logs: {$channel}");
        if ($filter) {
            $this->info("🔎 Filter: {$filter}");
        }
        $this->info("📁 File: {$logFile}");
        $this->info("Press Ctrl+C to stop monitoring");
        $this->newLine();

        // Show recent logs
        $this->showTailLogs($logFile, $tail, $filter);

        // Monitor for new logs
        $this->monitorLogs($logFile, $filter);
    }

    private function showTailLogs($logFile, $tail, $filter = null)
    {
        $this->info("📜 Last {$tail} log entries:");
        $this->line(str_repeat('-', 80));

        $lines = $this->getTailLines($logFile, $tail);
        
        foreach ($lines as $line) {
            if ($filter && stripos($line, $filter) === false) {
                continue;
            }
            $this->displayLogLine($line);
        }

        $this->line(str_repeat('-', 80));
        $this->info("🔄 Monitoring for new entries...");
        $this->newLine();

        // Set position for monitoring
        $this->lastPosition = filesize($logFile);
    }

    private function getTailLines($filename, $lines)
    {
        $handle = fopen($filename, 'r');
        $lineArray = [];

        if ($handle) {
            fseek($handle, -1, SEEK_END);
            $lineCount = 0;
            $content = '';

            while (ftell($handle) > 0 && $lineCount < $lines) {
                $char = fgetc($handle);
                if ($char === "\n") {
                    $lineCount++;
                    if ($lineCount < $lines) {
                        array_unshift($lineArray, $content);
                        $content = '';
                    }
                } else {
                    $content = $char . $content;
                }
                fseek($handle, -2, SEEK_CUR);
            }

            if ($content !== '') {
                array_unshift($lineArray, $content);
            }

            fclose($handle);
        }

        return $lineArray;
    }

    private function monitorLogs($logFile, $filter = null)
    {
        while (true) {
            clearstatcache();
            $currentSize = filesize($logFile);

            if ($currentSize > $this->lastPosition) {
                $handle = fopen($logFile, 'r');
                fseek($handle, $this->lastPosition);

                while (($line = fgets($handle)) !== false) {
                    $line = trim($line);
                    if (!empty($line)) {
                        if (!$filter || stripos($line, $filter) !== false) {
                            $this->displayLogLine($line);
                        }
                    }
                }

                $this->lastPosition = $currentSize;
                fclose($handle);
            }

            usleep(500000); // Sleep for 0.5 seconds
        }
    }

    private function displayLogLine($line)
    {
        // Parse timestamp and level
        $timestamp = '';
        $level = '';
        $message = $line;

        if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*?\.(\w+):/', $line, $matches)) {
            $timestamp = $matches[1];
            $level = strtoupper($matches[2]);
        }

        // Color code based on log level
        $color = match($level) {
            'ERROR' => 'red',
            'WARNING' => 'yellow',
            'INFO' => 'blue',
            'DEBUG' => 'cyan',
            default => 'white'
        };

        // Format timestamp
        if ($timestamp) {
            $time = date('H:i:s', strtotime($timestamp));
            $this->line("<fg=gray>[{$time}]</> <fg={$color}>[{$level}]</> {$this->formatLogMessage($line)}");
        } else {
            $this->line("<fg={$color}>{$line}</>");
        }
    }

    private function formatLogMessage($line)
    {
        // Try to extract and format JSON context
        if (strpos($line, '{"message":') !== false) {
            $jsonStart = strpos($line, '{"message":');
            $jsonData = substr($line, $jsonStart);
            $data = json_decode($jsonData, true);

            if ($data && isset($data['context'])) {
                $context = $data['context'];
                $formatted = $data['message'] ?? '';

                // Add key context information
                $contextInfo = [];
                
                if (isset($context['method']) && isset($context['endpoint'])) {
                    $contextInfo[] = "<fg=cyan>{$context['method']}</> <fg=white>{$context['endpoint']}</>";
                }
                
                if (isset($context['status_code'])) {
                    $statusColor = $context['status_code'] >= 400 ? 'red' : 'green';
                    $contextInfo[] = "<fg={$statusColor}>{$context['status_code']}</>";
                }
                
                if (isset($context['response_time'])) {
                    $timeColor = $context['response_time'] > 1000 ? 'red' : ($context['response_time'] > 500 ? 'yellow' : 'green');
                    $contextInfo[] = "<fg={$timeColor}>{$context['response_time']}ms</>";
                }
                
                if (isset($context['ip'])) {
                    $contextInfo[] = "<fg=gray>{$context['ip']}</>";
                }

                if (!empty($contextInfo)) {
                    $formatted .= ' [' . implode(' | ', $contextInfo) . ']';
                }

                return $formatted;
            }
        }

        return $line;
    }
}
