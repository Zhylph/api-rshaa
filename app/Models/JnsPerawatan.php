<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JnsPerawatan extends Model
{
    protected $table = 'jns_perawatan';
    protected $primaryKey = 'kd_jenis_prw';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kd_jenis_prw', 'nm_perawatan', 'kd_kategori', 'material', 'bhp',
        'tarif_tindakandr', 'tarif_tindakanpr', 'kso', 'menejemen',
        'total_byrdr', 'total_byrpr', 'total_byrdrpr', 'kd_pj',
        'kd_poli', 'status'
    ];

    protected $casts = [
        'material' => 'float',
        'bhp' => 'float',
        'tarif_tindakandr' => 'float',
        'tarif_tindakanpr' => 'float',
        'kso' => 'float',
        'menejemen' => 'float',
        'total_byrdr' => 'float',
        'total_byrpr' => 'float',
        'total_byrdrpr' => 'float'
    ];
}
