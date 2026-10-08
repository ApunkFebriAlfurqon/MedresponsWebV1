<?php
require_once '../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

if (!isUserLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please sign in again.'], 401);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload) || !isset($payload['lat'], $payload['lng']) || !is_numeric($payload['lat']) || !is_numeric($payload['lng'])) {
    jsonResponse(['success' => false, 'message' => 'Valid coordinates are required.'], 400);
}

$lat = (float)$payload['lat'];
$lng = (float)$payload['lng'];
if (!is_finite($lat) || !is_finite($lng) || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    jsonResponse(['success' => false, 'message' => 'Coordinates are outside the valid range.'], 400);
}

// Nominatim's public service requires no more than one request per second per application.
$lockPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rapidaid-nominatim-' . hash('sha256', APP_URL) . '.lock';
$rateLock = @fopen($lockPath, 'c+');
if (!$rateLock || !flock($rateLock, LOCK_EX)) {
    if (is_resource($rateLock)) fclose($rateLock);
    jsonResponse(['success' => false, 'message' => 'Address lookup is temporarily unavailable.'], 503);
}
rewind($rateLock);
$lastRequestAt = (float)stream_get_contents($rateLock);
$waitSeconds = 1 - (microtime(true) - $lastRequestAt);
if ($lastRequestAt > 0 && $waitSeconds > 0) {
    usleep((int)ceil($waitSeconds * 1000000));
}
rewind($rateLock);
ftruncate($rateLock, 0);
fwrite($rateLock, sprintf('%.6f', microtime(true)));
fflush($rateLock);
flock($rateLock, LOCK_UN);
fclose($rateLock);

$url = 'https://nominatim.openstreetmap.org/reverse?' . http_build_query([
    'format' => 'jsonv2',
    'lat' => $lat,
    'lon' => $lng,
    'zoom' => 18,
    'addressdetails' => 1,
]);
$userAgent = APP_NAME . '/' . APP_VERSION . ' (' . APP_URL . ')';
$body = false;
$statusCode = 0;

if (function_exists('curl_init')) {
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Referer: ' . APP_URL . '/'],
    ]);
    $body = curl_exec($curl);
    $statusCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
} else {
    $context = stream_context_create(['http' => [
        'method' => 'GET',
        'timeout' => 10,
        'ignore_errors' => true,
        'header' => "Accept: application/json\r\nUser-Agent: $userAgent\r\nReferer: " . APP_URL . "/\r\n",
    ]]);
    $body = @file_get_contents($url, false, $context);
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d{3})/', $header, $matches)) {
            $statusCode = (int)$matches[1];
            break;
        }
    }
}

if ($body === false || $statusCode !== 200) {
    jsonResponse(['success' => false, 'message' => 'The address service could not be reached.'], 502);
}

$result = json_decode($body, true);
if (!is_array($result) || empty($result['display_name'])) {
    jsonResponse(['success' => false, 'message' => 'No readable address was found for these coordinates.'], 404);
}

$addressParts = $result['address'] ?? [];
jsonResponse([
    'success' => true,
    'address' => $result['display_name'],
    'details' => [
        'area' => $addressParts['neighbourhood'] ?? $addressParts['suburb'] ?? $addressParts['village'] ?? null,
        'city' => $addressParts['city'] ?? $addressParts['town'] ?? $addressParts['municipality'] ?? null,
        'region' => $addressParts['state'] ?? null,
        'postcode' => $addressParts['postcode'] ?? null,
        'country' => $addressParts['country'] ?? null,
        'latitude' => $result['lat'] ?? $lat,
        'longitude' => $result['lon'] ?? $lng,
    ],
]);
