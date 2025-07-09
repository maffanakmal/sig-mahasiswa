<?php

namespace App\Imports;

use App\Models\Jurusan;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class JurusanImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = [];
        $processedKodeProdi = [];

        $existingKodeProdi = Jurusan::pluck('kode_prodi')->toArray();

        foreach ($collection as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $kode = trim($row[0] ?? '');
            $nama = trim($row[1] ?? '');

            if (empty($kode) || empty($nama)) {
                continue;
            }

            if (in_array($kode, $existingKodeProdi) || in_array($kode, $processedKodeProdi)) {
                continue;
            }

            $data[] = [
                'prodi_uuid' => Str::uuid(),
                'kode_prodi' => $kode,
                'nama_prodi' => $nama,
            ];

            $processedKodeProdi[] = $kode;
        }

        if (!empty($data)) {
            Jurusan::insert($data);
        }
    }
}
