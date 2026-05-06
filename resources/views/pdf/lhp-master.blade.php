<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $lhp->nomor_lhp }} - LHP</title>
    <style>
        /* 1. KERTAS & MARGIN MUTLAK (Sesuai SK) */
        @page {
            size: A4 portrait;
            /* Atas: 6.5cm (Area Kop + Jarak 2 spasi), Kanan: 2cm, Bawah: 2.5cm, Kiri: 3cm */
            margin: 6.5cm 2cm 2.5cm 3cm;
        }

        /* 2. FONT MUTLAK (Sesuai SK) */
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            color: #000;
            line-height: 1.35;
        }

        main {
            text-align: justify;
        }

        /* Spasi dasar surat/laporan */
        main p,
        main ol,
        main ul,
        main li {
            line-height: 1.35;
            margin-top: 0;
            margin-bottom: 4px;
        }

        .section-title {
            text-align: center;
            font-weight: bold;
            line-height: 1.25;
            margin-bottom: 14px;
        }

        .chapter-title {
            text-align: center;
            font-weight: bold;
            line-height: 1.25;
            margin-top: 14px;
            margin-bottom: 10px;
        }

        .editor-content {
            text-align: justify;
            line-height: 1.35;
        }

        .editor-content p,
        .editor-content div {
            margin-top: 0;
            margin-bottom: 4px;
            line-height: 1.35;
        }

        .content-block {
            text-align: justify;
            line-height: 1.35;
        }

        .content-block > p,
        .content-block > div {
            margin-top: 0;
            margin-bottom: 4px;
        }

        .point-content {
            margin-left: 24px;
            margin-bottom: 5px;
        }

        .subpoint-content {
            margin-left: 46px;
            margin-bottom: 4px;
        }

        .layout-table,
        .report-point,
        .report-subpoint {
            border-collapse: collapse;
            width: 100%;
            border: none;
        }

        .layout-table td,
        .report-point td,
        .report-subpoint td {
            border: none !important;
            padding: 0;
            vertical-align: top;
            line-height: 1.35;
        }

        .report-point {
            margin-bottom: 5px;
            page-break-after: avoid;
        }

        .report-subpoint {
            margin-bottom: 4px;
            page-break-after: avoid;
        }

        .report-number {
            width: 24px;
            white-space: nowrap;
        }

        .report-sub-indent {
            width: 24px;
        }

        .report-sub-number {
            width: 22px;
            white-space: nowrap;
        }

        /* 3. HEADER (KOP SURAT) GLOBAL */
        header {
            position: fixed;
            /* Kita tarik kop surat ke atas masuk ke dalam area margin 6.5cm */
            top: -5.1cm;
            left: 0;
            right: 0;
            height: 3.5cm;
        }

        .watermark {
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(200, 0, 0, 0.1);
            z-index: -1000;
            font-weight: bold;
        }

        .page-break {
            page-break-before: always;
        }

        /* 4. MERAPIKAN SPASI TIM PEMERIKSA */
        .tim-pemeriksa {
            line-height: 1.2;
        }
        .tim-pemeriksa p {
            margin: 0; 
            padding: 0;
        }
        .tim-pemeriksa .tim-pemeriksa-name {
            white-space: nowrap;
        }
        .tim-pemeriksa li {
            margin-bottom: 4px;
        }

        /* Fallback untuk list native; list SunEditor akan dinormalisasi menjadi table-list. */
        main ol,
        main ul,
        .editor-content ol,
        .editor-content ul {
            margin-top: 2px !important;
            margin-bottom: 2px !important;
            margin-left: 20px !important;
            padding-left: 0 !important;
        }

        main li,
        .editor-content li {
            margin-left: 0 !important;
            padding-left: 2px !important;
            margin-bottom: 2px !important;
            text-align: justify;
        }

        /* Nested lists (a, b, c or i, ii, iii) */
        main ol li ol,
        main ul li ul,
        main ol li ul,
        main ul li ol,
        .editor-content ol li ol,
        .editor-content ul li ul,
        .editor-content ol li ul,
        .editor-content ul li ol {
            margin-left: 16px !important;
            padding-left: 0 !important;
            margin-top: 2px !important;
            margin-bottom: 2px !important;
        }

        /* Neutralize SunEditor's injected paragraphs inside lists */
        main li p,
        .editor-content li p {
            margin: 0 !important;
            padding: 0 !important;
            display: inline !important;
        }

        .editor-content p {
            margin-top: 0 !important;
            margin-bottom: 3px !important;
        }

        /* Ensure base ordered list uses numbers and has padding */
        main ol {
            list-style-type: decimal;
            margin-top: 2px;
            margin-bottom: 2px;
        }
        /* Level 2: force to lower-alpha (a, b, c) and indent */
        main ol > li > ol {
            list-style-type: lower-alpha;
        }
        /* Level 3: force to lower-roman (i, ii, iii) and indent */
        main ol > li > ol > li > ol {
            list-style-type: lower-roman;
        }

        /* Honor explicit list type/style coming from SunEditor content */
        main ol[type="a"], main ol[style*="lower-alpha"], main ul[style*="lower-alpha"] { list-style-type: lower-alpha !important; }
        main ol[type="A"], main ol[style*="upper-alpha"], main ul[style*="upper-alpha"] { list-style-type: upper-alpha !important; }
        main ol[type="i"], main ol[style*="lower-roman"], main ul[style*="lower-roman"] { list-style-type: lower-roman !important; }
        main ol[type="I"], main ol[style*="upper-roman"], main ul[style*="upper-roman"] { list-style-type: upper-roman !important; }

        /* Allow long layout tables to split normally in DOMPDF */
        table {
            page-break-inside: auto !important;
        }
        tr {
            page-break-inside: auto !important;
            page-break-after: auto !important;
        }
        td,
        th {
            page-break-inside: auto !important;
        }
        thead {
            display: table-header-group !important;
        }
        tfoot {
            display: table-footer-group !important;
        }

        .signature-table,
        .signature-table tr,
        .signature-table td {
            page-break-inside: avoid !important;
        }

        /* SunEditor Content Tables */
        .editor-content table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 8px;
            margin-bottom: 8px;
        }
        .editor-content table,
        .editor-content th,
        .editor-content td {
            border: 1px solid black;
            padding: 4px 6px;
            vertical-align: top;
        }

        /* Borderless table-list generated from SunEditor ol/ul for stable DOMPDF hanging indent */
        .editor-content table.pdf-list,
        .editor-content table.pdf-list tr,
        .editor-content table.pdf-list td {
            border: none !important;
            padding: 0 !important;
        }

        .editor-content table.pdf-list {
            border-collapse: collapse;
            width: 100%;
            margin: 1px 0 3px 0;
        }

        .editor-content table.pdf-list td.pdf-list-marker {
            width: 22px;
            padding-right: 4px !important;
            white-space: nowrap;
            vertical-align: top;
            text-align: left;
        }

        .editor-content table.pdf-list td.pdf-list-content {
            vertical-align: top;
            text-align: justify;
        }
    </style>
