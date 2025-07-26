<?php

namespace App\Exports;

use App\Models\Sekolah;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class SekolahExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('sekolah')
            ->join('daerah', 'sekolah.kode_daerah', '=', 'daerah.kode_daerah')
            ->select(
                'sekolah.npsn',
                'sekolah.nama_sekolah',
                'sekolah.alamat_sekolah',
                'daerah.nama_daerah as nama_daerah',
                'sekolah.latitude_sekolah',
                'sekolah.longitude_sekolah'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nomor Pokok Sekolah Nasional',
            'Nama Sekolah',
            'Alamat Sekolah',
            'Nama Daerah',
            'Latitude Sekolah',
            'Longitude Sekolah',
        ];
    }
}
