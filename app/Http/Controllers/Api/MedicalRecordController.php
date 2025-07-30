<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\RawatInapDr;
use App\Models\RawatJlDr;
use App\Models\JnsPerawatanInap;
use App\Models\JnsPerawatan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class MedicalRecordController extends Controller
{
    /**
     * Get pegawai data by NIK
     */
    public function getPegawai(Request $request)
    {
        try {
            $nik = $request->query('nik');
            
            if (!$nik) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter NIK is required'
                ], 400);
            }

            $pegawai = Pegawai::where('nik', $nik)->first();

            if (!$pegawai) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pegawai not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data pegawai retrieved successfully',
                'data' => $pegawai
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rawat inap dokter data by month and year
     */
    public function getRawatInapDr(Request $request)
    {
        try {
            $bulan = $request->query('bulan');
            $tahun = $request->query('tahun');
            
            if (!$bulan || !$tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter bulan and tahun are required'
                ], 400);
            }

            // Validate month and year
            if ($bulan < 1 || $bulan > 12) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid month. Must be between 1-12'
                ], 400);
            }

            if ($tahun < 1900 || $tahun > date('Y')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid year'
                ], 400);
            }

            $data = RawatInapDr::whereMonth('tgl_perawatan', $bulan)
                             ->whereYear('tgl_perawatan', $tahun)
                             ->orderBy('tgl_perawatan', 'desc')
                             ->get();

            return response()->json([
                'success' => true,
                'message' => "Data rawat inap dokter for {$bulan}/{$tahun} retrieved successfully",
                'data' => $data,
                'total_records' => $data->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rawat jalan dokter data by month and year
     */
    public function getRawatJlDr(Request $request)
    {
        try {
            $bulan = $request->query('bulan');
            $tahun = $request->query('tahun');
            
            if (!$bulan || !$tahun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parameter bulan and tahun are required'
                ], 400);
            }

            // Validate month and year
            if ($bulan < 1 || $bulan > 12) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid month. Must be between 1-12'
                ], 400);
            }

            if ($tahun < 1900 || $tahun > date('Y')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid year'
                ], 400);
            }

            $data = RawatJlDr::whereMonth('tgl_perawatan', $bulan)
                           ->whereYear('tgl_perawatan', $tahun)
                           ->orderBy('tgl_perawatan', 'desc')
                           ->get();

            return response()->json([
                'success' => true,
                'message' => "Data rawat jalan dokter for {$bulan}/{$tahun} retrieved successfully",
                'data' => $data,
                'total_records' => $data->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all jenis perawatan inap data
     */
    public function getJnsPerawatanInap()
    {
        try {
            $data = JnsPerawatanInap::orderBy('kd_jenis_prw')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data jenis perawatan inap retrieved successfully',
                'data' => $data,
                'total_records' => $data->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all jenis perawatan data
     */
    public function getJnsPerawatan()
    {
        try {
            $data = JnsPerawatan::orderBy('kd_jenis_prw')->get();

            return response()->json([
                'success' => true,
                'message' => 'Data jenis perawatan retrieved successfully',
                'data' => $data,
                'total_records' => $data->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate or refresh API token with expiry
     */
    public function generateToken(Request $request)
    {
        $adminKey = $request->input('admin_key');
        
        if ($adminKey !== 'rsaz_admin_2025') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid admin key'
            ], 401);
        }

        // Generate token with timestamp and random string
        $timestamp = now()->addMonth()->timestamp; // Expires in 1 month
        $randomString = \Illuminate\Support\Str::random(32);
        $token = base64_encode($timestamp . '|' . $randomString . '|' . env('API_TOKEN_SECRET'));

        // Store token info (in real app, store in database)
        $expiresAt = now()->addMonth();

        return response()->json([
            'success' => true,
            'message' => 'Token generated successfully',
            'token' => $token,
            'expires_at' => $expiresAt->toISOString(),
            'expires_in' => '30 days (1 month)',
            'usage' => 'Include this token in Authorization header as: Bearer {token}'
        ]);
    }

    /**
     * Check token validity and expiration
     */
    public function checkToken(Request $request)
    {
        $token = $request->header('Authorization');
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'No token provided',
                'valid' => false
            ]);
        }

        // Remove "Bearer " prefix if present
        $token = str_replace('Bearer ', '', $token);

        // Check if it's the old permanent token format
        $permanentToken = env('API_TOKEN_SECRET');
        if ($token === $permanentToken) {
            return response()->json([
                'success' => true,
                'message' => 'Token is valid (permanent token)',
                'valid' => true,
                'type' => 'permanent',
                'expires_at' => null
            ]);
        }

        // Check if it's the new expiring token format
        try {
            $decodedToken = base64_decode($token);
            $parts = explode('|', $decodedToken);
            
            if (count($parts) !== 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token format',
                    'valid' => false
                ]);
            }

            [$timestamp, $randomString, $secret] = $parts;

            // Validate secret
            if ($secret !== env('API_TOKEN_SECRET')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token',
                    'valid' => false
                ]);
            }

            // Check if token is expired
            $currentTime = time();
            $isExpired = $currentTime > $timestamp;
            
            return response()->json([
                'success' => true,
                'message' => $isExpired ? 'Token is expired' : 'Token is valid',
                'valid' => !$isExpired,
                'type' => 'expiring',
                'expires_at' => date('Y-m-d H:i:s', $timestamp),
                'current_time' => date('Y-m-d H:i:s', $currentTime),
                'time_remaining' => $isExpired ? 0 : ($timestamp - $currentTime) . ' seconds'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token format',
                'valid' => false
            ]);
        }
    }
}
