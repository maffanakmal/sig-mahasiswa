<?php

namespace App\Imports;

use App\Models\Daerah;
use App\Models\Jurusan;
use App\Models\Sekolah;
use App\Models\Mahasiswa;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MahasiswaImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $data = [];

        $daerahList = Daerah::pluck('kode_daerah', 'nama_daerah')
            ->mapWithKeys(fn($kode, $nama) => [strtolower(trim($nama)) => $kode])
            ->toArray();

        $prodiList = Jurusan::pluck('kode_prodi', 'nama_prodi')
            ->mapWithKeys(fn($kode, $nama) => [strtolower(trim($nama)) => $kode])
            ->toArray();

        $sekolahList = Sekolah::pluck('npsn', 'nama_sekolah')
            ->mapWithKeys(fn($npsn, $nama) => [strtolower(trim($nama)) => $npsn])
            ->toArray();

        $existingNIMs = Mahasiswa::pluck('nim')->toArray();
        $processedNIMs = [];

        // Lewati baris pertama jika itu heading
        $rows = $rows->slice(1);

        foreach ($rows as $row) {
            $nim = trim((string)($row[0] ?? ''));              // NIM
            $tahunMasuk = (int)($row[1] ?? 0);                 // Tahun Masuk

            $namaProdi = strtolower(trim($row[2] ?? ''));      // Program Studi
            $kodeProdi = $prodiList[$namaProdi] ?? null;

            $namaSekolah = strtolower(trim($row[3] ?? ''));    // Sekolah Asal
            $npsn = $sekolahList[$namaSekolah] ?? null;

            $namaDaerah = strtolower(trim($row[4] ?? ''));     // Daerah Asal
            $kodeDaerah = $daerahList[$namaDaerah] ?? null;

            if (
                !$nim ||
                strlen($nim) < 9 || strlen($nim) > 10 || // cek panjang NIM
                in_array($nim, $existingNIMs) ||
                in_array($nim, $processedNIMs)
            ) {
                continue;
            }

            $data[] = [
                'mahasiswa_uuid' => Str::uuid(),
                'nim'            => $nim,
                'tahun_masuk'    => $tahunMasuk,
                'kode_prodi'     => $kodeProdi,
                'npsn'           => $npsn,
                'kode_daerah'    => $kodeDaerah,
            ];

            $processedNIMs[] = $nim;
        }

        if (!empty($data)) {
            Mahasiswa::insert($data);
        }
    }
}