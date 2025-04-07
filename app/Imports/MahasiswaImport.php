<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class MahasiswaImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $index = 1;

        foreach ($collection as $row) {
            // Skip the first row (header)
            if ($index > 1) {
                $data['mahasiswa_uuid'] = Str::uuid();
                $data['nama_mahasiswa'] = !empty($row[0]) ? $row[0] : '';
                $data['nim'] = !empty($row[1]) ? $row[1] : '';
                $data['tahun_masuk'] = !empty($row[2]) ? $row[2] : '';
                $data['jurusan'] = !empty($row[3]) ? $row[3] : '';
                $data['sekolah_asal'] = !empty($row[4]) ? $row[4] : '';
                $data['daerah_asal'] = !empty($row[5]) ? $row[5] : '';
                $data['status_mahasiswa'] = !empty($row[6]) ? $row[6] : '';
                $data['created_at'] = now();
                $data['updated_at'] = now();

                // Insert the data into the database
                Mahasiswa::insert($data);
            }

            $index++;
        }
    }
}
