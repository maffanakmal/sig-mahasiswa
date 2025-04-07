<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $primaryKey = 'mahasiswa_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'mahasiswa_uuid',
        'mahasiswa_nama',
        'nim',
        'tahun_masuk',
        'jurusan',
        'sekolah_asal',
        'daerah_asal',
        'status_mahasiswa',
    ];
}
