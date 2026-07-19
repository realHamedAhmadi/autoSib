<?php

namespace App\Services\Sib;

use App\Exceptions\SibApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class SibHttpClient
{
    public function request(?string $accessToken = null): PendingRequest
    {
        $baseUrl = config('sib.base_url');
        $host = parse_url($baseUrl, PHP_URL_HOST);

        $headers = [
            'Accept-Language' => 'fa-IR',
            'Origin' => $baseUrl,
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:150.0) Gecko/20100101 Firefox/150.0',
            'Connection' => 'keep-alive',
        ];

        if ($accessToken !== null) {
            $headers['Referer'] = "https://{$host}/sibnew/checkTkn?uri=%22%2F%22&tkn={$accessToken}";
            // Explicitly pass Cookie in headers to guarantee its inclusion.
            $headers['Cookie'] = "X-Access-Token={$accessToken}";
        } else {
            $headers['Referer'] = "https://{$host}/";
        }

        $request = Http::baseUrl($baseUrl)
            ->acceptJson()
            ->asJson()
            ->withHeaders($headers)
            ->connectTimeout(config('sib.connect_timeout'))
            ->timeout(config('sib.timeout'))
            ->retry(2, 500, throw: false);

        if ($accessToken !== null) {
            $request->withCookies(
                ['X-Access-Token' => $accessToken],
                $host,
            );
        }

        return $request;
    }

    public function data(Response $response): array
    {
        $payload = $this->payload($response);

        if (! $response->successful()) {
            Log::info($response->status());
            throw new SibApiException(
                message: $payload['Message']
                ?? "SIB returned HTTP {$response->status()}.",
                sibCode: isset($payload['Code']) ? (int) $payload['Code'] : null,
                traceId: $payload['TraceID'] ?? null,
                responseData: $payload,
                httpStatus: $response->status(),
            );
        }

        if (($payload['Code'] ?? null) !== 1) {
            throw new SibApiException(
                message: $payload['Message']
                ?? 'SIB rejected the request.',
                sibCode: isset($payload['Code']) ? (int) $payload['Code'] : null,
                traceId: $payload['TraceID'] ?? null,
                responseData: $payload,
                httpStatus: $response->status(),
            );
        }

        $data = $payload['Data'] ?? null;

        if (! is_array($data)) {
            throw new SibApiException(
                message: 'SIB returned an invalid response payload.',
                sibCode: isset($payload['Code']) ? (int) $payload['Code'] : null,
                traceId: $payload['TraceID'] ?? null,
                responseData: $payload,
                httpStatus: $response->status(),
            );
        }

        return $data;
    }

    private function payload(Response $response): array
    {
        $payload = $response->json();

        return is_array($payload) ? $payload : [];
    }
}
