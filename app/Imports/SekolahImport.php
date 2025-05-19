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
        $daerahList = Daerah::pluck('kode_daerah', 'nama_daerah')->toArray();

        $data = [];
        $index = 1;

        foreach ($collection as $row) {
            if ($index > 1) {
                $namaDaerah = $row[1] ?? '';
                $kodeDaerah = $daerahList[$namaDaerah] ?? null;

                $data[] = [
                    'sekolah_uuid' => Str::uuid(),
                    'nama_sekolah' => $row[0] ?? '',
                    'daerah_sekolah' => $kodeDaerah,
                    'latitude_sekolah' => $row[2] ?? '',
                    'longitude_sekolah' => $row[3] ?? '',
                ];
            }

            $index++;
        }

        if (!empty($data)) {
            Sekolah::insert($data);
        }
    }
}
