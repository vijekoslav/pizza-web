<?php
require_once __DIR__ . '/../konfigBP.php';

header('Content-Type: application/json');

$q = trim($_GET['address'] ?? '');
if ($q === '') {
  echo json_encode(['error' => 'Nema adrese']);
  exit;
}

$base = 'https://nominatim.openstreetmap.org/search';
$params = http_build_query([
  'q' => $q,                // kompletna adresa kao string (ulica, grad)
  'format' => 'json',
  'addressdetails' => 1,
  'limit' => 1,
]);

$url = "$base?$params";

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_USERAGENT => 'PizzaWeb-TVZ/1.0 (vboras@tvz.hr)',
  CURLOPT_TIMEOUT => 5,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($http_code !== 200 || !$response) {
  echo json_encode(['error' => 'Greška prilikom dohvaćanja adrese']);
  exit;
}

$data = json_decode($response, true);
if (!$data) {
  echo json_encode(['error' => 'Neispravan odgovor API-ja']);
  exit;
}

$addr = $data[0]['address'] ?? [];
$city = $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? '';
$postcode = $addr['postcode'] ?? '';

echo json_encode([
  'result' => [
    'city' => $city,
    'postcode' => $postcode,
  ]
]);
