<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('lhp_contents')
            ->select('id', 'metadata_tambahan')
            ->orderBy('created_at')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    $metadata = $row->metadata_tambahan;

                    if (is_string($metadata)) {
                        $decoded = json_decode($metadata, true);
                        $metadata = is_array($decoded) ? $decoded : [];
                    } elseif (!is_array($metadata)) {
                        $metadata = [];
                    }

                    $changed = false;

                    $existingTembusan = [];
                    if (!empty($metadata['tembusan']) && is_array($metadata['tembusan'])) {
                        $existingTembusan = $metadata['tembusan'];
                    } else {
                        $existingTembusan = [
                            $metadata['tembusan_1'] ?? null,
                            $metadata['tembusan_2'] ?? null,
                        ];
                    }

                    $tembusanList = collect($existingTembusan)
                        ->map(fn ($item) => is_string($item) ? trim(strip_tags($item)) : null)
                        ->filter(fn ($item) => is_string($item) && $item !== '')
                        ->values()
                        ->all();

                    if (!isset($metadata['tembusan']) || !is_array($metadata['tembusan'])) {
                        $metadata['tembusan'] = $tembusanList;
                        $changed = true;
                    }

                    $normalizedTim = [];
                    if (!empty($metadata['tim_pemeriksa']) && is_array($metadata['tim_pemeriksa'])) {
                        $normalizedTim = collect($metadata['tim_pemeriksa'])
                            ->map(fn ($item) => is_string($item) ? trim(strip_tags($item)) : null)
                            ->filter(fn ($item) => is_string($item) && $item !== '')
                            ->values()
                            ->all();
                    }

                    if (!isset($metadata['tim_pemeriksa']) || !is_array($metadata['tim_pemeriksa'])) {
                        // Untuk data lama, isi awal tim_pemeriksa mengikuti data tembusan yang tersedia.
                        $metadata['tim_pemeriksa'] = $tembusanList;
                        $changed = true;
                    } elseif ($normalizedTim !== $metadata['tim_pemeriksa']) {
                        $metadata['tim_pemeriksa'] = $normalizedTim;
                        $changed = true;
                    }

                    $firstTembusan = $tembusanList[0] ?? null;
                    $secondTembusan = $tembusanList[1] ?? null;
                    if (($metadata['tembusan_1'] ?? null) !== $firstTembusan) {
                        $metadata['tembusan_1'] = $firstTembusan;
                        $changed = true;
                    }
                    if (($metadata['tembusan_2'] ?? null) !== $secondTembusan) {
                        $metadata['tembusan_2'] = $secondTembusan;
                        $changed = true;
                    }

                    if ($changed) {
                        DB::table('lhp_contents')
                            ->where('id', $row->id)
                            ->update([
                                'metadata_tambahan' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
                                'updated_at' => now(),
                            ]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op. Backfill metadata bersifat non-destruktif.
    }
};
