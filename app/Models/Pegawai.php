<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'nik', 'nama', 'jk', 'jbtn', 'jnj_jabatan', 'kode_kelompok',
        'kode_resiko', 'kode_emergency', 'departemen', 'bidang', 'stts_wp',
        'stts_kerja', 'npwp', 'pendidikan', 'gapok', 'tmp_lahir', 'tgl_lahir',
        'alamat', 'kota', 'mulai_kerja', 'ms_kerja', 'indexins', 'bpd',
        'rekening', 'stts_aktif'
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
        'mulai_kerja' => 'date',
        'gapok' => 'float'
    ];
}
