<?php
require_once __DIR__ . '/konfigBP.php';
require_admin();
require_once __DIR__ . '/zaglavlje.php';

$ok = $err = '';

if (isset($_POST['promijeni'])) {
  $old = $_POST['old'] ?? '';
  $new = $_POST['new'] ?? '';
  $rep = $_POST['rep'] ?? '';

  if (!$old || !$new || !$rep) {
    $err = 'Ispuni sva polja.';
  } elseif ($new !== $rep) {
    $err = 'Nova lozinka i ponovljena nisu iste.';
  } elseif (strlen($new) < 6) {
    $err = 'Nova lozinka mora imati barem 6 znakova.';
  } else {
    $st = db()->prepare('SELECT pass_hash FROM users WHERE id=? LIMIT 1');
    $st->execute([$_SESSION['admin_id']]);
    $row = $st->fetch();
    if (!$row || !password_verify($old, $row['pass_hash'])) {
      $err = 'Trenutna lozinka nije ispravna.';
    } else {
      $hash = password_hash($new, PASSWORD_BCRYPT);
      $up = db()->prepare('UPDATE users SET pass_hash=? WHERE id=?');
      $up->execute([$hash, $_SESSION['admin_id']]);
      // radi sigurnosti — odjava pa ponovna prijava
      session_destroy();
      header('Location: ' . BASE . 'prijava.php');
      exit;
    }
  }
}
?>

<main class="content login-page">
  <h1>Promjena lozinke</h1>

  <?php if ($err): ?>
    <div class="error-box"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <p>(Nakon uspješne promjene bit ćeš odjavljen i trebaš se ponovno prijaviti.)</p>

  <form method="post" class="login-form">
    <label>Trenutna lozinka
      <input id="pwd-old" type="password" name="old" required autocomplete="current-password">
    </label>

    <label>Nova lozinka
      <input id="pwd-new" type="password" name="new" required autocomplete="new-password">
    </label>

    <label>Ponovi novu lozinku
      <input id="pwd-rep" type="password" name="rep" required autocomplete="new-password">
    </label>

    <label class="show-password">
      <input type="checkbox" id="showpass"> Prikaži lozinku
    </label>

    <button name="promijeni">Spremi</button>
  </form>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('showPwds');
    if (!toggle) return;

    const fields = ['pwd-old', 'pwd-new', 'pwd-rep']
      .map(id => document.getElementById(id))
      .filter(Boolean);

    toggle.addEventListener('change', function() {
      const type = this.checked ? 'text' : 'password';
      fields.forEach(f => f.type = type);
    });
  });
</script>

<?php require_once __DIR__ . '/podnozje.php'; ?>