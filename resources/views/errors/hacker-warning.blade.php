<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: #050505;
            color: #ff2a2a;
            font-family: "Courier New", monospace;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .panel {
            width: 100%;
            max-width: 900px;
            border: 2px solid #8b0000;
            background: rgba(20, 0, 0, 0.92);
            box-shadow: 0 0 24px rgba(255, 0, 0, 0.35);
            padding: 28px;
            line-height: 1.6;
        }
        .title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 1px;
            margin: 0 0 16px 0;
            text-transform: uppercase;
        }
        .ip {
            display: inline-block;
            margin: 14px 0;
            font-size: 24px;
            font-weight: 700;
            color: #fff;
            background: #9f0000;
            padding: 8px 14px;
        }
        .desc {
            color: #ff9c9c;
            font-size: 16px;
            margin: 0;
        }
    </style>
</head>
<body>
    <section class="panel">
        <h1 class="title">PERINGATAN! Aktivitas Ilegal Terdeteksi.</h1>
        <p class="desc">
            Alamat IP Anda
            <span class="ip">{{ $ip }}</span>
            telah direkam.
        </p>
        <p class="desc">
            Percobaan peretasan sistem pemerintah diancam pidana Pasal 30 UU ITE dengan hukuman maksimal 8 tahun penjara dan/atau denda Rp 800.000.000,00.
        </p>
    </section>
</body>
</html>
