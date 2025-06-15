<?php

namespace App\Imports;

use App\Models\Daerah;
use App\Models\Sekolah;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class SekolahImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $data = [];

        // Ambil semua NPSN yang sudah ada di database
        $existingNpsn = Sekolah::pluck('npsn')->toArray();

        // Ambil semua nama daerah dari database dan ubah ke lowercase
        $daerahList = Daerah::pluck('kode_daerah', 'nama_daerah')
            ->mapWithKeys(function ($kode, $nama) {
                return [strtolower(trim($nama)) => $kode];
            })
            ->toArray();

        // Simpan NPSN yang sudah diproses dari file Excel
        $processedNpsn = [];

        foreach ($collection as $index => $row) {
            // Lewati baris pertama (header)
            if ($index === 0) {
                continue;
            }

            $npsn = $row[0] ?? null;

            // Validasi: hanya proses baris dengan NPSN numerik
            if (!is_numeric($npsn)) {
                continue;
            }

            $npsn = (int) $npsn;

            // Skip jika NPSN sudah ada di DB atau sudah diproses di file ini
            if (in_array($npsn, $existingNpsn) || in_array($npsn, $processedNpsn)) {
                continue;
            }

            $namaDaerah = strtolower(trim($row[3] ?? ''));
            $kodeDaerah = $daerahList[$namaDaerah] ?? null;

            $data[] = [
                'sekolah_uuid' => Str::uuid(),
                'npsn' => $npsn,
                'nama_sekolah' => $row[1] ?? '',
                'alamat_sekolah' => $row[2] ?? '',
                'kode_daerah' => $kodeDaerah,
                'latitude_sekolah' => $row[4] ?? null,
                'longitude_sekolah' => $row[5] ?? null,
            ];

            $processedNpsn[] = $npsn;
        }

        // Insert jika ada data valid
        if (!empty($data)) {
            Sekolah::insert($data);
        }
    }
}
