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
        'jurusan',
        'sekolah_asal',
        'daerah_asal',
    ];

    public function daerah()
    {
        return $this->belongsTo(Daerah::class, 'daerah_asal', 'kode_daerah');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan', 'kode_jurusan');
    }

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_asal', 'sekolah_id');
    }
}
