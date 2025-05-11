<?php

namespace App\Imports;

use App\Models\Daerah;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DaerahImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = [];
        $index = 1;

        // Ambil semua kode_daerah yang sudah ada di database
        $existingKodeDaerah = Daerah::pluck('kode_daerah')->toArray();

        // Simpan kode_daerah yang sudah diproses dari file Excel
        $processedKodeDaerah = [];

        foreach ($collection as $row) {
            if ($index > 1) {
                $kode = $row[0] ?? '';

                // Skip jika kosong
                if (empty($kode)) {
                    $index++;
                    continue;
                }

                // Skip jika duplikat di database atau sudah diproses sebelumnya
                if (in_array($kode, $existingKodeDaerah) || in_array($kode, $processedKodeDaerah)) {
                    $index++;
                    continue;
                }

                $data[] = [
                    'daerah_uuid' => Str::uuid(),
                    'kode_daerah' => $kode,
                    'nama_daerah' => $row[1] ?? '',
                    'latitude' => $row[2] ?? '',
                    'longitude' => $row[3] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Tambahkan ke array kode yang sudah diproses
                $processedKodeDaerah[] = $kode;
            }

            $index++;
        }

        if (!empty($data)) {
            Daerah::insert($data);
        }
    }
}
