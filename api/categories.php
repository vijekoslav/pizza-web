<?php
// popis kategorija pizza u JSON obliku

require_once __DIR__ . '../konfigBP.php';

function respond_json($data, $status = 200)
{
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

try {
  $pdo = db();
  $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY name');
  $rows = $stmt->fetchAll();

  respond_json([
    'categories' => $rows
  ], 200);
} catch (Throwable $e) {
  respond_json(['error' => 'Server error'], 500);
}
