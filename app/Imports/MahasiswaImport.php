<?php

namespace App\Imports;

use App\Models\Daerah;
use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\Sekolah;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class MahasiswaImport implements ToCollection
{
    public function collection(Collection $collection)
    {
        // Ambil semua referensi satu kali untuk efisiensi
        $daerahList = Daerah::pluck('kode_daerah', 'nama_daerah')->toArray();
        $jurusanList = Jurusan::pluck('kode_jurusan', 'nama_jurusan')->toArray();
        $sekolahList = Sekolah::pluck('sekolah_id', 'nama_sekolah')->toArray();

        $data = [];
        $index = 1;

        foreach ($collection as $row) {
            if ($index > 1) {
                $namaDaerah = $row[4] ?? '';
                $kodeDaerah = $daerahList[$namaDaerah] ?? null;

                $namaJurusan = $row[2] ?? '';
                $kodeJurusan = $jurusanList[$namaJurusan] ?? null;

                $namaSekolah = $row[3] ?? '';
                $kodeSekolah = $sekolahList[$namaSekolah] ?? null;

                $data[] = [
                    'mahasiswa_uuid' => Str::uuid(),
                    'nim' => $row[0] ?? '',
                    'tahun_masuk' => $row[1] ?? '',
                    'jurusan' => $kodeJurusan,
                    'sekolah_asal' => $kodeSekolah,
                    'daerah_asal' => $kodeDaerah,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $index++;
        }

        if (!empty($data)) {
            Mahasiswa::insert($data);
        }
    }
}
