<?php
// popis pizza u JSON obliku

require_once __DIR__ . '/../konfigBP.php';

function respond_json($data, $status = 200)
{
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

try {
  $pdo = db();

  $sql = 'SELECT p.id, p.name, p.description, p.price, p.image,
                  c.id   AS category_id,
                  c.name AS category_name
            FROM pizzas p
            JOIN categories c ON c.id = p.category_id
            ORDER BY c.name, p.name';

  $stmt = $pdo->query($sql);
  $rows = $stmt->fetchAll();

  respond_json([
    'pizzas' => $rows
  ], 200);
} catch (Throwable $e) {
  respond_json(['error' => 'Server error'], 500);
}
