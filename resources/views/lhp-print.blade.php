<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LHP - {{ $lhp->nomor_lhp }}</title>
    <style>
        @page {
            size: A4;
            margin: 2.5cm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 16pt;
            margin: 0;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0;
            font-size: 11pt;
        }
        .title-block {
            text-align: center;
            margin-bottom: 40px;
        }
        .title-block h2 {
            font-size: 14pt;
            text-decoration: underline;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .section-title {
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
            font-size: 12pt;
            text-transform: uppercase;
        }
        .content-box {
            margin-bottom: 20px;
            text-align: justify;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 10pt;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }
        .table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .footer-sig {
            margin-top: 50px;
            width: 100%;
        }
        .sig-box {
            float: right;
            width: 300px;
            text-align: center;
        }
        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2563eb;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-family: sans-serif;
            font-weight: bold;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <a href="javascript:window.print()" class="no-print">Klik untuk Cetak / Save PDF</a>

    <div class="container">
        <!-- Kop Surat -->
        <div class="header">
            <h1>PEMERINTAH KABUPATEN TAMBUNAN</h1>
            <h1>INSPEKTORAT DAERAH</h1>
            <p>Jalan Protokol No. 123, Tambunan. Telp (021) 1234567</p>
        </div>

        <!-- Judul LHP -->
        <div class="title-block">
            <h2>LAPORAN HASIL PEMERIKSAAN (LHP)</h2>
            <p>NOMOR: {{ $lhp->nomor_lhp }}</p>
            <p>TANGGAL: {{ \Carbon\Carbon::parse($lhp->tgl_lhp)->translatedFormat('d F Y') }}</p>
            <p style="margin-top: 15px; font-weight: bold; text-transform: uppercase;">
                {{ $lhp->judul }}<br>
                PADA {{ $lhp->opd->nama_opd }}<br>
                TAHUN ANGGARAN {{ $lhp->tahun_anggaran }}
            </p>
        </div>

        <!-- BAB I -->
        <div class="section-title">BAB I - INFORMASI UMUM</div>
        <div class="content-box">
            <strong>1. Dasar Audit:</strong><br>
            {!! nl2br(e($lhp->content->metadata_tambahan['dasar_audit'] ?? '-')) !!}
        </div>
        <div class="content-box">
            <strong>2. Tujuan Audit:</strong><br>
            {!! nl2br(e($lhp->content->metadata_tambahan['tujuan_audit'] ?? '-')) !!}
        </div>
        <div class="content-box">
            <strong>3. Metodologi & Batasan:</strong><br>
            {!! nl2br(e(($lhp->content->metadata_tambahan['metodologi_audit'] ?? $lhp->content->metadata_tambahan['metodologi'] ?? '-'))) !!}
        </div>
        <div class="content-box">
            <strong>4. Sasaran & Ruang Lingkup:</strong><br>
            {!! nl2br(e(($lhp->content->metadata_tambahan['sasaran_audit'] ?? $lhp->content->metadata_tambahan['sasaran'] ?? '-'))) !!}
        </div>

        <!-- BAB II -->
        <div class="section-title">BAB II - HASIL PEMERIKSAAN (TEMUAN)</div>
        <table class="table">
            <thead>
                <tr>
                    <th width="30">NO</th>
                    <th width="80">KODE</th>
                    <th>URAIAN TEMUAN</th>
                    <th width="120">NILAI TEMUAN (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($lhp->findings as $idx => $finding)
                @php $val = $finding->kerugian_negara + $finding->kerugian_daerah; $total += $val; @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $finding->kode_temuan }}</td>
                    <td>{{ $finding->uraian_temuan }}</td>
                    <td class="text-right">{{ number_format($val, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="font-bold text-right">TOTAL NILAI TEMUAN</td>
                    <td class="font-bold text-right">{{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- BAB III -->
        <div class="section-title">BAB III - REKOMENDASI TINDAK LANJUT</div>
        <table class="table">
            <thead>
                <tr>
                    <th width="80">KODE REC</th>
                    <th>URAIAN REKOMENDASI</th>
                    <th width="120">NILAI REC (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lhp->findings as $finding)
                    @foreach($finding->recommendations as $rec)
                    <tr>
                        <td class="text-center">{{ $rec->kode_rekomendasi }}</td>
                        <td>{{ $rec->uraian_rekomendasi }}</td>
                        <td class="text-right">{{ number_format($rec->nilai_rekomendasi ?? ($finding->kerugian_negara + $finding->kerugian_daerah), 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <!-- Penutup -->
        <div class="section-title">PENUTUP</div>
        <div class="content-box">
            {!! nl2br(e($lhp->content->bab_3_penutup ?? 'Demikian laporan hasil pemeriksaan ini kami sampaikan untuk dipergunakan sebagaimana mestinya.')) !!}
        </div>

        <!-- Tanda Tangan -->
        <div class="footer-sig">
            <div class="sig-box">
                <p>Tambunan, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p>Inspektur Kabupaten,</p>
                <br><br><br>
                <p><strong>[NAMA INSPEKTUR]</strong></p>
                <p>NIP. XXXXXXXXXXXXXX</p>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</body>
</html>
