<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolah';

    protected $primaryKey = 'npsn';

    public $incrementing = false;

    protected $keyType = 'integer';

    protected $fillable = [
        'npsn',
        'sekolah_uuid',
        'nama_sekolah',
        'alamat_sekolah',
        'kode_daerah',
        'latitude_daerah',
        'longitude_daerah',
    ];

    public function daerah()
    {
        return $this->belongsTo(Daerah::class, 'kode_daerah', 'kode_daerah');
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'npsn', 'npsn');
    }
}
