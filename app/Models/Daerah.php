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
        'daerah_uuid',
        'kode_daerah',
        'nama_daerah',
        'latitude',
        'longitude',
    ];

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'daerah_asal', 'kode_daerah');
    }

    public function sekolah()
    {
        return $this->hasMany(Sekolah::class, 'daerah_sekolah', 'kode_daerah');
    }

    
}

