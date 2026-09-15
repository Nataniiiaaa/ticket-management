<?php
require_once __DIR__ . '/../config.php';

/**
 * @param array $params contoh: ['status' => 'active'] atau ['search' => 'budi']
 * @return array{ok: bool, data: mixed, error: ?string, http_code: int}
 */
function fetchCustomers(array $params = []): array
{
    $url = API_BASE_URL . '/customers';
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    return curlGetCustomerApi($url);
}

function fetchCustomerById(string $id): array
{
    $url = API_BASE_URL . '/customers/' . urlencode($id);
    return curlGetCustomerApi($url);
}

function curlGetCustomerApi(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . API_TOKEN,
            'Accept: application/json',
        ],
        CURLOPT_TIMEOUT => 8, // timeout 8 detik
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // Kasus 1: koneksi gagal total (API down / DNS error / timeout)
    if ($response === false) {
        return [
            'ok' => false, 'data' => null,
            'error' => 'Tidak dapat menghubungi Customer API: ' . $curlError,
            'http_code' => 0,
        ];
    }

    $decoded = json_decode($response, true);

    // Kasus 2: HTTP status bukan 200
    if ($httpCode !== 200) {
        $msg = match ($httpCode) {
            401 => 'Token tidak valid atau kadaluarsa (401 Unauthorized).',
            404 => 'Data tidak ditemukan (404 Not Found).',
            422 => 'Parameter tidak valid (422 Unprocessable Entity).',
            500 => 'Customer API sedang bermasalah di server mereka (500).',
            default => "Customer API mengembalikan status $httpCode.",
        };
        return ['ok' => false, 'data' => null, 'error' => $msg, 'http_code' => $httpCode];
    }

    // Kasus 3: sukses
    return ['ok' => true, 'data' => $decoded, 'error' => null, 'http_code' => 200];
}
