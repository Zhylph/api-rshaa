<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');
        
        // Log authentication attempt
        Log::channel('api_security')->info('Authentication attempt', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'endpoint' => $request->path(),
            'has_token' => !empty($token),
            'timestamp' => now()->toISOString()
        ]);
        
        if (!$token) {
            Log::channel('api_security')->warning('Missing token', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'timestamp' => now()->toISOString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Token is required. Please include Authorization header.'
            ], 401);
        }

        // Remove "Bearer " prefix if present
        $token = str_replace('Bearer ', '', $token);

        // Check if it's the old permanent token format (for backward compatibility)
        $permanentToken = env('API_TOKEN_SECRET');
        if ($token === $permanentToken) {
            Log::channel('api_access')->info('Permanent token access', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'token_type' => 'permanent',
                'timestamp' => now()->toISOString()
            ]);
            return $next($request);
        }

        // Check if it's the new expiring token format
        try {
            $decodedToken = base64_decode($token);
            $parts = explode('|', $decodedToken);
            
            if (count($parts) !== 3) {
                Log::channel('api_security')->error('Invalid token format', [
                    'ip' => $request->ip(),
                    'endpoint' => $request->path(),
                    'token_parts' => count($parts),
                    'timestamp' => now()->toISOString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token format. Please generate a new token.'
                ], 401);
            }

            [$timestamp, $randomString, $secret] = $parts;

            // Validate secret
            if ($secret !== env('API_TOKEN_SECRET')) {
                Log::channel('api_security')->error('Invalid token secret', [
                    'ip' => $request->ip(),
                    'endpoint' => $request->path(),
                    'timestamp' => now()->toISOString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token. Access denied.'
                ], 401);
            }

            // Check if token is expired
            if (time() > $timestamp) {
                Log::channel('api_security')->warning('Expired token used', [
                    'ip' => $request->ip(),
                    'endpoint' => $request->path(),
                    'expired_at' => date('Y-m-d H:i:s', $timestamp),
                    'timestamp' => now()->toISOString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired. Please generate a new token.',
                    'expired_at' => date('Y-m-d H:i:s', $timestamp)
                ], 401);
            }

            // Log successful authentication
            Log::channel('api_access')->info('Token authentication successful', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'token_type' => 'expiring',
                'expires_at' => date('Y-m-d H:i:s', $timestamp),
                'timestamp' => now()->toISOString()
            ]);

            return $next($request);

        } catch (\Exception $e) {
            Log::channel('api_security')->error('Token validation exception', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid token format. Please generate a new token.'
            ], 401);
        }
    }
}
