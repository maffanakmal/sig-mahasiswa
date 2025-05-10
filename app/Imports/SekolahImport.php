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
        $index = 1;

        foreach ($collection as $row) {
            if ($index > 1) {
                $namaDaerah = $row[1] ?? '';
                $kodeDaerah = Daerah::where('nama_daerah', $namaDaerah)->value('kode_daerah');

                // Jika tidak ditemukan, beri kode default 404
                if (!$kodeDaerah) {
                    $kodeDaerah = '404';
                }

                $data[] = [
                    'sekolah_uuid' => Str::uuid(),
                    'nama_sekolah' => $row[0] ?? '',
                    'daerah_sekolah' => $kodeDaerah,
                    'latitude' => $row[2] ?? '',
                    'longitude' => $row[3] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $index++;
        }

        if (!empty($data)) {
            Sekolah::insert($data);
        }
    }
}
