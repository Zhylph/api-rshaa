<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        
        if (!$token) {
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
            return $next($request);
        }

        // Check if it's the new expiring token format
        try {
            $decodedToken = base64_decode($token);
            $parts = explode('|', $decodedToken);
            
            if (count($parts) !== 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token format. Please generate a new token.'
                ], 401);
            }

            [$timestamp, $randomString, $secret] = $parts;

            // Validate secret
            if ($secret !== env('API_TOKEN_SECRET')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token. Access denied.'
                ], 401);
            }

            // Check if token is expired
            if (time() > $timestamp) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token has expired. Please generate a new token.',
                    'expired_at' => date('Y-m-d H:i:s', $timestamp)
                ], 401);
            }

            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token format. Please generate a new token.'
            ], 401);
        }
    }
}
