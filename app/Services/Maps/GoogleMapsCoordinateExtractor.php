<?php

declare(strict_types=1);

namespace JuegosTenerife\Services\Maps;

final class GoogleMapsCoordinateExtractor
{
    /**
     * @return array{lat: float, lng: float, final_url: string}|null
     */
    public function extract(string $url): ?array
    {
        $url = trim($url);

        if ($url === '' || !str_starts_with($url, 'https://')) {
            return null;
        }

        $finalUrl = $this->resolveFinalUrl($url);

        if ($finalUrl === null) {
            return null;
        }

        $coordinates = $this->parseCoordinates($finalUrl);

        if ($coordinates === null) {
            return null;
        }

        return [
            'lat' => $coordinates['lat'],
            'lng' => $coordinates['lng'],
            'final_url' => $finalUrl,
        ];
    }

    private function resolveFinalUrl(string $url): ?string
    {
        $curl = curl_init($url);

        if ($curl === false) {
            return null;
        }

        curl_setopt_array($curl, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => false,
            CURLOPT_NOBODY => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_USERAGENT => 'JuegosTenerifeCenso/1.0',
        ]);

        curl_exec($curl);
        $finalUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
        $statusCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if (!is_string($finalUrl) || $finalUrl === '' || $statusCode >= 400) {
            return null;
        }

        return $finalUrl;
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    private function parseCoordinates(string $url): ?array
    {
        $decoded = urldecode($url);

        $patterns = [
            '/@(-?\d+\.\d+),\s*(-?\d+\.\d+)/',
            '/\/search\/(-?\d+\.\d+),\s*\+?(-?\d+\.\d+)/',
            '/[?&]query=(-?\d+\.\d+),\s*\+?(-?\d+\.\d+)/',
            '/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $decoded, $matches) === 1) {
                return [
                    'lat' => (float) $matches[1],
                    'lng' => (float) $matches[2],
                ];
            }
        }

        return null;
    }
}

