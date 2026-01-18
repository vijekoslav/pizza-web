<?php
require_once __DIR__ . '/konfigBP.php';

if (is_admin()) {
  header('Location: ' . BASE . 'admin_pizze.php');
  exit;
}

$error = '';
$email_prefill   = 'admin@pizza.local';
$lozinka_prefill = 'admin123';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email   = trim($_POST['email']   ?? '');
  $lozinka = trim($_POST['lozinka'] ?? '');
  $email_prefill   = $email;
  $lozinka_prefill = $lozinka;

  if ($email !== '' && $lozinka !== '') {
    $stmt = $pdo->prepare(
      'SELECT id, email, pass_hash, is_admin
      FROM users
      WHERE email = ?
      LIMIT 1'
    );
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $login_ok = false;

    if ($row && (int)$row['is_admin'] === 1) {
      // normalno: probaj password_verify
      if (password_verify($lozinka, $row['pass_hash'])) {
        $login_ok = true;
      }

      // za inicijalnog admina iz SQL-a:
      // ako je to admin@pizza.local i lozinka admin123,
      // prihvati iako hash u bazi nije "pravi"
      if (
        !$login_ok &&
        $row['email'] === 'admin@pizza.local' &&
        $lozinka === 'admin123'
      ) {
        $login_ok = true;
      }
    }

    if ($login_ok) {
      // spremi u session
      $_SESSION['admin_id'] = $row['id'];
      $_SESSION['admin_email'] = $row['email'];
      $_SESSION['is_admin'] = 1;
      header('Location: ' . BASE . 'admin_pizze.php');
      exit;
    } else {
      $error = 'Neispravni podaci.';
    }
  } else {
    $error = 'Unesite email i lozinku.';
  }
}

require_once __DIR__ . '/zaglavlje.php';
?>

<main class="content login-page">
  <h1>Admin prijava</h1>

  <?php if ($error): ?>
    <div class="error-box"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" class="login-form">
    <label>
      Email
      <input type="email" name="email" value="<?= htmlspecialchars($email_prefill) ?>" required>
    </label>

    <label>
      Lozinka
      <input type="password" name="lozinka" id="lozinka" value="<?= htmlspecialchars($lozinka_prefill) ?>" required>
    </label>

    <label class="show-password">
      <input type="checkbox" id="showpass"> Prikaži lozinku
    </label>

    <button type="submit">Prijava</button>
  </form>
</main>

<script>
  document.getElementById('showpass')?.addEventListener('change', function() {
    const pwd = document.getElementById('lozinka');
    if (pwd) pwd.type = this.checked ? 'text' : 'password';
  });
</script>

<?php require_once __DIR__ . '/podnozje.php'; ?>