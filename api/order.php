<?php
// detalj jedne narudžbe u JSON obliku

require_once __DIR__ . '/../konfigBP.php';

function respond_json($data, $status = 200)
{
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}

$id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
$secret = $_GET['secret'] ?? '';

if ($id <= 0) {
  respond_json(['error' => 'Nedostaje parametar id.'], 400);
}

$pdo = db();

if (is_admin()) {
  $stmt = $pdo->prepare(
    'SELECT id, customer_name, phone, city, status, total, created_at
    FROM orders
    WHERE id = ?
    LIMIT 1'
  );
  $stmt->execute([$id]);
} else {
  $stmt = $pdo->prepare(
    'SELECT id, customer_name, phone, city, status, total, created_at
    FROM orders
    WHERE id = ?
    AND secret = ?
    LIMIT 1'
  );
  $stmt->execute([$id, $secret]);
}

$order = $stmt->fetch();

if (!$order) {
  respond_json(['error' => 'Narudžba nije pronađena.'], 404);
}

$itemStmt = $pdo->prepare(
  'SELECT 
        COALESCE(p.name, "(pizza obrisana)") AS pizza_name,
        oi.qty,
        COALESCE(p.price, 0.00) AS unit_price
    FROM order_items oi
    LEFT JOIN pizzas p ON p.id = oi.pizza_id
    WHERE oi.order_id = ?
    ORDER BY oi.id'
);
$itemStmt->execute([$order['id']]);
$itemsRaw = $itemStmt->fetchAll();

$itemsOut = [];
foreach ($itemsRaw as $r) {
  $itemsOut[] = [
    'pizza' => $r['pizza_name'],
    'qty' => (int)$r['qty'],
    'price' => (float)$r['unit_price'],
  ];
}

respond_json([
  'order' => [
    'id' => (int)$order['id'],
    'customer_name' => $order['customer_name'],
    'city' => $order['city'],
    'phone' => $order['phone'],
    'status' => $order['status'],
    'total' => (float)$order['total'],
    'created_at' => $order['created_at'],
    'items' => $itemsOut,
  ]
], 200);
