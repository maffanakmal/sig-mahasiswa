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
        $index = 1;

        foreach ($collection as $row) {
            if ($index > 1) {
                $data[] = [
                    'jurusan_uuid' => Str::uuid(),
                    'kode_jurusan' => $row[0] ?? '',
                    'nama_jurusan' => $row[1] ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $index++;
        }

        if (!empty($data)) {
            Jurusan::insert($data);
        }
    }
}
