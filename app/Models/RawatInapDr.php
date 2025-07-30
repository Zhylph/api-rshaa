<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawatInapDr extends Model
{
    protected $table = 'rawat_inap_dr';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'no_rawat', 'kd_jenis_prw', 'kd_dokter', 'tgl_perawatan', 'jam_rawat',
        'material', 'bhp', 'tarif_tindakandr', 'kso', 'menejemen', 'biaya_rawat'
    ];

    protected $casts = [
        'tgl_perawatan' => 'date',
        'material' => 'float',
        'bhp' => 'float',
        'tarif_tindakandr' => 'float',
        'kso' => 'float',
        'menejemen' => 'float',
        'biaya_rawat' => 'float'
    ];
}
