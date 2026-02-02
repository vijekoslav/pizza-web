<?php
require_once __DIR__ . '/../konfigBP.php';

function respond_json($data, $status = 200)
{
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

$q = trim($_GET['q'] ?? 'pizza');
if ($q === '') $q = 'pizza';

// TheMealDB search by name
$url = 'https://www.themealdb.com/api/json/v1/1/search.php?s=' . rawurlencode($q);

$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 10,
  CURLOPT_USERAGENT => "PizzaWeb/1.0 (demo)"
]);
$raw = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($raw === false || $http >= 400) {
  respond_json(['error' => 'Vanjski API nije dostupan.'], 502);
}

$data = json_decode($raw, true);
$meals = $data['meals'] ?? [];

$out = [];
foreach ($meals as $m) {
  $out[] = [
    'id' => $m['idMeal'] ?? null,
    'name' => $m['strMeal'] ?? '',
    'thumb' => $m['strMealThumb'] ?? '',
    'category' => $m['strCategory'] ?? '',
    'area' => $m['strArea'] ?? '',
    'source' => $m['strSource'] ?? '',
    'youtube' => $m['strYoutube'] ?? '',
  ];
}

respond_json(['query' => $q, 'recipes' => $out], 200);
