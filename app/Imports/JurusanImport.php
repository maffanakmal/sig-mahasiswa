<?php

namespace App\Imports;

use App\Models\Jurusan;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JurusanImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $data = [];
        $processedKodeProdi = [];

        $existingKodeProdi = Jurusan::pluck('kode_prodi')->toArray();

        foreach ($rows as $row) {
            $kode = trim($row['kode_prodi'] ?? '');
            $nama = trim($row['nama_prodi'] ?? '');

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
