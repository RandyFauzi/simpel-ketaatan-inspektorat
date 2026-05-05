<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cover LHP</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm 2cm;
        }

        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <div
        style="position: relative; height: 920px; box-sizing: border-box; text-align: center; font-family: 'Times New Roman', Times, serif; padding: 40px;">

        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; border: 1px solid #3b5998; z-index: -2;">
        </div>
        <div
            style="position: absolute; top: 5px; left: 5px; right: 5px; bottom: 5px; border: 5px solid #3b5998; z-index: -2;">
        </div>

        <div
            style="position: absolute; top: -2px; left: -2px; width: 8px; height: 8px; border: 1px solid #3b5998; background-color: white; z-index: -1;">
        </div>
        <div
            style="position: absolute; top: -2px; right: -2px; width: 8px; height: 8px; border: 1px solid #3b5998; background-color: white; z-index: -1;">
        </div>
        <div
            style="position: absolute; bottom: -2px; left: -2px; width: 8px; height: 8px; border: 1px solid #3b5998; background-color: white; z-index: -1;">
        </div>
        <div
            style="position: absolute; bottom: -2px; right: -2px; width: 8px; height: 8px; border: 1px solid #3b5998; background-color: white; z-index: -1;">
        </div>

        <div style="position: relative; z-index: 10;">
            {!! $kopSurat !!}

            @php
                $opdName = trim((string) ($lhp->opd->nama_opd ?? 'OPD'));
                $judulRaw = trim((string) ($lhp->judul ?? ''));
                $judulNormalized = preg_replace('/\s+/u', ' ', $judulRaw);
                $opdPattern = '/' . preg_quote($opdName, '/') . '/iu';

                $judulUntukCover = preg_replace('/\s+PADA\s+' . preg_quote($opdName, '/') . '\s*$/iu', '', $judulNormalized);
                if ($judulUntukCover === null || trim($judulUntukCover) === '') {
                    $judulUntukCover = $judulNormalized;
                }

                if (preg_match($opdPattern, $judulUntukCover) === 1 && mb_strtoupper($judulUntukCover) === mb_strtoupper($opdName)) {
                    $judulUntukCover = '';
                }
            @endphp

            <h2
                style="font-size: 16pt; font-weight: bold; margin-top: 62px; margin-bottom: 6px; line-height: 1.35; text-transform: uppercase;">
                LAPORAN HASIL AUDIT KETAATAN<br>
                @if(trim($judulUntukCover) !== '')
                    ATAS {{ $judulUntukCover }}<br>
                @endif
                PADA {{ $opdName }}<br>
                KABUPATEN BARITO SELATAN
            </h2>

            <div style="margin-top: 50px; margin-bottom: 40px; text-align: center;">
                <div
                    style="display: inline-block; width: 2px; height: 150px; background-color: black; margin: 0 15px; vertical-align: middle;">
                </div>
                <div
                    style="display: inline-block; width: 2px; height: 190px; background-color: black; margin: 0 15px; vertical-align: middle;">
                </div>
                <div
                    style="display: inline-block; width: 2px; height: 150px; background-color: black; margin: 0 15px; vertical-align: middle;">
                </div>
            </div>

            <div style="position: absolute; top: 730px; left: 0; right: 0; width: 100%; text-align: center;">
                <table
                    style="margin: 0 auto; margin-bottom: 40px; font-size: 12pt; border: none; text-align: left; width: 350px;">
                    <tr>
                        <td style="width: 80px; vertical-align: top;">Nomor</td>
                        <td style="width: 15px; vertical-align: top;">:</td>
                        <td style="vertical-align: top;">{{ $lhp->nomor_lhp }}</td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Tanggal</td>
                        <td style="vertical-align: top;">:</td>
                        <td style="vertical-align: top;">
                            {{ \Carbon\Carbon::parse($lhp->tgl_lhp ?? $lhp->tanggal_lhp)->translatedFormat('d F Y') }}
                        </td>
                    </tr>
                </table>

                <div style="font-weight: bold; font-size: 14pt;">
                    INSPEKTORAT DAERAH<br>
                    KABUPATEN BARITO SELATAN
                </div>
            </div>
        </div>
    </div>
</body>

</html>
