<?php

namespace App\Exports;

use App\Models\Jurusan;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class JurusanExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Jurusan::select('kode_prodi', 'nama_prodi')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Program Studi',
            'Nama Program Studi',
        ];
    }
}

