<?php

namespace App\Exports;

use App\Models\Daerah;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class DaerahExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Daerah::select('kode_daerah', 'nama_daerah', 'latitude_daerah', 'longitude_daerah')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Daerah',
            'Nama Daerah',
            'Latitude Daerah',
            'Longitude Daerah',
        ];
    }
}
