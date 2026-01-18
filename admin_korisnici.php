<?php
require_once __DIR__ . '/konfigBP.php';
require_admin();

$pdo = db();

$msg = '';
$err = '';

// ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['akcija'] ?? '') === 'novi') {

  $email = trim($_POST['email'] ?? '');
  $loz1 = trim($_POST['lozinka1'] ?? '');
  $loz2 = trim($_POST['lozinka2'] ?? '');
  $is_admin_novi = isset($_POST['is_admin']) ? 1 : 0;

  if ($email === '' || $loz1 === '' || $loz2 === '') {
    $err = 'Sva polja su obavezna.';
  } elseif ($loz1 !== $loz2) {
    $err = 'Lozinke se ne podudaraju.';
  } else {
    $provjera = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $provjera->execute([$email]);

    if ($provjera->fetch()) {
      $err = 'Email već postoji.';
    } else {
      $hash = password_hash($loz1, PASSWORD_BCRYPT);
      $ins = $pdo->prepare(
        'INSERT INTO users (email, pass_hash, is_admin) VALUES (?, ?, ?)'
      );
      $ins->execute([$email, $hash, $is_admin_novi]);
      $msg = 'Korisnik dodan.';
    }
  }
}

// EDIT OVLASTI
if (
  isset($_GET['user_id'], $_GET['admin']) &&
  ctype_digit($_GET['user_id']) &&
  ($_GET['admin'] === '0' || $_GET['admin'] === '1')
) {
  $uid = (int)$_GET['user_id'];
  $adminFlag = (int)$_GET['admin'];

  if ($uid === (int)($_SESSION['admin_id'] ?? 0) && $adminFlag === 0) {
    $err = 'Nije moguće sam sebi ukinuti admin ovlasti.';
  } else {
    $upd = $pdo->prepare('UPDATE users SET is_admin = ? WHERE id = ?');
    $upd->execute([$adminFlag, $uid]);
    $msg = 'Ovlasti ažurirane.';
  }
}

// dohvat korisnika
$korisnici = $pdo->query(
  'SELECT id, email, is_admin, created_at
  FROM users
  ORDER BY id DESC'
)->fetchAll();

require_once __DIR__ . '/zaglavlje.php';
?>

<div class="admin-wrap">
  <h1>Korisnici</h1>

  <?php if ($err): ?><div class="admin-error"><?= h($err) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="admin-msg"><?= h($msg) ?></div><?php endif; ?>

  <section class="admin-card">
    <h2>Dodaj novog korisnika</h2>

    <form method="post" class="admin-form">
      <input type="hidden" name="akcija" value="novi">

      <div class="field">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>

      <div class="field">
        <label>Lozinka</label>
        <input type="password" name="lozinka1" required>
      </div>

      <div class="field">
        <label>Ponovi lozinku</label>
        <input type="password" name="lozinka2" required>
      </div>

      <label class="checkbox">
        <input type="checkbox" name="is_admin">
        <span>Admin ovlasti</span>
      </label>

      <button type="submit" class="btn">Dodaj</button>
    </form>
  </section>


  <section>
    <h2>Popis korisnika</h2>

    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Uloga</th>
            <th>Kreirano</th>
            <th>Akcija</th>
          </tr>
        </thead>
        <tbody>

          <?php foreach ($korisnici as $u): ?>
            <?php
            $je_admin = (int)$u['is_admin'] === 1;

            if ($je_admin) {
              if ((int)$u['id'] === (int)($_SESSION['admin_id'] ?? 0)) {
                $akcija = '<span class="admin-muted">(tvoj račun)</span>';
              } else {
                $akcija = '<a class="btn danger" href="' .
                  h('admin_korisnici.php?user_id=' . $u['id'] . '&admin=0') .
                  '">Skini admin</a>';
              }
            } else {
              $akcija = '<a class="btn success" href="' .
                h('admin_korisnici.php?user_id=' . $u['id'] . '&admin=1') .
                '">Daj admina</a>';
            }
            ?>
            <tr>
              <td><?= h($u['id']) ?></td>
              <td><?= h($u['email']) ?></td>
              <td>
                <?= $je_admin ? '<span class="role admin">admin</span>' : '<span class="role user">korisnik</span>' ?>
              </td>
              <td><?= h($u['created_at']) ?></td>
              <td><?= $akcija ?></td>
            </tr>
          <?php endforeach; ?>

        </tbody>
      </table>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/podnozje.php'; ?>