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
        $data = [];

        // Ambil referensi dan ubah key menjadi lowercase
        $daerahList = Daerah::pluck('kode_daerah', 'nama_daerah')
            ->mapWithKeys(fn($kode, $nama) => [strtolower(trim($nama)) => $kode])
            ->toArray();

        $jurusanList = Jurusan::pluck('kode_jurusan', 'nama_jurusan')
            ->mapWithKeys(fn($kode, $nama) => [strtolower(trim($nama)) => $kode])
            ->toArray();

        $sekolahList = Sekolah::pluck('npsn', 'nama_sekolah')
            ->mapWithKeys(fn($npsn, $nama) => [strtolower(trim($nama)) => $npsn])
            ->toArray();

        // Ambil NIM yang sudah ada untuk hindari duplikat
        $existingNIMs = Mahasiswa::pluck('nim')->toArray();
        $processedNIMs = [];

        foreach ($collection as $index => $row) {
            if ($index === 0 || !isset($row[0]) || !is_numeric($row[0])) {
                continue;
            }

            $nim = trim((string)$row[0]);
            $tahunMasuk = (int)($row[1] ?? 0);

            $namaJurusan = strtolower(trim($row[2] ?? ''));
            $kodeJurusan = $jurusanList[$namaJurusan] ?? null;

            $namaSekolah = strtolower(trim($row[3] ?? ''));
            $npsn = $sekolahList[$namaSekolah] ?? null;

            $namaDaerah = strtolower(trim($row[4] ?? ''));
            $kodeDaerah = $daerahList[$namaDaerah] ?? null;

            // Hindari NIM duplikat
            if (in_array($nim, $existingNIMs) || in_array($nim, $processedNIMs)) {
                continue;
            }

            $data[] = [
                'mahasiswa_uuid' => Str::uuid(),
                'nim' => $nim,
                'tahun_masuk' => $tahunMasuk,
                'kode_jurusan' => $kodeJurusan,
                'npsn' => $npsn,
                'kode_daerah' => $kodeDaerah,
            ];

            $processedNIMs[] = $nim;
        }

        if (!empty($data)) {
            Mahasiswa::insert($data);
        }
    }
}
