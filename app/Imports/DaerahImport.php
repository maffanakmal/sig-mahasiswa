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
        $index = 1;

        foreach ($collection as $row) {
            // Skip the first row (header)
            if ($index > 1) {
                $data['daerah_uuid'] = Str::uuid();
                $data['kode_daerah'] = !empty($row[0]) ? $row[0] : '';
                $data['nama_daerah'] = !empty($row[1]) ? $row[1] : '';
                $data['latitude'] = !empty($row[2]) ? $row[2] : '';
                $data['longitude'] = !empty($row[3]) ? $row[3] : '';
                $data['created_at'] = now();
                $data['updated_at'] = now();

                // Insert the data into the database
                Daerah::insert($data);
            }

            $index++;
        }
    }
}
