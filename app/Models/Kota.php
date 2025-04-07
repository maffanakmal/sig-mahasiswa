<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kota extends Model
{
    use HasFactory;

    protected $table = 'kota';

    protected $primaryKey = 'kota_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'kota_uuid',
        'nama_kota',
        'warna_kota',
        'geojson_kota',
    ];
}
