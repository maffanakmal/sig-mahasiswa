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
        $processedKodeJurusan = [];

        // Ambil semua kode_jurusan yang sudah ada di database
        $existingKodeJurusan = Jurusan::pluck('kode_jurusan')->toArray();

        foreach ($collection as $index => $row) {
            // Lewati baris pertama (header)
            if ($index === 0) {
                continue;
            }

            $kode = trim($row[0] ?? '');
            $nama = trim($row[1] ?? '');

            // Skip jika salah satu kolom kosong
            if (empty($kode) || empty($nama)) {
                continue;
            }

            // Skip jika duplikat di DB atau di file Excel
            if (in_array($kode, $existingKodeJurusan) || in_array($kode, $processedKodeJurusan)) {
                continue;
            }

            $data[] = [
                'jurusan_uuid' => Str::uuid(),
                'kode_jurusan' => $kode,
                'nama_jurusan' => $nama,
            ];

            $processedKodeJurusan[] = $kode;
        }

        if (!empty($data)) {
            Jurusan::insert($data);
        }
    }
}
