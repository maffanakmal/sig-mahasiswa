<?php

namespace App\Imports;

use App\Models\Daerah;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DaerahImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $data = [];

        // Lewati baris pertama (heading)
        $rows = $rows->slice(1);

        $existingKodeDaerah = Daerah::pluck('kode_daerah')->toArray();
        $processedKodeDaerah = [];

        foreach ($rows as $row) {
            $kode = strtolower(trim($row[0] ?? ''));

            if (empty($kode)) {
                continue;
            }

            if (
                empty($kode) ||
                strlen($kode) < 4 || strlen($kode) > 10 || // validasi panjang kode_daerah
                in_array($kode, $existingKodeDaerah) || 
                in_array($kode, $processedKodeDaerah)
            ) {
                continue;
            }

            $data[] = [
                'daerah_uuid'      => Str::uuid(),
                'kode_daerah'      => $kode,
                'nama_daerah'      => $row[1] ?? '',
                'latitude_daerah'  => $row[2] ?? '',
                'longitude_daerah' => $row[3] ?? '',
            ];

            $processedKodeDaerah[] = $kode;
        }

        if (!empty($data)) {
            Daerah::insert($data);
        }
    }
}

