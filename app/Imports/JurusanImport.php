<?php

namespace App\Imports;

use App\Models\Jurusan;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JurusanImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $data = [];

        $existingKodeJurusan = Jurusan::pluck('kode_prodi')->toArray();
        $processedKodeJurusan = [];

        // Lewati baris pertama jika itu header
        $rows = $rows->slice(1);

        foreach ($rows as $row) {
            $kode = trim((string)($row[0] ?? ''));

            if (empty($kode)) {
                continue;
            }

            if (
                empty($kode) ||
                strlen($kode) < 5 || strlen($kode) > 10 ||
                in_array($kode, $existingKodeJurusan) ||
                in_array($kode, $processedKodeJurusan)
            ) {
                continue;
            }

            $data[] = [
                'prodi_uuid' => Str::uuid(),
                'kode_prodi' => $kode,
                'nama_prodi' => $row[1] ?? '',
            ];

            $processedKodeJurusan[] = $kode;
        }

        if (!empty($data)) {
            Jurusan::insert($data);
        }
    }
}
