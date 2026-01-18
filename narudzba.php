<?php require_once __DIR__.'/zaglavlje.php';

$cart = cart_get();
if (!$cart) { header('Location: '.BASE.'kosarica.php'); exit; }

// priprema artikala i ukupno
$items = []; $total = 0.0;
$ids = implode(',', array_map('intval', array_keys($cart)));
$rows = db()->query("SELECT id, name, price FROM pizzas WHERE id IN ($ids)")->fetchAll();
$map = []; foreach ($rows as $r) $map[$r['id']] = $r;
foreach ($cart as $pid=>$qty) {
  if (!isset($map[$pid])) continue;
  $line = $map[$pid];
  $line['qty'] = $qty;
  $line['sum'] = $qty * (float)$line['price'];
  $items[] = $line;
  $total += $line['sum'];
}

// podnijeti
if (isset($_POST['order'])) {
  check_csrf();
  $name  = trim($_POST['customer_name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $street= trim($_POST['street'] ?? '');
  $city  = trim($_POST['city'] ?? '');
  $postal= trim($_POST['postal'] ?? '');
  $note  = trim($_POST['note'] ?? '');

  if ($name && $phone && $street && $city && $postal && $items) {
    $pdo = db();
    $pdo->beginTransaction();
    $secret = bin2hex(random_bytes(16));
    $st = $pdo->prepare("INSERT INTO orders (customer_name, phone, street, city, postal, note, status, total, order_secret) VALUES (?,?,?,?,?,?,?,?,?)");
    $st->execute([$name,$phone,$street,$city,$postal,$note,'pending',$total,$secret]);
    $oid = (int)$pdo->lastInsertId();

    $sti = $pdo->prepare("INSERT INTO order_items (order_id, pizza_id, qty, unit_price) VALUES (?,?,?,?)");
    foreach ($items as $it) {
      $sti->execute([$oid, $it['id'], $it['qty'], $it['price']]);
    }
    $pdo->commit();

    cart_clear();
    header('Location: '.BASE.'narudzba_detalj.php?id='.$oid.'&secret='.$secret);
    exit;
  } else {
    $err = "Molimo ispunite sva obavezna polja.";
  }
}
?>
<h1>Podaci za dostavu</h1>
<?php if (!empty($err)): ?><p class="error"><?= htmlspecialchars($err) ?></p><?php endif; ?>

<form method="post" class="form">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <label>Ime i prezime* <input name="customer_name" required></label>
  <label>Telefon* <input name="phone" required></label>
  <label>Ulica i broj* <input name="street" required></label>
  <label>Grad* <input name="city" required></label>
  <label>Poštanski broj* <input name="postal" required></label>
  <label>Napomena <textarea name="note"></textarea></label>

  <h3>Pregled narudžbe</h3>
  <ul>
    <?php foreach ($items as $it): ?>
      <li><?= htmlspecialchars($it['name']) ?> × <?= $it['qty'] ?> — <?= number_format($it['sum'],2,',','.') ?> €</li>
    <?php endforeach; ?>
  </ul>
  <p><strong>Ukupno: <?= number_format($total,2,',','.') ?> €</strong></p>

  <button name="order">Potvrdi narudžbu</button>
</form>

<?php require_once __DIR__.'/podnozje.php'; ?>
