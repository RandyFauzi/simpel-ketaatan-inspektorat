<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lhp;
use App\Models\Finding;
use App\Models\Recommendation;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Facades\DB;

class LhpService
{
    /**
     * Daftar field rich-text dari WYSIWYG yang wajib disanitasi.
     *
     * @var array<int, string>
     */
    private const RICH_TEXT_FIELDS = [
        'dasar_audit',
        'tujuan_audit',
        'metodologi_audit',
        'batasan_tanggung_jawab',
        'sasaran_audit',
        'ruang_lingkup',
        'periode_audit',
        'info_tujuan_program',
        'info_kegiatan_program',
        'info_lokasi_dana',
        'info_sumber_dana',
        'info_struktur_org',
        'penilaian_spi',
        'simpulan_audit',
        'penilaian_ketaatan',
        'kesesuaian_output',
        'hal_penting',
        'tindak_lanjut',
        'hal_penting_lainnya',
        'tindak_lanjut_sebelumnya',
        'simpulan_manual',
        'rekomendasi_manual',
        'penutup_manual',
        'bab_1_info_umum',
        'bab_2_hasil_audit',
        'bab_3_penutup',
    ];

    /**
     * Atomic Transaction: Simpan LHP + LhpContent + Findings + Recommendations sekaligus.
     */
    public function createFullLhp(array $data): Lhp
    {
        return DB::transaction(function () use ($data) {
            $data = $this->sanitizeRichTextInputs($data);
            $timPemeriksaList = $this->normalizeTimPemeriksa($data);
            $tembusanList = $this->normalizeTembusan($data);

            // 1. Insert Lhp Header
            $lhp = Lhp::create([
                'nomor_lhp'       => $data['nomor_lhp'],
                'tgl_lhp'         => $data['tgl_lhp'],
                'judul'           => $data['judul'],
                'tahun_anggaran'  => $data['tahun_anggaran'],
                'opd_id'          => $data['opd_id'],
                'simpulan_manual' => $data['simpulan_manual'] ?? null,
                'rekomendasi_manual' => $data['rekomendasi_manual'] ?? null,
                'penutup_manual' => $data['penutup_manual'] ?? null,
                'penilaian_ketaatan' => $data['penilaian_ketaatan'] ?? null,
                'kesesuaian_output' => $data['kesesuaian_output'] ?? null,
                'hal_penting' => $data['hal_penting'] ?? null,
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
                'created_by'      => auth()->id(),
                'status'          => 'draft',
                'tim'             => auth()->user() ? auth()->user()->tim : null,
            ]);

            // 2. Insert LhpContent (Bab I - Informasi Umum) with structured metadata
            $lhp->content()->create([
                'bab_1_info_umum'    => $data['bab_1_info_umum'] ?? null,
                'bab_2_hasil_audit'  => $data['bab_2_hasil_audit'] ?? null,
                'bab_3_penutup'      => $data['bab_3_penutup'] ?? null,
                'metadata_tambahan'  => [
                    'sifat' => $data['sifat'] ?? null,
                    'lampiran' => $data['lampiran'] ?? null,
                    'tujuan_surat' => $data['tujuan_surat'] ?? null,
                    'nomor_spt' => $data['nomor_spt'] ?? null,
                    'tanggal_spt' => $data['tanggal_spt'] ?? null,
                    'dasar_audit'   => $data['dasar_audit'] ?? null,
                    'tujuan_audit'  => $data['tujuan_audit'] ?? null,
                    'metodologi_audit' => $data['metodologi_audit'] ?? null,
                    'batasan_tanggung_jawab' => $data['batasan_tanggung_jawab'] ?? null,
                    'sasaran_audit' => $data['sasaran_audit'] ?? null,
                    'ruang_lingkup' => $data['ruang_lingkup'] ?? null,
                    'periode_audit' => $data['periode_audit'] ?? null,
                    'info_tujuan_program' => $data['info_tujuan_program'] ?? null,
                    'info_kegiatan_program' => $data['info_kegiatan_program'] ?? null,
                    'info_lokasi_dana' => $data['info_lokasi_dana'] ?? null,
                    'info_sumber_dana' => $data['info_sumber_dana'] ?? null,
                    'info_struktur_org' => $data['info_struktur_org'] ?? null,
                    'penilaian_spi' => $data['penilaian_spi'] ?? null,
                    'simpulan_audit' => $data['simpulan_audit'] ?? null,
                    'tim_pemeriksa' => $timPemeriksaList,
                    'tembusan' => $tembusanList,
                    // Backward compatibility untuk data lama/template lama.
                    'tembusan_1' => $tembusanList[0] ?? null,
                    'tembusan_2' => $tembusanList[1] ?? null,
                    ],
            ]);

            // 3. Insert Findings (Bab II) + nested Recommendations (Bab III) (BULK BATCH INSERT)
            if (!empty($data['findings'])) {
                $now = now();
                $findingsBatch = [];
                $recommendationsBatch = [];

                foreach ($data['findings'] as $fIndex => $findingData) {
                    $findingId = (string) str()->uuid();
                    $uraianTemuanRekomendasi = $this->sanitizeRichTextValue(
                        $findingData['uraian_temuan_rekomendasi'] ?? $findingData['uraian_temuan'] ?? ''
                    );
                    $findingsBatch[] = [
                        'id'              => $findingId,
                        'lhp_id'          => $lhp->id,
                        'kode_temuan'     => $findingData['kode_temuan'] ?: 'T-' . ($fIndex + 1),
                        'uraian_temuan'   => $uraianTemuanRekomendasi,
                        'kondisi'         => $findingData['kondisi'] ?? null,
                        'kriteria'        => $findingData['kriteria'] ?? null,
                        'sebab'           => $findingData['sebab'] ?? null,
                        'akibat'          => $findingData['akibat'] ?? null,
                        'rekomendasi_teks'=> null,
                        'kerugian_negara' => (float) ($findingData['kerugian_negara'] ?? 0),
                        'kerugian_daerah' => (float) ($findingData['kerugian_daerah'] ?? 0),
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];

                    // 4. Collect Recommendations per Finding
                    if (!empty($findingData['recommendations'])) {
                        foreach ($findingData['recommendations'] as $rIndex => $recData) {
                            $recommendationsBatch[] = [
                                'id'                 => (string) str()->uuid(),
                                'finding_id'         => $findingId,
                                'kode_rekomendasi'   => $recData['kode_rekomendasi'] ?: 'R-' . ($fIndex + 1) . '.' . ($rIndex + 1),
                                'uraian_rekomendasi' => $recData['uraian_rekomendasi'] ?? '',
                                'nilai_rekomendasi'  => (float) ($recData['nilai_rekomendasi'] ?? 0),
                                'status'             => 'belum_sesuai',
                                'created_at'         => $now,
                                'updated_at'         => $now,
                            ];
                        }
                    }
                }

                if (!empty($findingsBatch)) {
                    Finding::insert($findingsBatch);
                }
                
                if (!empty($recommendationsBatch)) {
                    Recommendation::insert($recommendationsBatch);
                }
            }

            return $lhp;
        });
    }

