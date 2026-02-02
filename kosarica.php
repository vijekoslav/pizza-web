<?php require_once __DIR__ . '/zaglavlje.php'; ?>

<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function inparam(array $keys, $default = null)
{
  foreach ($keys as $k) {
    if (isset($_POST[$k])) return $_POST[$k];
    if (isset($_GET[$k])) return $_GET[$k];
  }
  return $default;
}

$prefill_name = '';
if (!empty($_SESSION['user_logged_in'])) {
  $prefill_name = $_SESSION['user_name'];
}

$success = '';
$error   = '';

/** dodavanje u košaricu */
if (isset($_POST['add']) || isset($_GET['add'])) {
  $id  = (int) inparam(['pizza_id', 'id', 'pid', 'p'], 0);
  $qty = (int) inparam(['qty', 'kolicina', 'q'], 1);
  if ($id > 0 && $qty > 0) {
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + $qty;
  }
}

/** uklanjanje stavke */
if (isset($_POST['remove'])) {
  $rid = (int)$_POST['remove'];
  if ($rid > 0 && isset($_SESSION['cart'][$rid])) unset($_SESSION['cart'][$rid]);
}

/** pražnjenje košarice */
if (isset($_POST['empty'])) {
  $_SESSION['cart'] = [];
}

/** ažuriranje količina */
if (isset($_POST['update_qty']) && !empty($_POST['qty']) && is_array($_POST['qty'])) {
  foreach ($_POST['qty'] as $pid => $q) {
    $pid = (int)$pid;
    $q = (int)$q;
    if ($pid > 0) {
      if ($q > 0) $_SESSION['cart'][$pid] = $q;
      else unset($_SESSION['cart'][$pid]);
    }
  }
}

/** priprema podataka za prikaz */
$items = [];
$total = 0.0;

if (!empty($_SESSION['cart'])) {
  $ids = array_keys($_SESSION['cart']);
  $ph  = implode(',', array_fill(0, count($ids), '?'));
  $st  = db()->prepare("SELECT id, name, price, image FROM pizzas WHERE id IN ($ph) ORDER BY name");
  $st->execute($ids);
  foreach ($st->fetchAll() as $r) {
    $id  = (int)$r['id'];
    $qty = (int)($_SESSION['cart'][$id] ?? 0);
    if ($qty > 0) {
      $line = $qty * (float)$r['price'];
      $total += $line;
      $items[] = [
        'id'    => $id,
        'name'  => $r['name'],
        'image' => $r['image'],
        'price' => (float)$r['price'],
        'qty'   => $qty,
        'sum'   => $line,
      ];
    }
  }
}

