<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApiActivityLogger
{
    /**
     * Handle an incoming request and log API activities
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        // Get request information
        $requestData = [
            'timestamp' => Carbon::now()->toISOString(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_id' => uniqid('api_', true),
            'parameters' => $request->query(),
            'has_auth' => $request->hasHeader('Authorization'),
            'auth_type' => $request->hasHeader('Authorization') ? 'Bearer Token' : 'None'
        ];

        // Log the incoming request
        Log::channel('api')->info('API Request', $requestData);

        // Process the request
        $response = $next($request);

        // Calculate response time
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);

        // Get response information
        $responseData = [
            'request_id' => $requestData['request_id'],
            'status_code' => $response->getStatusCode(),
            'response_time_ms' => $responseTime,
            'response_size' => strlen($response->getContent()),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
        ];

        // Try to get response data for additional context
        try {
            $responseContent = json_decode($response->getContent(), true);
            if (isset($responseContent['success'])) {
                $responseData['api_success'] = $responseContent['success'];
            }
            if (isset($responseContent['message'])) {
                $responseData['api_message'] = $responseContent['message'];
            }
            if (isset($responseContent['total_records'])) {
                $responseData['records_returned'] = $responseContent['total_records'];
            }
        } catch (\Exception $e) {
            // Ignore JSON decode errors
        }

        // Log the response
        $logLevel = $response->getStatusCode() >= 400 ? 'warning' : 'info';
        Log::channel('api')->{$logLevel}('API Response', $responseData);

        // Log errors separately
        if ($response->getStatusCode() >= 400) {
            Log::channel('api_errors')->error('API Error Response', array_merge($requestData, $responseData));
        }

        return $response;
    }
}
