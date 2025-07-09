<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Daerah extends Model
{
    use HasFactory;

    protected $table = 'daerah';

    protected $primaryKey = 'kode_daerah';

    public $incrementing = false;

    protected $keyType = 'integer';

    protected $fillable = [
        'kode_daerah',
        'daerah_uuid',
        'nama_daerah',
        'latitude_daerah',
        'longitude_daerah',
    ];

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'kode_daerah', 'kode_daerah');
    }

    public function sekolah()
    {
        return $this->hasMany(Sekolah::class, 'kode_daerah', 'kode_daerah');
    }

    
}

