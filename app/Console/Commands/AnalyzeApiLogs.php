<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AnalyzeApiLogs extends Command
{
    protected $signature = 'api:analyze-logs 
                            {--date= : Analyze logs for specific date (Y-m-d format)}
                            {--days=7 : Number of days to analyze}
                            {--output=table : Output format (table, json, csv)}';

    protected $description = 'Analyze API usage logs and generate statistics';

    public function handle()
    {
        $this->info('📊 Analyzing API logs...');

        $date = $this->option('date');
        $days = (int) $this->option('days');
        $output = $this->option('output');

        if ($date) {
            $logFiles = [$this->getLogFileName('api', $date)];
            $this->info("Analyzing logs for date: {$date}");
        } else {
            $logFiles = $this->getRecentLogFiles($days);
            $this->info("Analyzing logs for the last {$days} days");
        }

        $stats = $this->analyzeLogFiles($logFiles);

        $this->displayStats($stats, $output);
    }

    private function getLogFileName($channel, $date)
    {
        return storage_path("logs/{$channel}-{$date}.log");
    }

    private function getRecentLogFiles($days)
    {
        $files = [];
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $file = $this->getLogFileName('api', $date);
            if (File::exists($file)) {
                $files[] = $file;
            }
        }
        return $files;
    }

    private function analyzeLogFiles($logFiles)
    {
        $stats = [
            'total_requests' => 0,
            'successful_requests' => 0,
            'failed_requests' => 0,
            'endpoints' => [],
            'methods' => [],
            'status_codes' => [],
            'ip_addresses' => [],
            'response_times' => [],
            'hourly_distribution' => [],
            'token_types' => [],
            'errors' => []
        ];

        foreach ($logFiles as $logFile) {
            if (!File::exists($logFile)) {
                continue;
            }

            $content = File::get($logFile);
            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                if (empty(trim($line))) continue;

                $this->processLogLine($line, $stats);
            }
        }

        // Calculate averages and percentages
        if (count($stats['response_times']) > 0) {
            $stats['avg_response_time'] = array_sum($stats['response_times']) / count($stats['response_times']);
            $stats['max_response_time'] = max($stats['response_times']);
            $stats['min_response_time'] = min($stats['response_times']);
        }

        if ($stats['total_requests'] > 0) {
            $stats['success_rate'] = ($stats['successful_requests'] / $stats['total_requests']) * 100;
        }

        return $stats;
    }

    private function processLogLine($line, &$stats)
    {
        // Parse JSON log entry
        if (strpos($line, '{"message":') !== false) {
            $jsonStart = strpos($line, '{"message":');
            $jsonData = substr($line, $jsonStart);
            $data = json_decode($jsonData, true);

            if ($data && isset($data['context'])) {
                $context = $data['context'];
                $stats['total_requests']++;

                // Track endpoints
                if (isset($context['endpoint'])) {
                    $endpoint = $context['endpoint'];
                    $stats['endpoints'][$endpoint] = ($stats['endpoints'][$endpoint] ?? 0) + 1;
                }

                // Track methods
                if (isset($context['method'])) {
                    $method = $context['method'];
                    $stats['methods'][$method] = ($stats['methods'][$method] ?? 0) + 1;
                }

                // Track status codes
                if (isset($context['status_code'])) {
                    $statusCode = $context['status_code'];
                    $stats['status_codes'][$statusCode] = ($stats['status_codes'][$statusCode] ?? 0) + 1;

                    if ($statusCode >= 200 && $statusCode < 300) {
                        $stats['successful_requests']++;
                    } else {
                        $stats['failed_requests']++;
                    }
                }

                // Track IP addresses
                if (isset($context['ip'])) {
                    $ip = $context['ip'];
                    $stats['ip_addresses'][$ip] = ($stats['ip_addresses'][$ip] ?? 0) + 1;
                }

                // Track response times
                if (isset($context['response_time'])) {
                    $stats['response_times'][] = (float) $context['response_time'];
                }

                // Track hourly distribution
                if (isset($context['timestamp'])) {
                    $hour = date('H', strtotime($context['timestamp']));
                    $stats['hourly_distribution'][$hour] = ($stats['hourly_distribution'][$hour] ?? 0) + 1;
                }

                // Track token types
                if (isset($context['token_type'])) {
                    $tokenType = $context['token_type'];
                    $stats['token_types'][$tokenType] = ($stats['token_types'][$tokenType] ?? 0) + 1;
                }
            }
        }
    }

    private function displayStats($stats, $output)
    {
        switch ($output) {
            case 'json':
                $this->line(json_encode($stats, JSON_PRETTY_PRINT));
                break;
            case 'csv':
                $this->outputCsv($stats);
                break;
            default:
                $this->displayTableStats($stats);
        }
    }

    private function displayTableStats($stats)
    {
        $this->newLine();
        $this->info('📈 API Usage Statistics');
        $this->newLine();

        // Summary
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Requests', number_format($stats['total_requests'])],
                ['Successful Requests', number_format($stats['successful_requests'])],
                ['Failed Requests', number_format($stats['failed_requests'])],
                ['Success Rate', number_format($stats['success_rate'] ?? 0, 2) . '%'],
                ['Average Response Time', number_format($stats['avg_response_time'] ?? 0, 2) . ' ms'],
                ['Max Response Time', number_format($stats['max_response_time'] ?? 0, 2) . ' ms'],
                ['Min Response Time', number_format($stats['min_response_time'] ?? 0, 2) . ' ms'],
            ]
        );

        // Top Endpoints
        if (!empty($stats['endpoints'])) {
            $this->newLine();
            $this->info('🎯 Top Endpoints');
            arsort($stats['endpoints']);
            $topEndpoints = array_slice($stats['endpoints'], 0, 10, true);
            $endpointData = [];
            foreach ($topEndpoints as $endpoint => $count) {
                $percentage = ($count / $stats['total_requests']) * 100;
                $endpointData[] = [$endpoint, number_format($count), number_format($percentage, 2) . '%'];
            }
            $this->table(['Endpoint', 'Requests', 'Percentage'], $endpointData);
        }

        // Status Codes
        if (!empty($stats['status_codes'])) {
            $this->newLine();
            $this->info('📊 Status Code Distribution');
            arsort($stats['status_codes']);
            $statusData = [];
            foreach ($stats['status_codes'] as $code => $count) {
                $percentage = ($count / $stats['total_requests']) * 100;
                $statusData[] = [$code, number_format($count), number_format($percentage, 2) . '%'];
            }
            $this->table(['Status Code', 'Count', 'Percentage'], $statusData);
        }

        // Top IPs
        if (!empty($stats['ip_addresses'])) {
            $this->newLine();
            $this->info('🌐 Top IP Addresses');
            arsort($stats['ip_addresses']);
            $topIps = array_slice($stats['ip_addresses'], 0, 10, true);
            $ipData = [];
            foreach ($topIps as $ip => $count) {
                $percentage = ($count / $stats['total_requests']) * 100;
                $ipData[] = [$ip, number_format($count), number_format($percentage, 2) . '%'];
            }
            $this->table(['IP Address', 'Requests', 'Percentage'], $ipData);
        }

        // Token Types
        if (!empty($stats['token_types'])) {
            $this->newLine();
            $this->info('🔑 Token Type Usage');
            $tokenData = [];
            foreach ($stats['token_types'] as $type => $count) {
                $percentage = ($count / array_sum($stats['token_types'])) * 100;
                $tokenData[] = [$type, number_format($count), number_format($percentage, 2) . '%'];
            }
            $this->table(['Token Type', 'Usage', 'Percentage'], $tokenData);
        }

        // Hourly Distribution
        if (!empty($stats['hourly_distribution'])) {
            $this->newLine();
            $this->info('🕐 Hourly Request Distribution');
            ksort($stats['hourly_distribution']);
            $hourlyData = [];
            foreach ($stats['hourly_distribution'] as $hour => $count) {
                $percentage = ($count / $stats['total_requests']) * 100;
                $hourlyData[] = [
                    str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00',
                    number_format($count),
                    str_repeat('█', min(50, (int)($percentage * 2))) . ' ' . number_format($percentage, 1) . '%'
                ];
            }
            $this->table(['Hour', 'Requests', 'Distribution'], $hourlyData);
        }
    }

    private function outputCsv($stats)
    {
        $filename = 'api_stats_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path("logs/{$filename}");

        $csv = fopen($filepath, 'w');

        // Summary
        fputcsv($csv, ['Metric', 'Value']);
        fputcsv($csv, ['Total Requests', $stats['total_requests']]);
        fputcsv($csv, ['Successful Requests', $stats['successful_requests']]);
        fputcsv($csv, ['Failed Requests', $stats['failed_requests']]);
        fputcsv($csv, ['Success Rate', number_format($stats['success_rate'] ?? 0, 2) . '%']);
        fputcsv($csv, ['']);

        // Endpoints
        fputcsv($csv, ['Endpoint', 'Requests', 'Percentage']);
        foreach ($stats['endpoints'] as $endpoint => $count) {
            $percentage = ($count / $stats['total_requests']) * 100;
            fputcsv($csv, [$endpoint, $count, number_format($percentage, 2) . '%']);
        }

        fclose($csv);
        $this->info("CSV report saved to: {$filepath}");
    }
}
