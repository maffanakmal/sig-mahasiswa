<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Daerah extends Model
{
    use HasFactory;

    protected $table = 'daerah';

    protected $primaryKey = 'daerah_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'daerah_uuid',
        'kode_daerah',
        'nama_daerah',
        'latitude',
        'longitude',
    ];
}
