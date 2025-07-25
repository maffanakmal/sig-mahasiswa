<?php

namespace App\Imports;

use App\Models\Daerah;
use App\Models\Sekolah;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SekolahImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $data = [];

        $existingNpsn = Sekolah::pluck('npsn')->toArray();

        // Ambil semua nama daerah dari DB, jadikan lowercase untuk pencocokan
        $daerahList = Daerah::pluck('kode_daerah', 'nama_daerah')
            ->mapWithKeys(function ($kode, $nama) {
                return [strtolower(trim($nama)) => $kode];
            })
            ->toArray();

        $processedNpsn = [];

        foreach ($rows as $row) {
            $npsn = $row['npsn'] ?? null;

            if (!is_numeric($npsn)) {
                continue;
            }

            $npsn = (int) $npsn;

            if (in_array($npsn, $existingNpsn) || in_array($npsn, $processedNpsn)) {
                continue;
            }

            $namaDaerah = strtolower(trim($row['nama_daerah'] ?? ''));
            $kodeDaerah = $daerahList[$namaDaerah] ?? null;

            $data[] = [
                'sekolah_uuid' => Str::uuid(),
                'npsn' => $npsn,
                'nama_sekolah' => $row['nama_sekolah'] ?? '',
                'alamat_sekolah' => $row['alamat_sekolah'] ?? '',
                'kode_daerah' => $kodeDaerah,
                'latitude_sekolah' => $row['latitude_sekolah'] ?? null,
                'longitude_sekolah' => $row['longitude_sekolah'] ?? null,
            ];

            $processedNpsn[] = $npsn;
        }

        if (!empty($data)) {
            Sekolah::insert($data);
        }
    }
}
