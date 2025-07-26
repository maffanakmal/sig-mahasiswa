<?php

namespace App\Exports;

use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class MahasiswaExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('mahasiswa')
            ->leftJoin('prodi', 'mahasiswa.kode_prodi', '=', 'prodi.kode_prodi')
            ->leftJoin('sekolah', 'mahasiswa.npsn', '=', 'sekolah.npsn')
            ->leftJoin('daerah', 'sekolah.kode_daerah', '=', 'daerah.kode_daerah')
            ->select(
                'mahasiswa.nim',
                'mahasiswa.tahun_masuk',
                'prodi.nama_prodi',
                'sekolah.nama_sekolah',
                'daerah.nama_daerah'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nomor Induk Mahasiswa',
            'Tahun Masuk',
            'Program Studi',
            'Sekolah Asal',
            'Daerah Asal',
        ];
    }
}