    /**
     * Atomic Transaction: Update LHP + LhpContent + Findings + Recommendations sekaligus.
     */
    public function updateFullLhp(string $lhpId, array $data): Lhp
    {
        return DB::transaction(function () use ($lhpId, $data) {
            $data = $this->sanitizeRichTextInputs($data);
            $timPemeriksaList = $this->normalizeTimPemeriksa($data);
            $tembusanList = $this->normalizeTembusan($data);
            $lhp = Lhp::findOrFail($lhpId);

            // 1. Update LHP Header
            $lhp->update([
                'nomor_lhp'       => $data['nomor_lhp'],
                'tgl_lhp'         => $data['tgl_lhp'],
                'judul'           => $data['judul'],
                'tahun_anggaran'  => $data['tahun_anggaran'],
                'opd_id'          => $data['opd_id'],
                'simpulan_manual' => $data['simpulan_manual'] ?? null,
                'rekomendasi_manual' => $data['rekomendasi_manual'] ?? null,
                'penutup_manual' => $data['penutup_manual'] ?? null,
                'penilaian_ketaatan' => $data['penilaian_ketaatan'] ?? null,
                'kesesuaian_output' => $data['kesesuaian_output'] ?? null,
                'hal_penting' => $data['hal_penting'] ?? null,
                'tindak_lanjut' => $data['tindak_lanjut'] ?? null,
                'status'          => 'draft', // Reset ke draft setelah revisi
            ]);

            // 3. Update LhpContent
            $lhp->content()->updateOrCreate(
                ['lhp_id' => $lhp->id],
                [
                    'bab_1_info_umum'    => $data['bab_1_info_umum'] ?? null,
                    'bab_2_hasil_audit'  => $data['bab_2_hasil_audit'] ?? null,
                    'bab_3_penutup'      => $data['bab_3_penutup'] ?? null,
                    'metadata_tambahan'  => [
                        'sifat' => $data['sifat'] ?? null,
                        'lampiran' => $data['lampiran'] ?? null,
                        'tujuan_surat' => $data['tujuan_surat'] ?? null,
                        'nomor_spt' => $data['nomor_spt'] ?? null,
                        'tanggal_spt' => $data['tanggal_spt'] ?? null,
                        'dasar_audit'   => $data['dasar_audit'] ?? null,
                        'tujuan_audit'  => $data['tujuan_audit'] ?? null,
                        'metodologi_audit' => $data['metodologi_audit'] ?? null,
                        'batasan_tanggung_jawab' => $data['batasan_tanggung_jawab'] ?? null,
                        'sasaran_audit' => $data['sasaran_audit'] ?? null,
                        'ruang_lingkup' => $data['ruang_lingkup'] ?? null,
                        'periode_audit' => $data['periode_audit'] ?? null,
                        'info_tujuan_program' => $data['info_tujuan_program'] ?? null,
                        'info_kegiatan_program' => $data['info_kegiatan_program'] ?? null,
                        'info_lokasi_dana' => $data['info_lokasi_dana'] ?? null,
                        'info_sumber_dana' => $data['info_sumber_dana'] ?? null,
                        'info_struktur_org' => $data['info_struktur_org'] ?? null,
                        'penilaian_spi' => $data['penilaian_spi'] ?? null,
                        'simpulan_audit' => $data['simpulan_audit'] ?? null,
                        'tim_pemeriksa' => $timPemeriksaList,
                        'tembusan' => $tembusanList,
                        // Backward compatibility untuk data lama/template lama.
                        'tembusan_1' => $tembusanList[0] ?? null,
                        'tembusan_2' => $tembusanList[1] ?? null,
                    ],
                ]
            );

            // 4. Delete semua Findings lama beserta Recommendations-nya, lalu reinsert
            foreach ($lhp->findings as $oldFinding) {
                $oldFinding->recommendations()->delete();
            }
            $lhp->findings()->delete();

            if (!empty($data['findings'])) {
                $now = now();
                $findingsBatch = [];
                $recommendationsBatch = [];

                foreach ($data['findings'] as $fIndex => $findingData) {
                    $findingId = (string) str()->uuid();
                    $uraianTemuanRekomendasi = $this->sanitizeRichTextValue(
                        $findingData['uraian_temuan_rekomendasi'] ?? $findingData['uraian_temuan'] ?? ''
                    );
                    $findingsBatch[] = [
                        'id'              => $findingId,
                        'lhp_id'          => $lhp->id,
                        'kode_temuan'     => $findingData['kode_temuan'] ?: 'T-' . ($fIndex + 1),
                        'uraian_temuan'   => $uraianTemuanRekomendasi,
                        'rekomendasi_teks'=> null,
                        'kerugian_negara' => (float) ($findingData['kerugian_negara'] ?? 0),
                        'kerugian_daerah' => (float) ($findingData['kerugian_daerah'] ?? 0),
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];

                    if (!empty($findingData['recommendations'])) {
                        foreach ($findingData['recommendations'] as $rIndex => $recData) {
                            $recommendationsBatch[] = [
                                'id'                 => (string) str()->uuid(),
                                'finding_id'         => $findingId,
                                'kode_rekomendasi'   => $recData['kode_rekomendasi'] ?: 'R-' . ($fIndex + 1) . '.' . ($rIndex + 1),
                                'uraian_rekomendasi' => $recData['uraian_rekomendasi'] ?? '',
                                'nilai_rekomendasi'  => (float) ($recData['nilai_rekomendasi'] ?? 0),
                                'status'             => 'belum_sesuai',
                                'created_at'         => $now,
                                'updated_at'         => $now,
                            ];
                        }
                    }
                }

                if (!empty($findingsBatch)) {
                    Finding::insert($findingsBatch);
                }
                if (!empty($recommendationsBatch)) {
                    Recommendation::insert($recommendationsBatch);
                }
            }

            return $lhp;
        });
    }

