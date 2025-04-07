<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    use HasFactory;

    protected $table = 'kelurahan';

    protected $primaryKey = 'kelurahan_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $casts = [
        'geojson_kelurahan' => 'json' // Pastikan Laravel membaca sebagai JSON
    ];

    protected $fillable = [
        'kelurahan_uuid',
        'nama_kelurahan',
        'warna_kelurahan',
        'geojson_kelurahan',
    ];
}
