@extends('pdf.lhp-master')

@section('content')

    @php
        $renderRichText = function ($value, $fallback = '................................................................') {
            if (!is_string($value) || trim($value) === '') {
                return $fallback;
            }
            $sanitized = \Mews\Purifier\Facades\Purifier::clean($value, 'audit_wysiwyg');
            // Bersihkan karakter artefak copy/paste yang kadang dirender DOMPDF menjadi "?" di baris terpisah.
            $sanitized = str_replace("\u{FFFD}", '', $sanitized);
            $sanitized = preg_replace('/<\s*(p|div|li)\b[^>]*>\s*\?\s*<\/\s*\1\s*>/iu', '', (string) $sanitized) ?? (string) $sanitized;
            $plainText = html_entity_decode(strip_tags($sanitized), ENT_QUOTES, 'UTF-8');
            $plainText = trim(str_replace("\u{00A0}", ' ', $plainText));
            return $plainText !== '' ? $sanitized : $fallback;
        };

        $judulAuditRaw = trim((string) ($lhp->judul ?? ''));
        $judulAuditClean = preg_replace('/\bpada\s+bidang\b/iu', '', $judulAuditRaw);
        $judulAuditClean = preg_replace('/\s*Tahun\s+\d{4}\s*$/iu', '', (string) $judulAuditClean);
        $judulAuditClean = trim(preg_replace('/\s{2,}/u', ' ', (string) $judulAuditClean));
        if ($judulAuditClean === '') {
            $judulAuditClean = $judulAuditRaw;
        }

        $opdNameRaw = trim((string) ($lhp->opd->nama_opd ?? 'OPD'));
        $opdNameClean = preg_replace('/\s*Tahun\s+\d{4}\s*$/iu', '', $opdNameRaw);
        $opdNameClean = trim((string) $opdNameClean);
        if ($opdNameClean === '') {
            $opdNameClean = $opdNameRaw;
        }
    @endphp

    {{-- ══════════════════════════════════════════════════ --}}
{{-- ══════════════════════════════════════════════════ --}}
<div>
        <table width="100%" style="border: none; margin-bottom: 20px;">
            <tr>
                <td width="60%" style="vertical-align: top; border: none;">
                    <table width="100%" style="border: none; line-height: 1.5;">
                        <tr>
                            <td width="20%" style="vertical-align: top;">Nomor</td>
                            <td width="3%" style="vertical-align: top;">:</td>
                            <td width="77%" style="vertical-align: top;">{{ $lhp->nomor_lhp }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">Sifat</td>
                            <td style="vertical-align: top;">:</td>
                            <td style="vertical-align: top;">
                                {{ $lhp->content->metadata_tambahan['sifat'] ?? 'Biasa' }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">Lampiran</td>
                            <td style="vertical-align: top;">:</td>
                            <td style="vertical-align: top;">
                                {{ $lhp->content->metadata_tambahan['lampiran'] ?? '1 (satu) berkas' }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">Perihal</td>
                            <td style="vertical-align: top;">:</td>
                            <td style="vertical-align: top; text-align: justify;">
                                Laporan Hasil Audit Ketaatan {{ $judulAuditClean }}<br>
                                Pada {{ $opdNameClean }}
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="40%" style="vertical-align: top; border: none; text-align: right;">
                    Buntok,
                    {{ \Carbon\Carbon::parse($lhp->tgl_lhp ?? $lhp->tanggal_lhp)->translatedFormat('d F Y') }}
                </td>
            </tr>
        </table>

        <div style="margin-bottom: 15px;">
            Yth. &nbsp;&nbsp;{{ $lhp->content->metadata_tambahan['tujuan_surat'] ?? 'Bupati Barito Selatan' }}<br><br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Di -Buntok
        </div>

        <p style="text-indent: 45px; text-align: justify; margin-bottom: 15px;">
            Kami telah melakukan Audit Ketaatan {{ $judulAuditClean }}. Audit dilaksanakan sesuai dengan Standar Audit yang ditetapkan oleh Dewan Pengurus Nasional Asosiasi Auditor Intern Pemerintah Indonesia (AAIPI) dan kami yakin bahwa audit tersebut dapat memberikan dasar yang memadai untuk menyimpulkan ketaatan terhadap peraturan perundang-undangan, memberikan saran perbaikan yang diperlukan untuk perbaikan pengelolaan risiko dan proses pengendalian intern serta tata kelola pemerintahan.
        </p>

        <p style="margin-bottom: 10px;">Hasil audit disajikan dengan pokok-pokok bahasan sebagai berikut:</p>

        <table width="100%" style="border: none; margin-bottom: 30px;">
            <tr>
                <td width="25%" style="vertical-align: top;">BAGIAN PERTAMA</td>
                <td width="3%" style="vertical-align: top;">:</td>
                <td width="72%" style="vertical-align: top;">SIMPULAN DAN REKOMENDASI</td>
            </tr>
            <tr>
                <td style="vertical-align: top;">BAGIAN KEDUA</td>
                <td style="vertical-align: top;">:</td>
                <td style="vertical-align: top;">URAIAN HASIL AUDIT</td>
            </tr>
        </table>
    </div>


    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- BAGIAN PERTAMA --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div class="page-break"></div>

    <div>
        <div style="text-align: center; font-weight: bold; margin-bottom: 25px;">
            BAGIAN PERTAMA<br>SIMPULAN DAN REKOMENDASI
        </div>

        <div>
            <b>A. SIMPULAN (Ringkasan Hasil Audit)</b>
            <div class="editor-content" style="text-align: justify; margin-left: 20px; margin-bottom: 15px;">
                {!! $renderRichText($lhp->simpulan_manual ?? null) !!}
            </div>
            
            <b>B. REKOMENDASI</b>
            <div class="editor-content" style="text-align: justify; margin-left: 20px; margin-bottom: 30px;">
                {!! $renderRichText($lhp->rekomendasi_manual ?? null) !!}
            </div>
        </div>

        <p style="text-indent: 0; margin-bottom: 5px;">Demikian Kami sampaikan, untuk dapat melakukan langkah-langkah tindak lanjut yang diperlukan.</p>
        <p style="text-indent: 0; margin-bottom: 30px;">Atas perhatian dan kerjasama yang baik, kami ucapkan terima kasih.</p>

        <table width="100%" style="border: none; page-break-inside: avoid;">
            <tr>
                <td width="50%" style="border: none;"></td>
                <td width="50%" style="border: none; text-align: center; line-height: 1.2;">
                    INSPEKTUR DAERAH<br>
                    KABUPATEN BARITO SELATAN
                    <br><br><br><br><br><br>
                    <u><b>YURISTIANTI YUDHA, S.Hut., M.M., CGCAE</b></u><br>
                    Pembina Tingkat I (IV/b)<br>
                    NIP. 19731220 200801 2 010
                </td>
            </tr>
        </table>
    </div>

    {{-- ══════════════════════════════════════════════════════════ --}}
    {{-- BAGIAN KEDUA --}}
    {{-- ══════════════════════════════════════════════════════════ --}}
    <div class="page-break"></div>

    <div>
        <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">
            BAGIAN KEDUA<br>URAIAN HASIL AUDIT
        </div>
        
        <div style="margin-bottom: 10px; margin-top: 15px; font-weight: bold; text-transform: uppercase;">
            BAB I INFORMASI UMUM
        </div>

        <div style="text-align: justify; margin-bottom: 8px;">
            1. Dasar Audit<br>
            <div class="editor-content" style="margin-top: 2px;">
                {!! $renderRichText($lhp->content->metadata_tambahan['dasar_audit'] ?? null) !!}
            </div>
        </div>

        <div style="text-align: justify; margin-bottom: 8px;">
            2. Tujuan, Metodologi, dan Batasan Tanggung Jawab
            <ol type="a" style="margin-top: 0; padding-left: 20px;">
                <li>Tujuan Audit<br>
                    <div class="editor-content" style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['tujuan_audit'] ?? null) !!}
                    </div>
                </li>
                <li>Metodologi Audit<br>
                    <div class="editor-content" style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['metodologi_audit'] ?? null) !!}
                    </div>
                </li>
                <li>Batasan Tanggung Jawab<br>
                    <div class="editor-content" style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['batasan_tanggung_jawab'] ?? null) !!}
                    </div>
                </li>
            </ol>
        </div>

        <div style="text-align: justify; margin-bottom: 8px;">
            3. Sasaran dan Ruang Lingkup Audit
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 2px; margin-bottom: 5px; border: none;">
                <tr>
                    <td width="20" valign="top" style="border: none;">a.</td>
                    <td valign="top" style="border: none;">
                        Sasaran Audit<br>
                        <div class="editor-content">{!! $renderRichText($lhp->content->metadata_tambahan['sasaran_audit'] ?? null) !!}</div>
                    </td>
                </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px; border: none;">
                <tr>
                    <td width="20" valign="top" style="border: none;">b.</td>
                    <td valign="top" style="border: none;">
                        Ruang lingkup Audit<br>
                        <div class="editor-content">{!! $renderRichText($lhp->content->metadata_tambahan['ruang_lingkup'] ?? null) !!}</div>
                    </td>
                </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 3px; border: none;">
                <tr>
                    <td width="20" valign="top" style="border: none;">c.</td>
                    <td valign="top" style="border: none;">
                        Periode Audit<br>
                        <div class="editor-content">{!! $renderRichText($lhp->content->metadata_tambahan['periode_audit'] ?? null) !!}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="text-align: justify; margin-bottom: 8px;">
            4. Informasi Auditi
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 2px; margin-bottom: 5px; border: none;">
                <tr>
                    <td width="20" valign="top" style="border: none;">a.</td>
                    <td valign="top" style="border: none;">
                        Tujuan Program<br>
                        <div class="editor-content">{!! $renderRichText($lhp->content->metadata_tambahan['info_tujuan_program'] ?? null) !!}</div>
                    </td>
                </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px; border: none;">
                <tr>
                    <td width="20" valign="top" style="border: none;">b.</td>
                    <td valign="top" style="border: none;">
                        Kegiatan Program<br>
                        <div class="editor-content">{!! $renderRichText($lhp->content->metadata_tambahan['info_kegiatan_program'] ?? null) !!}</div>
                    </td>
                </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px; border: none;">
                <tr>
                    <td width="20" valign="top" style="border: none;">c.</td>
                    <td valign="top" style="border: none;">
                        Lokasi Program dan Alokasi Dana<br>
                        <div class="editor-content">{!! $renderRichText($lhp->content->metadata_tambahan['info_lokasi_dana'] ?? null) !!}</div>
                    </td>
                </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 5px; border: none;">
                <tr>
                    <td width="20" valign="top" style="border: none;">d.</td>
                    <td valign="top" style="border: none;">
                        Sumber dana<br>
                        <div class="editor-content">{!! $renderRichText($lhp->content->metadata_tambahan['info_sumber_dana'] ?? null) !!}</div>
                    </td>
                </tr>
            </table>
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 3px; border: none;">
                <tr>
                    <td width="20" valign="top" style="border: none;">e.</td>
                    <td valign="top" style="border: none;">
                        Struktur Organisasi<br>
                        <div class="editor-content">{!! $renderRichText($lhp->content->metadata_tambahan['info_struktur_org'] ?? null) !!}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div style="text-align: justify; margin-bottom: 12px;">
            5. Penilaian atas Sistem Pengendalian Intern<br>
            <div class="editor-content" style="margin-top: 2px;">
                {!! $renderRichText($lhp->content->metadata_tambahan['penilaian_spi'] ?? null) !!}
            </div>
        </div>

        {{-- Page Break untuk memisahkan BAB II ke halaman baru --}}
        <div class="page-break"></div>
        
        <div style="text-align: center; font-weight: bold; margin-top: 15px;">
            BAB II<br>URAIAN HASIL AUDIT
        </div>
        <ol style="margin-top: 10px;">
            <li>Penilaian atas Ketaatan terhadap Ketentuan (area, proses, sistem, fungsi, program/kegiatan)</li>
            <li>Kesesuaian Output dengan Tujuan Program</li>
            <li>Temuan Hasil Audit
                @forelse($lhp->findings as $finding)
                    <div style="text-align: justify; margin-bottom: 6px; padding-left: 18px; margin-top: 4px;">
                        <div>
                            {{ $loop->iteration }}.
                        </div>
                        <div class="editor-content" style="margin-top: 2px;">
                            {!! $renderRichText($finding->uraian_temuan, '................................................................') !!}
                        </div>
                        @if(($finding->kerugian_negara > 0) || ($finding->kerugian_daerah > 0))
                            <div style="margin-top: 4px;">
                                <i>(Nilai Kerugian Negara: Rp{{ number_format($finding->kerugian_negara ?? 0, 2, ',', '.') }} | Kerugian Daerah: Rp{{ number_format($finding->kerugian_daerah ?? 0, 2, ',', '.') }})</i>
                            </div>
                        @endif
                    </div>
                @empty
                    <div style="text-align: justify; margin-bottom: 6px; padding-left: 18px; margin-top: 4px;">
                        1. ................................................................
                    </div>
                @endforelse
            </li>
            <li>Hal-hal Penting Lainnya yang Perlu Diperhatikan</li>
            <li>Tindak Lanjut Temuan Audit Tahun Sebelumnya</li>
        </ol>
        <div style="text-align: center; font-weight: bold; margin-top: 15px;">
            BAB III<br>PENUTUP
        </div>

        <div class="editor-content" style="text-align: justify; margin-bottom: 30px; text-indent: 45px; margin-top: 8px;">
            {!! $renderRichText($lhp->penutup_manual ?? null) !!}
        </div>

        {{-- TABLE TANDA TANGAN (KIRI: INSPEKTUR, KANAN: TIM PEMERIKSA) --}}
        <table width="100%" style="border: none; page-break-inside: avoid; margin-top: 20px;">
            <tr>
                <td width="50%" style="border: none; text-align: center; vertical-align: top; line-height: 1.2;">
                    Mengetahui/Menyetujui :<br><br>
                    INSPEKTUR DAERAH<br>
                    KABUPATEN BARITO SELATAN
                    <br><br><br><br><br><br>
                    <u><b>YURISTIANTI YUDHA, S.Hut., M.M., CGCAE</b></u><br>
                    Pembina Tingkat I (IV/b)<br>
                    NIP. 197312202008012010
                </td>
                <td width="55%" style="border: none; vertical-align: top; padding-left: 12px; line-height: 1.2;">
                    Tim Pemeriksa :<br><br>
                    @php
                        // Mengambil data tim_pemeriksa, fallback ke tembusan jika data lama masih ada
                        $timPemeriksaItems = $lhp->content->metadata_tambahan['tim_pemeriksa'] ?? $lhp->content->metadata_tambahan['tembusan'] ?? [];
                        if (!is_array($timPemeriksaItems) || empty($timPemeriksaItems)) {
                            $timPemeriksaItems = [
                                $lhp->content->metadata_tambahan['tembusan_1'] ?? null,
                                $lhp->content->metadata_tambahan['tembusan_2'] ?? null,
                            ];
                            $timPemeriksaItems = array_values(array_filter($timPemeriksaItems, fn ($item) => is_string($item) && trim($item) !== ''));
                        }
                    @endphp
                    <div class="tim-pemeriksa">
                        @forelse($timPemeriksaItems as $idx => $item)
                            <p style="margin: 0 0 2px 0; line-height: 1.2;"><span class="tim-pemeriksa-name">{{ $idx + 1 }}. {!! strip_tags($item, '<b><i><strong><em><u>') !!}</span></p>
                        @empty
                            <p style="margin: 0; line-height: 1.2;">1. ........................................</p>
                            <p style="margin: 0; line-height: 1.2;">2. ........................................</p>
                            <p style="margin: 0; line-height: 1.2;">3. ........................................</p>
                        @endforelse
                    </div>
                </td>
            </tr>
        </table>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- HALAMAN TERAKHIR: SURAT PENYAMPAIAN & TEMBUSAN --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="page-break"></div>

        <table width="100%" style="border: none; margin-bottom: 20px;">
            <tr>
                <td width="60%" style="vertical-align: top; border: none;">
                    <table width="100%" style="border: none; line-height: 1.5;">
                        <tr>
                            <td width="20%" style="vertical-align: top;">Nomor</td>
                            <td width="3%" style="vertical-align: top;">:</td>
                            <td width="77%" style="vertical-align: top;">{{ $lhp->nomor_lhp }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">Sifat</td>
                            <td style="vertical-align: top;">:</td>
                            <td style="vertical-align: top;">{{ $lhp->content->metadata_tambahan['sifat'] ?? 'Penting' }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">Lampiran</td>
                            <td style="vertical-align: top;">:</td>
                            <td style="vertical-align: top;">{{ $lhp->content->metadata_tambahan['lampiran'] ?? '1 (satu) berkas' }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top;">Perihal</td>
                            <td style="vertical-align: top;">:</td>
                            <td style="vertical-align: top; text-align: justify;">
                                Laporan Hasil Audit Ketaatan {{ $judulAuditClean }}<br>
                                Pada {{ $opdNameClean }}<br>
                                Kabupaten Barito Selatan
                            </td>
                        </tr>
                    </table>
                </td>
                <td width="40%" style="vertical-align: top; border: none; text-align: right;">
                    Buntok, &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ \Carbon\Carbon::parse($lhp->tgl_lhp ?? $lhp->tanggal_lhp)->translatedFormat('F Y') }}
                </td>
            </tr>
        </table>

        <div style="margin-bottom: 20px;">
            Yth. Bupati Barito Selatan<br>
            di -<br>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Buntok
        </div>

        <div style="text-align: justify; text-indent: 45px; margin-bottom: 15px; line-height: 1.5;">
            @php
                $tanggalSptRaw = $lhp->content->metadata_tambahan['tanggal_spt'] ?? null;
                try {
                    $tanggalSptFormatted = !empty($tanggalSptRaw)
                        ? \Carbon\Carbon::parse($tanggalSptRaw)->locale('id')->translatedFormat('d F Y')
                        : '...................';
                } catch (\Throwable $e) {
                    $tanggalSptFormatted = !empty($tanggalSptRaw) ? $tanggalSptRaw : '...................';
                }

                $tanggalLhpFormatted = \Carbon\Carbon::parse($lhp->tgl_lhp ?? $lhp->tanggal_lhp)
                    ->locale('id')
                    ->translatedFormat('d F Y');
            @endphp
            Berdasarkan Surat Perintah Tugas Inspektorat Daerah Kabupaten Barito Selatan Nomor: {{ $lhp->content->metadata_tambahan['nomor_spt'] ?? '...................' }} tanggal {{ $tanggalSptFormatted }}, dalam rangka melaksanakan Audit {{ $lhp->judul }} Tahun Anggaran {{ $lhp->tahun_anggaran }} pada {{ $lhp->opd->nama_opd ?? 'OPD' }} Kabupaten Barito Selatan, dengan ini kami sampaikan bahwa Tim Inspektorat Daerah Kabupaten Barito Selatan telah selesai melaksanakan tugas pemeriksaan pada {{ $lhp->opd->nama_opd ?? 'OPD' }} Kabupaten Barito Selatan, selanjutnya sudah membuat/menyusun Laporan hasil Audit Nomor {{ $lhp->nomor_lhp }} tanggal {{ $tanggalLhpFormatted }} sebagaimana terlampir.
        </div>

        <div style="text-align: justify; text-indent: 45px; margin-bottom: 40px;">
            Demikian Surat ini disampaikan, untuk bahan pembinaan selanjutnya dan atas perhatian Bapak, diucapkan terima kasih.
        </div>

        <table width="100%" style="border: none; page-break-inside: avoid;">
            <tr>
                <td width="50%" style="border: none;"></td>
                <td width="50%" style="border: none; text-align: center; line-height: 1.2;">
                    INSPEKTUR DAERAH<br>
                    KABUPATEN BARITO SELATAN
                    <br><br><br><br><br><br>
                    <u><b>YURISTIANTI YUDHA, S.Hut., M.M., CGCAE</b></u><br>
                    Pembina Tingkat I (IV/b)<br>
                    NIP. 19731220 200801 2 010
                </td>
            </tr>
        </table>

        <div style="margin-top: 40px; font-size: 11pt; line-height: 1.4; page-break-inside: avoid;">
            <b>TEMBUSAN,</b> Disampaikan kepada Yth:<br>
            @php
                $tembusanSurat = $lhp->content->metadata_tambahan['tembusan'] ?? [];
                if (!is_array($tembusanSurat) || empty($tembusanSurat)) {
                    $tembusanSurat = [
                        $lhp->content->metadata_tambahan['tembusan_1'] ?? null,
                        $lhp->content->metadata_tambahan['tembusan_2'] ?? null,
                    ];
                    $tembusanSurat = array_values(array_filter($tembusanSurat, fn ($item) => is_string($item) && trim($item) !== ''));
                }
            @endphp
            @forelse($tembusanSurat as $idx => $item)
                {{ $idx + 1 }}. {!! $item !!}<br>
            @empty
                1. Gubernur Kalimantan Tengah<br>
                &nbsp;&nbsp;&nbsp;Up. Inspektur Provinsi Kalimantan Tengah<br>
                &nbsp;&nbsp;&nbsp;di <b>Palangka Raya</b><br>
                2. Wakil Bupati Kabupaten Barito Selatan<br>
                &nbsp;&nbsp;&nbsp;di <b>Buntok</b><br>
                3. Kepala BPK RI Perwakilan Provinsi Kalimantan Tengah<br>
                &nbsp;&nbsp;&nbsp;di <b>Palangka Raya</b><br>
            @endforelse
        </div>
    </div>

@endsection



