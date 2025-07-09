<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $primaryKey = 'nim';

    public $incrementing = false;

    protected $keyType = 'integer';

    protected $fillable = [
        'mahasiswa_uuid',
        'nim',
        'tahun_masuk',
        'kode_prodi',
        'npsn',
        'daerah_asal',
    ];

    public function daerah()
    {
        return $this->belongsTo(Daerah::class, 'kode_daerah', 'kode_daerah');
    }

    public function prodi()
    {
        return $this->belongsTo(Jurusan::class, 'kode_prodi', 'kode_prodi');
    }

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'npsn', 'npsn');
    }
}
