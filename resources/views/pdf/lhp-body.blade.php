@extends('pdf.lhp-master')

@section('content')

    @php
        $renderRichText = function ($value, $fallback = '................................................................') {
            if (!is_string($value) || trim($value) === '') {
                return $fallback;
            }
            $sanitized = \Mews\Purifier\Facades\Purifier::clean($value, 'audit_wysiwyg');
            $plainText = html_entity_decode(strip_tags($sanitized), ENT_QUOTES, 'UTF-8');
            $plainText = trim(str_replace("\u{00A0}", ' ', $plainText));
            return $plainText !== '' ? $sanitized : $fallback;
        };
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
                                Laporan Hasil Audit Ketaatan<br>
                                pada Bidang {{ $lhp->judul }}<br>
                                {{ $lhp->opd->nama_opd ?? 'OPD' }} Tahun {{ $lhp->tahun_anggaran }}
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
            Kami telah melakukan Audit Ketaatan pada Bidang {{ $lhp->judul }}. Audit dilaksanakan sesuai dengan Standar Audit yang ditetapkan oleh Dewan Pengurus Nasional Asosiasi Auditor Intern Pemerintah Indonesia (AAIPI) dan kami yakin bahwa audit tersebut dapat memberikan dasar yang memadai untuk menyimpulkan ketaatan terhadap peraturan perundang-undangan, memberikan saran perbaikan yang diperlukan untuk perbaikan pengelolaan risiko dan proses pengendalian intern serta tata kelola pemerintahan.
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
            <div style="text-align: justify; margin-left: 20px; margin-bottom: 15px;">
                @if(isset($lhp->content->metadata_tambahan['simpulan_audit']))
                    {!! $renderRichText($lhp->content->metadata_tambahan['simpulan_audit']) !!}
                @else
                    Berdasarkan hasil pengujian atas {{ $lhp->judul }} pada {{ $lhp->opd->nama_opd ?? 'OPD' }} Tahun Anggaran {{ $lhp->tahun_anggaran }}, kami menyimpulkan bahwa secara keseluruhan tingkat ketaatan terhadap ketentuan yang ditetapkan dan pencapaian sasaran sistem pengendalian intern masih belum sepenuhnya memadai.
                @endif
            </div>
            
            <b>B. REKOMENDASI</b>
            <div style="text-align: justify; margin-left: 20px; margin-bottom: 30px;">
                Terhadap permasalahan yang ditemukan dalam audit, direkomendasikan kepada Kepala {{ $lhp->opd->nama_opd ?? 'OPD' }} Kabupaten Barito Selatan agar:
                <ol style="margin-top: 5px; padding-left: 20px; text-align: justify;">
                    @forelse($lhp->findings as $finding)
                        @foreach($finding->recommendations as $rec)
                            <li style="margin-bottom: 5px;">{{ $rec->uraian_rekomendasi }}
                                @if($rec->nilai_rekomendasi > 0)
                                senilai Rp{{ number_format($rec->nilai_rekomendasi, 2, ',', '.') }} @endif.</li>
                        @endforeach
                    @empty
                        <li>Tidak terdapat rekomendasi yang perlu ditindaklanjuti (Nihil).</li>
                    @endforelse
                </ol>
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
            <div style="margin-top: 2px;">
                {!! $renderRichText($lhp->content->metadata_tambahan['dasar_audit'] ?? null) !!}
            </div>
        </div>

        <div style="text-align: justify; margin-bottom: 8px;">
            2. Tujuan, Metodologi, dan Batasan Tanggung Jawab
            <ol type="a" style="margin-top: 0; padding-left: 20px;">
                <li>Tujuan Audit<br>
                    <div style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['tujuan_audit'] ?? null) !!}
                    </div>
                </li>
                <li>Metodologi Audit<br>
                    <div style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['metodologi_audit'] ?? null) !!}
                    </div>
                </li>
                <li>Batasan Tanggung Jawab<br>
                    <div style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['batasan_tanggung_jawab'] ?? null) !!}
                    </div>
                </li>
            </ol>
        </div>

        <div style="text-align: justify; margin-bottom: 8px;">
            3. Sasaran dan Ruang Lingkup Audit
            <ol type="a" style="margin-top: 0; padding-left: 20px;">
                <li>Sasaran Audit<br>
                    <div style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['sasaran_audit'] ?? null) !!}
                    </div>
                </li>
                <li>Ruang lingkup Audit<br>
                    <div style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['ruang_lingkup'] ?? null) !!}
                    </div>
                </li>
                <li>Periode Audit<br>
                    <div style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['periode_audit'] ?? null) !!}
                    </div>
                </li>
            </ol>
        </div>

        <div style="text-align: justify; margin-bottom: 8px;">
            4. Informasi Auditi
            <ol type="a" style="margin-top: 0; padding-left: 20px;">
                <li>Tujuan Program<br>
                    <div style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['info_tujuan_program'] ?? null) !!}
                    </div>
                </li>
                <li>Kegiatan Program<br>
                    <div style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['info_kegiatan_program'] ?? null) !!}
                    </div>
                </li>
                <li>Lokasi Program dan Alokasi Dana<br>
                    <div style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['info_lokasi_dana'] ?? null) !!}
                    </div>
                </li>
                <li>Sumber dana<br>
                    <div style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['info_sumber_dana'] ?? null) !!}
                    </div>
                </li>
                <li>Struktur Organisasi<br>
                    <div style="margin-bottom: 3px;">
                        {!! $renderRichText($lhp->content->metadata_tambahan['info_struktur_org'] ?? null) !!}
                    </div>
                </li>
            </ol>
        </div>

        <div style="text-align: justify; margin-bottom: 12px;">
            5. Penilaian atas Sistem Pengendalian Intern<br>
            <div style="margin-top: 2px;">
                {!! $renderRichText($lhp->content->metadata_tambahan['penilaian_spi'] ?? null) !!}
            </div>
        </div>

        {{-- Page Break untuk memisahkan BAB II ke halaman baru --}}
        <div class="page-break"></div>
        
        <div style="margin-bottom: 10px; margin-top: 0; font-weight: bold; text-transform: uppercase;">
            BAB II URAIAN HASIL AUDIT
        </div>

        <div style="text-align: justify; margin-bottom: 8px;">
            1. Penilaian atas Ketaatan terhadap Ketentuan<br>
            <div style="margin-top: 2px;">
                {!! $renderRichText($lhp->content->metadata_tambahan['penilaian_ketaatan'] ?? null) !!}
            </div>
        </div>

        <div style="text-align: justify; margin-bottom: 8px;">
            2. Kesesuaian Output dengan Tujuan Program<br>
            <div style="margin-top: 2px;">
                {!! $renderRichText($lhp->content->metadata_tambahan['kesesuaian_output'] ?? null) !!}
            </div>
        </div>

        <div style="text-align: justify; margin-bottom: 5px;">
            3. Temuan Hasil Audit
        </div>

        @forelse($lhp->findings as $finding)
            <div style="text-align: justify; margin-bottom: 6px; padding-left: 18px;">
                <div>
                    {{ $loop->iteration }}. <b>({{ $finding->kode_temuan }})</b>
                </div>
                <div style="margin-top: 2px;">
                    {!! $renderRichText($finding->uraian_temuan, '................................................................') !!}
                </div>
                @if(($finding->kerugian_negara > 0) || ($finding->kerugian_daerah > 0))
                    <div style="margin-top: 4px;">
                        <i>(Nilai Kerugian Negara: Rp{{ number_format($finding->kerugian_negara ?? 0, 2, ',', '.') }} | Kerugian Daerah: Rp{{ number_format($finding->kerugian_daerah ?? 0, 2, ',', '.') }})</i>
                    </div>
                @endif
            </div>
        @empty
            <div style="text-align: justify; margin-bottom: 6px; padding-left: 18px;">
                1. ................................................................
            </div>
        @endforelse

        <div style="text-align: justify; margin-bottom: 8px;">
            4. Hal-hal Penting Lainnya yang Perlu Diperhatikan<br>
            <div style="margin-top: 2px;">
                {!! $renderRichText($lhp->content->metadata_tambahan['hal_penting_lainnya'] ?? null) !!}
            </div>
        </div>

        <div style="text-align: justify; margin-bottom: 12px;">
            5. Tindak Lanjut Temuan Audit Tahun Sebelumnya<br>
            <div style="margin-top: 2px;">
                {!! $renderRichText($lhp->content->metadata_tambahan['tindak_lanjut_sebelumnya'] ?? null) !!}
            </div>
        </div>

        <div style="margin-bottom: 10px; margin-top: 25px; font-weight: bold; text-transform: uppercase;">
            BAB III PENUTUP
        </div>

        <div style="text-align: justify; margin-bottom: 30px; text-indent: 45px;">
            Demikian Laporan Hasil Audit Ketaatan ini disusun berdasarkan data dan fakta yang diperoleh pada saat Tim Pemeriksa melaksanakan pemeriksaan di lapangan, dengan berpedoman pada Norma Pemeriksaan yang berlaku bagi Aparat Pengawas Internal Pemerintah (APIP) di lingkungan Kementerian Dalam Negeri dan Sumpah Jabatan.
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
                <td width="50%" style="border: none; vertical-align: top; padding-left: 20px;">
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
                    @forelse($timPemeriksaItems as $idx => $item)
                        {{ $idx + 1 }}. {{ $item }}<br><br>
                    @empty
                        1. ........................................<br><br>
                        2. ........................................<br><br>
                        3. ........................................
                    @endforelse
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
                                Laporan Hasil Audit Ketaatan<br>
                                Atas {{ $lhp->judul }}<br>
                                Pada {{ $lhp->opd->nama_opd ?? 'OPD' }}<br>
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
            Berdasarkan Surat Perintah Tugas Inspektorat Daerah Kabupaten Barito Selatan Nomor: {{ $lhp->content->metadata_tambahan['nomor_spt'] ?? '...................' }} tanggal {{ $lhp->content->metadata_tambahan['tanggal_spt'] ?? '...................' }}, dalam rangka melaksanakan Audit {{ $lhp->judul }} Tahun Anggaran {{ $lhp->tahun_anggaran }} pada {{ $lhp->opd->nama_opd ?? 'OPD' }} Kabupaten Barito Selatan, dengan ini kami sampaikan bahwa Tim Inspektorat Daerah Kabupaten Barito Selatan telah selesai melaksanakan tugas pemeriksaan pada {{ $lhp->opd->nama_opd ?? 'OPD' }} Kabupaten Barito Selatan, selanjutnya sudah membuat/menyusun Laporan hasil Audit Nomor {{ $lhp->nomor_lhp }} tanggal {{ \Carbon\Carbon::parse($lhp->tgl_lhp ?? $lhp->tanggal_lhp)->translatedFormat('d F Y') }} sebagaimana terlampir.
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

        <div style="margin-top: 40px; font-size: 11pt; line-height: 1.4;">
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



