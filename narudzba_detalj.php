<?php
require_once __DIR__ . '/konfigBP.php';

$pdo = db();

// parametri iz URL-a
$id     = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
$secret = $_GET['secret'] ?? '';

if ($id <= 0) {
  http_response_code(400);
  echo "Neispravan zahtjev (nema ID).";
  exit;
}

// dohvati narudžbe
// admin smije po ID-u bez tokena
// kupac mora imati i ID i token
if (is_admin()) {
  $stmt = $pdo->prepare(
    'SELECT id, customer_name, phone, city,
                status, total, created_at
        FROM orders
        WHERE id = ?
        LIMIT 1'
  );
  $stmt->execute([$id]);
} else {
  $stmt = $pdo->prepare(
    'SELECT id, customer_name, phone, city,
                status, total, created_at
        FROM orders
        WHERE id = ?
        AND order_secret = ?
        LIMIT 1'
  );
  $stmt->execute([$id, $secret]);
}

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  http_response_code(404);
  exit('Narudžba nije pronađena.');
}

// dohvat stavki
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
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

$status_map = [
  'pending'    => 'na čekanju',
  'confirmed'  => 'potvrđeno',
  'preparing'  => 'priprema',
  'delivering' => 'u dostavi',
  'done'       => 'gotovo',
  'canceled'   => 'otkazano',
];

$status_hr = $status_map[$order['status']] ?? $order['status'];

require_once __DIR__ . '/zaglavlje.php';
?>

<div class="admin-wrap">

  <h1>Narudžba #<?= h($order['id']) ?></h1>

  <p class="order-status">
    <strong>Status:</strong> <?= h($status_hr) ?>
  </p>

  <p class="order-delivery">
    <strong>Dostava:</strong>
    <?= h($order['customer_name']) ?>
    <?= $order['city'] ? ', ' . h($order['city']) : '' ?>
    <?= $order['phone'] ? ', tel: ' . h($order['phone']) : '' ?>
  </p>

  <?php if ($order['created_at']): ?>
    <p class="order-date">
      Kreirano: <?= h($order['created_at']) ?>
    </p>
  <?php endif; ?>

  <ul class="order-items">
    <?php foreach ($items as $it): ?>
      <li>
        <span><?= h($it['pizza_name']) ?> × <?= h($it['qty']) ?></span>
        <span><?= h(number_format((float)$it['unit_price'], 2, ',', '.')) ?> €</span>
      </li>
    <?php endforeach; ?>
  </ul>

  <p class="order-total">
    Ukupno:
    <?= h(number_format((float)$order['total'], 2, ',', '.')) ?> €
  </p>

</div>

<?php require_once __DIR__ . '/podnozje.php'; ?>