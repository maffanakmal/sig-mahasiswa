<?php

namespace App\Imports;

use App\Models\Daerah;
use App\Models\Sekolah;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SekolahImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $data = [];

        $existingNpsn = Sekolah::pluck('npsn')->toArray();

        // Ambil semua nama daerah dari DB, lowercase + trim untuk pencocokan
        $daerahList = Daerah::pluck('kode_daerah', 'nama_daerah')
            ->mapWithKeys(function ($kode, $nama) {
                return [strtolower(trim($nama)) => $kode];
            })
            ->toArray();

        $processedNpsn = [];

        // Lewati baris pertama jika itu adalah header
        $rows = $rows->slice(1); // <-- ini penting kalau baris pertama adalah header manual

        foreach ($rows as $row) {
            $npsn = $row[0] ?? null;

            if (!is_numeric($npsn)) {
                continue;
            }

            $npsn = (int) $npsn;

            if (in_array($npsn, $existingNpsn) || in_array($npsn, $processedNpsn)) {
                continue;
            }

            $namaDaerah = strtolower(trim($row[3] ?? ''));
            $kodeDaerah = $daerahList[$namaDaerah] ?? null;

            $data[] = [
                'sekolah_uuid'     => Str::uuid(),
                'npsn'             => $npsn,
                'nama_sekolah'     => $row[1] ?? '',
                'alamat_sekolah'   => $row[2] ?? '',
                'kode_daerah'      => $kodeDaerah,
                'latitude_sekolah' => $row[4] ?? '',
                'longitude_sekolah'=> $row[5] ?? '',
            ];

            $processedNpsn[] = $npsn;
        }

        if (!empty($data)) {
            Sekolah::insert($data);
        }
    }
}
