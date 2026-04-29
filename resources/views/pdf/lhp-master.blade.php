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
            line-height: 1.5;
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
        .tim-pemeriksa li {
            margin-bottom: 4px;
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