    /**
     * Finalize LHP → Published.
     */
    public function finalizeLhp(string $lhpId): Lhp
    {
        return DB::transaction(function () use ($lhpId) {
            $lhp = Lhp::findOrFail($lhpId);
            $lhp->update(['status' => 'published']);
            return $lhp;
        });
    }

    /**
     * Sanitasi massal field rich-text untuk menutup celah XSS dari editor.
     */
    private function sanitizeRichTextInputs(array $data): array
    {
        foreach (self::RICH_TEXT_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->sanitizeRichTextValue($data[$field]);
            }
        }

        return $data;
    }

    private function sanitizeRichTextValue(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        return Purifier::clean($value, 'audit_wysiwyg');
    }

    /**
     * Normalisasi daftar Tim Pemeriksa dari payload baru/legacy.
     *
     * @param array<string, mixed> $data
     * @return array<int, string>
     */
    private function normalizeTimPemeriksa(array $data): array
    {
        $rawItems = [];

        if (!empty($data['metadata_tambahan']['tim_pemeriksa']) && is_array($data['metadata_tambahan']['tim_pemeriksa'])) {
            $rawItems = $data['metadata_tambahan']['tim_pemeriksa'];
        } elseif (!empty($data['tim_pemeriksa']) && is_array($data['tim_pemeriksa'])) {
            $rawItems = $data['tim_pemeriksa'];
        } elseif (!empty($data['metadata_tambahan']['tembusan']) && is_array($data['metadata_tambahan']['tembusan'])) {
            $rawItems = $data['metadata_tambahan']['tembusan'];
        } elseif (!empty($data['tembusan']) && is_array($data['tembusan'])) {
            $rawItems = $data['tembusan'];
        } else {
            $rawItems = [
                $data['metadata_tambahan']['tembusan_1'] ?? null,
                $data['metadata_tambahan']['tembusan_2'] ?? null,
                $data['tembusan_1'] ?? null,
                $data['tembusan_2'] ?? null,
            ];
        }

        return collect($rawItems)
            ->map(fn ($item) => is_string($item) ? trim(strip_tags($item)) : null)
            ->filter(fn ($item) => is_string($item) && $item !== '')
            ->values()
            ->all();
    }

    /**
     * Normalisasi daftar Tembusan dari payload baru/legacy.
     *
     * @param array<string, mixed> $data
     * @return array<int, string>
     */
    private function normalizeTembusan(array $data): array
    {
        $rawItems = [];

        if (!empty($data['metadata_tambahan']['tembusan']) && is_array($data['metadata_tambahan']['tembusan'])) {
            $rawItems = $data['metadata_tambahan']['tembusan'];
        } elseif (!empty($data['tembusan']) && is_array($data['tembusan'])) {
            $rawItems = $data['tembusan'];
        } else {
            $rawItems = [
                $data['metadata_tambahan']['tembusan_1'] ?? null,
                $data['metadata_tambahan']['tembusan_2'] ?? null,
                $data['tembusan_1'] ?? null,
                $data['tembusan_2'] ?? null,
            ];
        }

        return collect($rawItems)
            ->map(fn ($item) => is_string($item) ? trim(strip_tags($item)) : null)
            ->filter(fn ($item) => is_string($item) && $item !== '')
            ->values()
            ->all();
    }
}
