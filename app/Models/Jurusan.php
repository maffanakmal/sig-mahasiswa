<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'prodi';

    protected $primaryKey = 'kode_prodi';

    public $incrementing = false;

    protected $keyType = 'integer';

    protected $fillable = [
        'prodi_uuid',
        'kode_prodi',
        'nama_prodi',
    ];
}
