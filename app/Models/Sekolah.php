<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolah';

    protected $primaryKey = 'sekolah_id';

    public $incrementing = true;

    protected $keyType = 'integer';

    protected $fillable = [
        'sekolah_uuid',
        'nama_sekolah',
        'daerah_sekolah',
        'latitude',
        'longitude',
    ];

    public function daerah()
    {
        return $this->belongsTo(Daerah::class, 'daerah_sekolah', 'kode_daerah');
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'sekolah_asal', 'sekolah_id');
    }
}
