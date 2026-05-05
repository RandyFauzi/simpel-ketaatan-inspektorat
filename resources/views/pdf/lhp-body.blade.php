@extends('pdf.lhp-master')

@section('content')

    @php
        $toAlphaMarker = function (int $number, bool $upper = false): string {
            $number = max(1, $number);
            $marker = '';

            while ($number > 0) {
                $number--;
                $marker = chr(97 + ($number % 26)) . $marker;
                $number = intdiv($number, 26);
            }

            return $upper ? strtoupper($marker) : $marker;
        };

        $toRomanMarker = function (int $number, bool $upper = false): string {
            $number = max(1, min(3999, $number));
            $map = [
                1000 => 'm', 900 => 'cm', 500 => 'd', 400 => 'cd',
                100 => 'c', 90 => 'xc', 50 => 'l', 40 => 'xl',
                10 => 'x', 9 => 'ix', 5 => 'v', 4 => 'iv', 1 => 'i',
            ];
            $result = '';

            foreach ($map as $value => $roman) {
                while ($number >= $value) {
                    $result .= $roman;
                    $number -= $value;
                }
            }

            return $upper ? strtoupper($result) : $result;
        };

        $formatListMarker = function (\DOMElement $list, int $number, int $level) use ($toAlphaMarker, $toRomanMarker): string {
            $tagName = strtolower($list->tagName);

            if ($tagName === 'ul') {
                return $level % 2 === 0 ? '&bull;' : '-';
            }

            $type = $list->getAttribute('type');

            return match ($type) {
                'a' => $toAlphaMarker($number) . '.',
                'A' => $toAlphaMarker($number, true) . '.',
                'i' => $toRomanMarker($number) . '.',
                'I' => $toRomanMarker($number, true) . '.',
                default => match (true) {
                    $level === 1 => $toAlphaMarker($number) . '.',
                    $level >= 2 => $toRomanMarker($number) . '.',
                    default => $number . '.',
                },
            };
        };

        $renderAttributes = function (\DOMElement $node): string {
            $attributes = '';

            foreach ($node->attributes ?? [] as $attribute) {
                if (strtolower($attribute->name) === 'id') {
                    continue;
                }

                $name = htmlspecialchars($attribute->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $value = htmlspecialchars($attribute->value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $attributes .= " {$name}=\"{$value}\"";
            }

            return $attributes;
        };

        $renderNode = null;
        $renderList = null;

        $renderNode = function (\DOMNode $node, int $level = 0) use (&$renderNode, &$renderList, $renderAttributes): string {
            if ($node instanceof \DOMText) {
                return htmlspecialchars($node->nodeValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }

            if (!$node instanceof \DOMElement) {
                return '';
            }

            $tagName = strtolower($node->tagName);

            if (in_array($tagName, ['ol', 'ul'], true)) {
                return $renderList($node, $level);
            }

            if ($tagName === 'br') {
                return '<br>';
            }

            $attributes = $renderAttributes($node);
            $content = '';

            foreach ($node->childNodes as $childNode) {
                $content .= $renderNode($childNode, $level);
            }

            return "<{$tagName}{$attributes}>{$content}</{$tagName}>";
        };

        $renderList = function (\DOMElement $list, int $level = 0) use (&$renderNode, $formatListMarker): string {
            $start = $list->hasAttribute('start') ? max(1, (int) $list->getAttribute('start')) : 1;
            $number = $start;
            $html = '<table class="pdf-list pdf-list-level-' . min($level, 3) . '" width="100%" cellpadding="0" cellspacing="0">';

            foreach ($list->childNodes as $childNode) {
                if (!$childNode instanceof \DOMElement || strtolower($childNode->tagName) !== 'li') {
                    continue;
                }

                $marker = $formatListMarker($list, $number, $level);
                $content = '';

                foreach ($childNode->childNodes as $itemChild) {
                    $content .= $renderNode($itemChild, $level + 1);
                }

                $html .= '<tr>';
                $html .= '<td class="pdf-list-marker" width="22" valign="top" style="width: 22px; padding-right: 4px;">' . $marker . '</td>';
                $html .= '<td class="pdf-list-content" valign="top" style="text-align: justify;">' . $content . '</td>';
                $html .= '</tr>';
                $number++;
            }

            $html .= '</table>';

            return $html;
        };

        $normalizeRichTextLists = function (string $html) use (&$renderNode): string {
            if (!str_contains($html, '<ol') && !str_contains($html, '<ul')) {
                return $html;
            }

            $dom = new \DOMDocument('1.0', 'UTF-8');
            $previous = libxml_use_internal_errors(true);
            $loaded = $dom->loadHTML(
                '<?xml encoding="UTF-8"><div id="pdf-fragment">' . $html . '</div>',
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            if (!$loaded) {
                return $html;
            }

            $root = $dom->getElementById('pdf-fragment') ?: $dom->documentElement;
            $normalized = '';

            foreach ($root->childNodes as $childNode) {
                $normalized .= $renderNode($childNode);
            }

            return $normalized !== '' ? $normalized : $html;
        };

        $renderRichText = function ($value, $fallback = '................................................................') use ($normalizeRichTextLists) {
            if (!is_string($value) || trim($value) === '') {
                return $fallback;
            }
            $sanitized = \Mews\Purifier\Facades\Purifier::clean($value, 'audit_wysiwyg');
            // Bersihkan karakter artefak copy/paste yang kadang dirender DOMPDF menjadi "?" di baris terpisah.
            $sanitized = str_replace("\u{FFFD}", '', $sanitized);
            $sanitized = preg_replace('/<\s*(p|div|li)\b[^>]*>\s*\?\s*<\/\s*\1\s*>/iu', '', (string) $sanitized) ?? (string) $sanitized;
            $plainText = html_entity_decode(strip_tags($sanitized), ENT_QUOTES, 'UTF-8');
            $plainText = trim(str_replace("\u{00A0}", ' ', $plainText));
            return $plainText !== '' ? $normalizeRichTextLists($sanitized) : $fallback;
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

        <table class="signature-table" width="100%" style="border: none; page-break-inside: avoid;">
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

        <table class="report-point" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="report-number" valign="top">1.</td>
                <td valign="top">Dasar Audit</td>
            </tr>
        </table>
        <div class="editor-content content-block point-content">
            {!! $renderRichText($lhp->content->metadata_tambahan['dasar_audit'] ?? null) !!}
        </div>

        <table class="report-point" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="report-number" valign="top">2.</td>
                <td valign="top">Tujuan, Metodologi, dan Batasan Tanggung Jawab</td>
            </tr>
        </table>

        <table class="report-subpoint" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="report-sub-indent"></td>
                <td class="report-sub-number" valign="top">a.</td>
                <td valign="top">Tujuan Audit</td>
            </tr>
        </table>
        <div class="editor-content content-block subpoint-content">
            {!! $renderRichText($lhp->content->metadata_tambahan['tujuan_audit'] ?? null) !!}
        </div>

        <table class="report-subpoint" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="report-sub-indent"></td>
                <td class="report-sub-number" valign="top">b.</td>
                <td valign="top">Metodologi Audit</td>
            </tr>
        </table>
        <div class="editor-content content-block subpoint-content">
            {!! $renderRichText($lhp->content->metadata_tambahan['metodologi_audit'] ?? null) !!}
        </div>

        <table class="report-subpoint" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="report-sub-indent"></td>
                <td class="report-sub-number" valign="top">c.</td>
                <td valign="top">Batasan Tanggung Jawab</td>
            </tr>
        </table>
        <div class="editor-content content-block subpoint-content">
            {!! $renderRichText($lhp->content->metadata_tambahan['batasan_tanggung_jawab'] ?? null) !!}
        </div>

        <div style="text-align: justify; margin-bottom: 8px;">
            <table class="report-point" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="report-number" valign="top">3.</td>
                    <td valign="top">Sasaran dan Ruang Lingkup Audit</td>
                </tr>
            </table>

            <table class="report-subpoint" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="report-sub-indent"></td>
                    <td class="report-sub-number" valign="top">a.</td>
                    <td valign="top">Sasaran Audit</td>
                </tr>
            </table>
            <div class="editor-content content-block subpoint-content">
                {!! $renderRichText($lhp->content->metadata_tambahan['sasaran_audit'] ?? null) !!}
            </div>

            <table class="report-subpoint" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="report-sub-indent"></td>
                    <td class="report-sub-number" valign="top">b.</td>
                    <td valign="top">Ruang Lingkup Audit</td>
                </tr>
            </table>
            <div class="editor-content content-block subpoint-content">
                {!! $renderRichText($lhp->content->metadata_tambahan['ruang_lingkup'] ?? null) !!}
            </div>

            <table class="report-subpoint" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="report-sub-indent"></td>
                    <td class="report-sub-number" valign="top">c.</td>
                    <td valign="top">Periode Audit</td>
                </tr>
            </table>
            <div class="editor-content content-block subpoint-content">
                {!! $renderRichText($lhp->content->metadata_tambahan['periode_audit'] ?? null) !!}
            </div>
        </div>

        <div style="text-align: justify; margin-bottom: 8px;">
            <table class="report-point" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="report-number" valign="top">4.</td>
                    <td valign="top">Informasi Auditi</td>
                </tr>
            </table>

            <table class="report-subpoint" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="report-sub-indent"></td>
                    <td class="report-sub-number" valign="top">a.</td>
                    <td valign="top">Tujuan Program</td>
                </tr>
            </table>
            <div class="editor-content content-block subpoint-content">
                {!! $renderRichText($lhp->content->metadata_tambahan['info_tujuan_program'] ?? null) !!}
            </div>

            <table class="report-subpoint" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="report-sub-indent"></td>
                    <td class="report-sub-number" valign="top">b.</td>
                    <td valign="top">Kegiatan Program</td>
                </tr>
            </table>
            <div class="editor-content content-block subpoint-content">
                {!! $renderRichText($lhp->content->metadata_tambahan['info_kegiatan_program'] ?? null) !!}
            </div>

            <table class="report-subpoint" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="report-sub-indent"></td>
                    <td class="report-sub-number" valign="top">c.</td>
                    <td valign="top">Lokasi Program dan Alokasi Dana</td>
                </tr>
            </table>
            <div class="editor-content content-block subpoint-content">
                {!! $renderRichText($lhp->content->metadata_tambahan['info_lokasi_dana'] ?? null) !!}
            </div>

            <table class="report-subpoint" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="report-sub-indent"></td>
                    <td class="report-sub-number" valign="top">d.</td>
                    <td valign="top">Sumber Dana</td>
                </tr>
            </table>
            <div class="editor-content content-block subpoint-content">
                {!! $renderRichText($lhp->content->metadata_tambahan['info_sumber_dana'] ?? null) !!}
            </div>

            <table class="report-subpoint" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="report-sub-indent"></td>
                    <td class="report-sub-number" valign="top">e.</td>
                    <td valign="top">Struktur Organisasi</td>
                </tr>
            </table>
            <div class="editor-content content-block subpoint-content">
                {!! $renderRichText($lhp->content->metadata_tambahan['info_struktur_org'] ?? null) !!}
            </div>
        </div>

        <table class="report-point" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 12px;">
            <tr>
                <td class="report-number" valign="top">5.</td>
                <td valign="top">Penilaian atas Sistem Pengendalian Intern</td>
            </tr>
        </table>
        <div class="editor-content content-block point-content" style="margin-bottom: 12px;">
            {!! $renderRichText($lhp->content->metadata_tambahan['penilaian_spi'] ?? null) !!}
        </div>

        {{-- Page Break untuk memisahkan BAB II ke halaman baru --}}
        <div class="page-break"></div>
        
        <div style="text-align: center; font-weight: bold; margin-top: 15px;">
            BAB II<br>URAIAN HASIL AUDIT
        </div>

        <table class="report-point" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 10px;">
            <tr>
                <td class="report-number" valign="top">1.</td>
                <td valign="top">Penilaian atas Ketaatan terhadap Ketentuan (area, proses, sistem, fungsi, program/kegiatan)</td>
            </tr>
        </table>
        <div class="editor-content content-block point-content">
            {!! $renderRichText($lhp->penilaian_ketaatan ?? null) !!}
        </div>

        <table class="report-point" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="report-number" valign="top">2.</td>
                <td valign="top">Kesesuaian Output dengan Tujuan Program</td>
            </tr>
        </table>
        <div class="editor-content content-block point-content">
            {!! $renderRichText($lhp->kesesuaian_output ?? null) !!}
        </div>

        <table class="report-point" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="report-number" valign="top">3.</td>
                <td valign="top">Temuan Hasil Audit</td>
            </tr>
        </table>
        @forelse($lhp->findings as $finding)
            <div class="editor-content content-block point-content" style="margin-bottom: 6px;">
                {!! $renderRichText($finding->uraian_temuan, '................................................................') !!}
            </div>
        @empty
            <div class="point-content" style="text-align: justify; margin-bottom: 6px;">
                ................................................................
            </div>
        @endforelse

        <table class="report-point" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="report-number" valign="top">4.</td>
                <td valign="top">Hal-hal Penting Lainnya yang Perlu Diperhatikan</td>
            </tr>
        </table>
        <div class="editor-content content-block point-content">
            {!! $renderRichText($lhp->hal_penting ?? null) !!}
        </div>

        <table class="report-point" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="report-number" valign="top">5.</td>
                <td valign="top">Tindak Lanjut Temuan Audit Tahun Sebelumnya</td>
            </tr>
        </table>
        <div class="editor-content content-block point-content">
            {!! $renderRichText($lhp->tindak_lanjut ?? null) !!}
        </div>
        <div style="text-align: center; font-weight: bold; margin-top: 15px;">
            BAB III<br>PENUTUP
        </div>

        <div class="editor-content" style="text-align: justify; margin-bottom: 30px; text-indent: 45px; margin-top: 8px;">
            {!! $renderRichText($lhp->penutup_manual ?? null) !!}
        </div>

        {{-- TABLE TANDA TANGAN (KIRI: INSPEKTUR, KANAN: TIM PEMERIKSA) --}}
        <table class="signature-table" width="100%" style="border: none; page-break-inside: avoid; margin-top: 20px;">
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

        <table class="signature-table" width="100%" style="border: none; page-break-inside: avoid;">
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



