<?php
require_once __DIR__ . '/konfigBP.php';
require_admin();

$pdo = db();

$msg = '';
$err = '';

// mapiranje statusa eng -> hr
$status_hr = [
  'pending'    => 'na čekanju',
  'confirmed'  => 'potvrđeno',
  'preparing'  => 'priprema',
  'delivering' => 'u dostavi',
  'done'       => 'gotovo',
  'canceled'   => 'otkazano',
];

// popis statusa
$status_order = [
  'pending',
  'confirmed',
  'preparing',
  'delivering',
  'done',
  'canceled',
];

//pPromjena statusa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $order_id   = $_POST['order_id']   ?? '';
  $new_status = $_POST['new_status'] ?? '';

  if (!ctype_digit($order_id)) {
    $err = 'Neispravan ID narudžbe.';
  } elseif (!in_array($new_status, $status_order, true)) {
    $err = 'Neispravan status.';
  } else {
    $pdo->prepare('UPDATE orders SET status=? WHERE id=?')
      ->execute([$new_status, (int)$order_id]);
    $msg = 'Status ažuriran.';
  }
}

// dohvat narudzbi
$narudzbe = $pdo->query(
  'SELECT id, customer_name, phone, city, total, status, created_at
  FROM orders
  ORDER BY id DESC'
)->fetchAll(PDO::FETCH_ASSOC);

// 3) Uključi zaglavlje
require_once __DIR__ . '/zaglavlje.php';
?>

<div class="admin-wrap admin-wide">

  <h1>Narudžbe</h1>

  <?php if ($err): ?><div class="admin-error"><?= h($err) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="admin-msg"><?= h($msg) ?></div><?php endif; ?>

  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Kupac</th>
          <th>Telefon</th>
          <th>Grad</th>
          <th>Iznos</th>
          <th>Status</th>
          <th>Kreirano</th>
          <th>Detalji</th>
          <th>Promjena</th>
        </tr>
      </thead>
      <tbody>

        <?php foreach ($narudzbe as $n): ?>
          <?php
          $id = $n['id'];
          $status_eng = $n['status'];
          $detalj_url = 'narudzba_detalj.php?id=' . urlencode($id);
          ?>
          <tr>
            <td><?= h($id) ?></td>
            <td><?= h($n['customer_name']) ?></td>
            <td><?= h($n['phone']) ?></td>
            <td><?= h($n['city']) ?></td>
            <td class="nowrap">
              <?= h(number_format((float)$n['total'], 2, ',', '.')) ?> €
            </td>
            <td><?= h($status_hr[$status_eng] ?? $status_eng) ?></td>
            <td class="nowrap"><?= h($n['created_at']) ?></td>
            <td>
              <a href="<?= h($detalj_url) ?>" target="_blank">
                otvori
              </a>
            </td>
            <td>
              <form method="post" class="inline-form">
                <input type="hidden" name="order_id" value="<?= h($id) ?>">

                <select name="new_status" class="admin-select">
                  <?php foreach ($status_order as $code): ?>
                    <option value="<?= h($code) ?>" <?= $code === $status_eng ? 'selected' : '' ?>>
                      <?= h($status_hr[$code]) ?>
                    </option>
                  <?php endforeach; ?>
                </select>

                <button class="btn small">Spremi</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (!$narudzbe): ?>
          <tr>
            <td colspan="9" class="table-empty">
              Nema narudžbi.
            </td>
          </tr>
        <?php endif; ?>

      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/podnozje.php'; ?>