</head>

<body>

    @if($lhp->status === 'draft')
        <div class="watermark">DRAFT</div>
    @endif

    <header>
        <table width="100%" style="border-collapse: collapse; margin-top: 0;">
            <tr>
                <td width="15%" style="text-align: center; vertical-align: middle; padding: 0; border: none;">
                    @php
                        $logoPath = public_path('logo.png');
                        if (!file_exists($logoPath)) {
                            $logoPath = base_path('../public_html/logo.png');
                        }

                        $base64 = null;
                        if (file_exists($logoPath)) {
                            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                            $data = file_get_contents($logoPath);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        }
                    @endphp
                    @if($base64)
                        <img src="{{ $base64 }}" style="width: 80px; height: auto;">
                    @else
                        <div style="width: 80px; height: 100px; border: 1px solid #ccc; text-align: center; line-height: 100px; font-size: 10px;">LOGO</div>
                    @endif
                </td>
                <td width="85%" style="text-align: center; line-height: 1.1; padding: 0; border: none;">
                    <div style="text-transform: uppercase; font-weight: bold; font-size: 16pt; font-family: 'Times New Roman', serif;">PEMERINTAH KABUPATEN BARITO SELATAN</div>
                    <div style="text-transform: uppercase; font-weight: bold; font-style: italic; font-size: 22pt; letter-spacing: 0.5px;">INSPEKTORAT DAERAH</div>
                    <div style="font-size: 10pt; font-family: 'Times New Roman', serif;">Jln. Pelita Raya No. 60 Buntok Kode Pos 73711 Kalimantan Tengah</div>
                    <div style="font-size: 10pt; font-family: 'Times New Roman', serif;">Telp. (0525) 21262 Fax (0525) 22357</div>
                    <div style="font-size: 9pt; font-family: 'Times New Roman', serif;">Email : inspektorat@baritoselatan.co.id / inspektoratdaerah.barsel@gmail.com</div>
                    <div style="font-size: 9pt; font-family: 'Times New Roman', serif;">Website : inspektorat.baritoselatankab.go.id</div>
                </td>
            </tr>
        </table>
        <div style="border-top: 3px solid black; border-bottom: 1px solid black; height: 2px; margin-top: 8px;"></div>
    </header>

    <main>
        @yield('content')
    </main>

    {{-- SCRIPT NOMOR HALAMAN (Mulai dari Bagian Pertama) --}}
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('
                // Atur offset: Halaman fisik 2 (Bagian Pertama) akan dicetak sebagai angka 1
                $pageNumberOffset = -1; 
                $showPageNumberFrom = 2; // Mulai memunculkan angka di halaman fisik ke-2
                $hideLastPages = 2; // Sembunyikan nomor pada 2 halaman paling akhir (Surat Penyampaian/Tembusan)

                $displayPage = $PAGE_NUM + $pageNumberOffset;

                // Tampilkan nomor halaman HANYA JIKA halaman saat ini >= halaman mulai
                // DAN bukan merupakan halaman-halaman akhir (Surat Penyampaian/Tembusan)
                if ($PAGE_NUM >= $showPageNumberFrom && $PAGE_NUM <= ($PAGE_COUNT - $hideLastPages)) {
                    $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
                    $size = 12;
                    $text = $displayPage; 
                    $width = $fontMetrics->get_text_width($text, $font, $size);
                    
                    // Posisi: Tengah (Simetris) & Atas tepat di bawah KOP
                    $x = ($pdf->get_width() - $width) / 2;
                    $y = 150; 
                    
                    $pdf->text($x, $y, $text, $font, $size);
                }
            ');
        }
    </script>
</body>

</html>