/** blagajna - kreiranje narudžbe */
if (isset($_POST['checkout'])) {
  $name     = trim($_POST['name'] ?? '');
  $phone    = trim($_POST['phone'] ?? '');
  $city     = trim($_POST['city'] ?? '');
  $postcode = trim($_POST['postcode'] ?? '');
  $address  = trim($_POST['address'] ?? '');

  if ($name === '' || $phone === '' || $city === '') {
    $error = 'Molimo ispunite obavezna polja (ime, telefon, grad).';
  } elseif (empty($_SESSION['cart'])) {
    $error = 'Košarica je prazna.';
  } else {
    try {
      $pdo = db();
      $pdo->beginTransaction();

      $ids = array_keys($_SESSION['cart']);
      $ph  = implode(',', array_fill(0, count($ids), '?'));
      $st  = $pdo->prepare("SELECT id, price FROM pizzas WHERE id IN ($ph)");
      $st->execute($ids);
      $prices = [];
      foreach ($st->fetchAll() as $r) {
        $prices[(int)$r['id']] = (float)$r['price'];
      }

      $order_total = 0.0;
      foreach ($_SESSION['cart'] as $pid => $q) {
        $q = (int)$q;
        if ($q > 0 && isset($prices[$pid])) {
          $order_total += $q * $prices[$pid];
        }
      }

      $secret = bin2hex(random_bytes(12));

      $ins = $pdo->prepare("INSERT INTO orders
                (customer_name, phone, city, postcode, total, status, order_secret)
                VALUES (?,?,?,?,?, 'pending', ?)");
      $ins->execute([$name, $phone, $city, $postcode, $order_total, $secret]);

      $order_id = (int)$pdo->lastInsertId();

      $insIt = $pdo->prepare("INSERT INTO order_items (order_id, pizza_id, qty, unit_price) VALUES (?,?,?,?)");
      foreach ($_SESSION['cart'] as $pid => $q) {
        $q = (int)$q;
        if ($q > 0 && isset($prices[$pid])) {
          $insIt->execute([$order_id, (int)$pid, $q, $prices[$pid]]);
        }
      }

      $pdo->commit();
      $_SESSION['cart'] = [];

      $success    = "Narudžba zaprimljena! Broj #$order_id";
      $order_link = BASE . "narudzba_detalj.php?id=$order_id&secret=$secret";
    } catch (Throwable $e) {
      if ($pdo->inTransaction()) $pdo->rollBack();
      $error = 'Greška pri spremanju narudžbe.';
    }
  }
}
?>

<div class="cart-container content">
  <h1>Košarica</h1>

  <?php if ($success): ?>
    <p class="ok">
      <?= htmlspecialchars($success) ?> —
      <a href="<?= htmlspecialchars($order_link) ?>" target="_blank" rel="noopener">otvori detalje</a>
    </p>
  <?php endif; ?>

  <?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <?php if (empty($items)): ?>
    <p>Košarica je prazna.</p>
    <p><a class="back-btn" href="<?= BASE ?>ponuda.php">← Natrag na ponudu</a></p>
  <?php else: ?>

    <form method="post" class="cart-form">
      <table class="cart-table">
        <thead>
          <tr>
            <th>Pizza</th>
            <th>Cijena</th>
            <th>Količina</th>
            <th>Iznos</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td>
                <div class="cart-item">
                  <img src="<?= BASE ?>slike/<?= htmlspecialchars($it['image'] ?: 'no-photo.jpg') ?>" alt="">
                  <?= htmlspecialchars($it['name']) ?>
                </div>
              </td>
              <td><?= number_format($it['price'], 2, ',', '.') ?> €</td>
              <td><input type="number" name="qty[<?= (int)$it['id'] ?>]" min="1" value="<?= (int)$it['qty'] ?>"></td>
              <td><?= number_format($it['sum'], 2, ',', '.') ?> €</td>
              <td><button name="remove" value="<?= (int)$it['id'] ?>">Ukloni</button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" style="text-align:right">Ukupno:</th>
            <th><?= number_format($total, 2, ',', '.') ?> €</th>
            <th></th>
          </tr>
        </tfoot>
      </table>

      <div class="cart-actions">
        <button name="update_qty">Ažuriraj količine</button>
        <button name="empty" onclick="return confirm('Isprazniti košaricu?')">Isprazni košaricu</button>
        <a class="back-btn" href="<?= BASE ?>ponuda.php">← Natrag na ponudu</a>
      </div>
    </form>

    <h2>Podaci za dostavu</h2>
    <form method="post" class="delivery-form">
      <label>Ime i prezime <input name="name" value="<?= h($prefill_name) ?>" autocomplete="name" required></label>
      <label>Telefon <input name="phone" required></label>
      <label>Adresa <input name="address" id="addr" autocomplete="off" required></label>
      <label>Grad <input name="city" id="city" required></label>
      <label>Poštanski broj <input name="postcode" id="zip" required></label>
      <button name="checkout">Potvrdi narudžbu</button>
    </form>

  <?php endif; ?>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const addr = document.getElementById('addr');
    const city = document.getElementById('city');
    const zip = document.getElementById('zip');
    if (!addr || !city || !zip) return;

    let t; // za debounce

    addr.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(async () => {
        const q = addr.value.trim();

        // Ako je polje prazno → očisti grad i poštanski broj
        if (q.length < 3) {
          city.value = '';
          zip.value = '';
          return;
        }

        try {
          const r = await fetch('<?= BASE ?>api/geo.php?address=' + encodeURIComponent(q));
          if (!r.ok) throw new Error('Network response was not ok');

          const j = await r.json();

          // Ako API vrati rezultat → popuni polja
          if (j && j.result) {
            city.value = j.result.city || '';
            zip.value = j.result.postcode || '';
          } else {
            // Nema rezultata → očisti
            city.value = '';
            zip.value = '';
          }

        } catch (e) {
          console.log('Greška prilikom dohvaćanja adrese', e);
          city.value = '';
          zip.value = '';
        }

      }, 1000); // debounce 600ms
    });
  });
</script>

<?php require_once __DIR__ . '/podnozje.php'; ?>