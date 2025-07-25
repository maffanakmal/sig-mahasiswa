<?php

namespace App\Imports;

use App\Models\Daerah;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DaerahImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $data = [];

        // Ambil semua kode_daerah yang sudah ada di database
        $existingKodeDaerah = Daerah::pluck('kode_daerah')->toArray();

        $processedKodeDaerah = [];

        foreach ($rows as $row) {
            $kode = $row['kode_daerah'] ?? '';

            // Skip jika kosong
            if (empty($kode)) {
                continue;
            }

            // Skip jika duplikat di database atau sudah diproses
            if (in_array($kode, $existingKodeDaerah) || in_array($kode, $processedKodeDaerah)) {
                continue;
            }

            $data[] = [
                'daerah_uuid'      => Str::uuid(),
                'kode_daerah'      => $kode,
                'nama_daerah'      => $row['nama_daerah'] ?? '',
                'latitude_daerah'  => $row['latitude_daerah'] ?? '',
                'longitude_daerah' => $row['longitude_daerah'] ?? '',
            ];

            $processedKodeDaerah[] = $kode;
        }

        if (!empty($data)) {
            Daerah::insert($data);
        }
    }
}
