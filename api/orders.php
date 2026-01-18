<?php
// popis narudžbi u JSON obliku

require_once __DIR__ . '/../konfigBP.php';

function respond_json($data, $status = 200)
{
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

if (!is_admin()) {
  respond_json(['error' => 'Zabranjeno (samo admin).'], 403);
}

try {
  $pdo = db();

  $stmt = $pdo->query(
    'SELECT id, customer_name, city, phone, total, status, created_at
    FROM orders
    ORDER BY id DESC'
  );
  $rows = $stmt->fetchAll();

  respond_json([
    'orders' => $rows
  ], 200);
} catch (Throwable $e) {
  respond_json(['error' => 'Server error'], 500);
}
