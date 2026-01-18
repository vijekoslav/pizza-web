<?php
require_once __DIR__ . '/konfigBP.php';
require_admin();

$pdo = db();

$UPLOAD_DIR_REL = 'slike/'; // relative path
$UPLOAD_DIR_ABS = __DIR__ . '/slike/'; // fizički folder

$msg = '';
$err = '';

// spremanje uploadane slike i vraćanje imena datoteke ili null
function handle_image_upload(string $field_name, string $UPLOAD_DIR_ABS): ?string
{
  if (!isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] !== UPLOAD_ERR_OK) {
    return null;
  }
  $f = $_FILES[$field_name];
  // original filename (no path)
  $orig = basename($f['name']);
  if ($orig === '') {
    return null;
  }

  // split name + extension
  $ext  = pathinfo($orig, PATHINFO_EXTENSION);
  $name = pathinfo($orig, PATHINFO_FILENAME);
  // clean filename (letters, numbers, dash, underscore)
  $name = preg_replace('/[^\w\-]+/u', '_', $name);
  $ext  = preg_replace('/[^\w]+/u', '', $ext);

  if ($name === '' || $ext === '') {
    return null;
  }
  if (!is_dir($UPLOAD_DIR_ABS)) {
    @mkdir($UPLOAD_DIR_ABS, 0777, true);
  }

  // prevent overwrite → add timestamp if file exists
  $filename = $name . '.' . $ext;
  $dest = $UPLOAD_DIR_ABS . $filename;

  if (file_exists($dest)) {
    $filename = $name . '_' . time() . '.' . $ext;
    $dest = $UPLOAD_DIR_ABS . $filename;
  }

  if (!move_uploaded_file($f['tmp_name'], $dest)) {
    return null;
  }

  return $filename;
}

// DELETE
if (isset($_GET['brisi']) && ctype_digit($_GET['brisi'])) {
  try {
    $pdo->prepare('DELETE FROM pizzas WHERE id=?')->execute([$_GET['brisi']]);
    $msg = 'Pizza obrisana.';
  } catch (PDOException $e) {
    $err = 'Ne možete obrisati pizzu (možda je korištena u narudžbama).';
  }
}

// ADD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['akcija'] ?? '') === 'nova') {
  $category_id = $_POST['category_id'] ?? '';
  $name = trim($_POST['name'] ?? '');
  $desc = trim($_POST['description'] ?? '');
  $price = trim($_POST['price'] ?? '');

  if (!ctype_digit($category_id) || $name === '' || $price === '') {
    $err = 'Ispunite sva obavezna polja.';
  } else {
    $img = handle_image_upload('slika', $UPLOAD_DIR_ABS);
    $pdo->prepare(
      'INSERT INTO pizzas (category_id, name, description, price, image)
      VALUES (?,?,?,?,?)'
    )->execute([$category_id, $name, $desc, $price, $img]);
    $msg = 'Pizza dodana.';
  }
}

// EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['akcija'] ?? '') === 'uredi') {
  $pid = $_POST['pid'] ?? '';
  $category_id = $_POST['category_id'] ?? '';
  $name = trim($_POST['name'] ?? '');
  $desc = trim($_POST['description'] ?? '');
  $price = trim($_POST['price'] ?? '');

  if (!ctype_digit($pid) || !ctype_digit($category_id)) {
    $err = 'Neispravan ID ili kategorija.';
  } elseif ($name === '' || $price === '') {
    $err = 'Naziv i cijena su obavezni.';
  } else {
    $img = handle_image_upload('nova_slika', $UPLOAD_DIR_ABS);

    if ($img) {
      $pdo->prepare(
        'UPDATE pizzas
        SET category_id = ?, name = ?, description = ?, price = ?, image = ?
        WHERE id = ?'
      )->execute([$category_id, $name, $desc, $price, $img, $pid]);
    } else {
      $pdo->prepare(
        'UPDATE pizzas
        SET category_id = ?, name = ?, description = ?, price = ?
        WHERE id = ?'
      )->execute([$category_id, $name, $desc, $price, $pid]);
    }
    $msg = 'Pizza spremljena.';
  }
}

// popis kategorija za dropdown
$kategorije = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

// popis pizza za tablicu
$pizze = $pdo->query(
  'SELECT p.id, p.name, p.description, p.price, p.image,
          c.name AS kat_ime, c.id AS kat_id
  FROM pizzas p
  JOIN categories c ON c.id = p.category_id
  ORDER BY p.id DESC'
)->fetchAll();

require_once __DIR__ . '/zaglavlje.php';
?>

<div class="admin-wrap">
  <h1>Pizze</h1>

  <?php if ($err): ?><div class="alert error"><?= h($err) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="alert ok"><?= h($msg) ?></div><?php endif; ?>

  <section class="admin-card">
    <h2>Nova pizza</h2>

    <form method="post" enctype="multipart/form-data" class="form-grid">
      <input type="hidden" name="akcija" value="nova">

      <label>Kategorija
        <select name="category_id">
          <?php foreach ($kategorije as $kat): ?>
            <option value="<?= h($kat['id']) ?>"><?= h($kat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Naziv
        <input type="text" name="name" required>
      </label>

      <label>Opis
        <input type="text" name="description">
      </label>

      <label>Cijena (€)
        <input type="number" step="0.01" min="0" name="price" required>
      </label>

      <label>Slika
        <input type="file" name="slika" accept="image/*">
      </label>

      <button class="btn small">Spremi</button>
    </form>
  </section>

  <section>
    <h2>Popis pizza</h2>

    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Naziv</th>
          <th>Kategorija</th>
          <th>Cijena</th>
          <th>Slika</th>
          <th>Uredi</th>
          <th>Obriši</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pizze as $p): ?>
          <tr>
            <td><?= h($p['id']) ?></td>
            <td><?= h($p['name']) ?><br><small><?= h($p['description']) ?></small></td>
            <td><?= h($p['kat_ime']) ?></td>
            <td><?= number_format($p['price'], 2, ',', '.') ?> €</td>
            <td>
              <?php if ($p['image']): ?>
                <img src="<?= h($UPLOAD_DIR_REL . $p['image']) ?>" class="thumb">
              <?php else: ?>
                <span class="admin-muted">nema</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="post" enctype="multipart/form-data" class="edit-form">
                <input type="hidden" name="akcija" value="uredi">
                <input type="hidden" name="pid" value="<?= h($p['id']) ?>">

                <select name="category_id">
                  <?php foreach ($kategorije as $kat): ?>
                    <option value="<?= h($kat['id']) ?>" <?= $kat['id'] == $p['kat_id'] ? 'selected' : '' ?>>
                      <?= h($kat['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>

                <input type="text" name="name" value="<?= h($p['name']) ?>">
                <input type="text" name="description" value="<?= h($p['description']) ?>">
                <input type="number" step="0.01" name="price" value="<?= h($p['price']) ?>">
                <input type="file" name="nova_slika" accept="image/*">

                <button class="btn small">Spremi</button>
              </form>
            </td>
            <td>
              <a class="btn delete" onclick="return confirm('Obrisati pizzu?')"
                href="admin_pizze.php?brisi=<?= h($p['id']) ?>">
                <i class="fa-solid fa-trash-can"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>

<?php require_once __DIR__ . '/podnozje.php'; ?>