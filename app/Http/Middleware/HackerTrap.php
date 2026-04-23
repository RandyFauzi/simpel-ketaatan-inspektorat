<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HackerTrap
{
    /**
     * Signature payload yang umum digunakan pada serangan injeksi.
     *
     * @var array<int, string>
     */
    private array $blacklistPayloads = [
        '<script>',
        'union select',
        'concat(',
        'base64_',
        'eval(',
        '../',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $haystacks = array_merge([$request->fullUrl()], $this->flattenStrings($request->all()));

        foreach ($haystacks as $content) {
            $candidate = mb_strtolower($content);
            $decodedCandidate = mb_strtolower(rawurldecode($content));
            foreach ($this->blacklistPayloads as $payload) {
                if (str_contains($candidate, $payload) || str_contains($decodedCandidate, $payload)) {
                    Log::warning('HackerTrap triggered', [
                        'ip' => $request->ip(),
                        'url' => $request->fullUrl(),
                        'payload' => $payload,
                    ]);

                    return response()->view('errors.hacker-warning', [
                        'ip' => $request->ip(),
                    ], 403);
                }
            }
        }

        return $next($request);
    }

    /**
     * Flatten array input menjadi daftar string untuk inspeksi payload.
     *
     * @param array<mixed> $inputs
     * @return array<int, string>
     */
    private function flattenStrings(array $inputs): array
    {
        $results = [];

        foreach ($inputs as $value) {
            if (is_array($value)) {
                $results = array_merge($results, $this->flattenStrings($value));
                continue;
            }

            if (is_scalar($value)) {
                $results[] = (string) $value;
            }
        }

        return $results;
    }
}
