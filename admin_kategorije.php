<?php
require_once __DIR__ . '/konfigBP.php';
require_admin();

$pdo = db();

$msg = '';
$err = '';

// DELETE
if (isset($_GET['brisi']) && ctype_digit($_GET['brisi'])) {
  try {
    $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([(int)$_GET['brisi']]);
    $msg = 'Kategorija obrisana.';
  } catch (PDOException $e) {
    $err = 'Ne možete obrisati kategoriju (postoje pizze u toj kategoriji).';
  }
}

// ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['akcija'] ?? '') === 'nova') {
  $name = trim($_POST['name'] ?? '');
  if ($name === '') {
    $err = 'Naziv je obavezan.';
  } else {
    $pdo->prepare('INSERT INTO categories (name) VALUES (?)')->execute([$name]);
    $msg = 'Kategorija dodana.';
  }
}

// EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['akcija'] ?? '') === 'uredi') {
  if (!ctype_digit($_POST['cid'] ?? '')) {
    $err = 'Neispravan ID.';
  } else {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
      $err = 'Naziv je obavezan.';
    } else {
      $pdo->prepare('UPDATE categories SET name=? WHERE id=?')
        ->execute([$name, (int)$_POST['cid']]);
      $msg = 'Kategorija spremljena.';
    }
  }
}

// sve kategorija
$kategorije = $pdo
  ->query('SELECT id, name FROM categories ORDER BY name')
  ->fetchAll();

require_once __DIR__ . '/zaglavlje.php';
?>

<div class="admin-wrap">

  <h1>Kategorije</h1>

  <?php if ($err): ?><div class="admin-error"><?= h($err) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="admin-msg"><?= h($msg) ?></div><?php endif; ?>

  <section class="admin-card">
    <h2>Nova kategorija</h2>

    <form method="post" class="admin-form">
      <input type="hidden" name="akcija" value="nova">

      <div class="field">
        <label>Naziv kategorije</label>
        <input type="text" name="name" required>
      </div>

      <button class="btn small">Dodaj</button>
    </form>
  </section>

  <section>
    <h2>Postojeće kategorije</h2>

    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Naziv</th>
            <th>Spremi</th>
            <th>Obriši</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($kategorije as $kat): ?>
            <tr>
              <td><?= h($kat['id']) ?></td>

              <td>
                <form method="post" class="inline-form">
                  <input type="hidden" name="akcija" value="uredi">
                  <input type="hidden" name="cid" value="<?= h($kat['id']) ?>">
                  <input type="text" name="name" value="<?= h($kat['name']) ?>" required>
              </td>

              <td class="save">
                <button class="btn-primary">Spremi</button>
                </form>
              </td>

              <td>
                <a class="btn delete"
                  href="<?= h('admin_kategorije.php?brisi=' . $kat['id']) ?>"
                  onclick="return confirm('Obrisati ovu kategoriju?');">
                  <i class="fa-solid fa-trash-can"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/podnozje.php'; ?>