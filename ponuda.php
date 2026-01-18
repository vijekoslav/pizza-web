<?php
require_once __DIR__ . '/konfigBP.php';
require_once __DIR__ . '/zaglavlje.php';
?>

<main class="content">
  <h1>MENU</h1>

  <?php
  // kategorije za filter
  $categories = db()->query(
    "SELECT id, name
    FROM categories
    ORDER BY name"
  )->fetchAll();
  $k = isset($_GET['k']) ? (int)$_GET['k'] : 0;

  // dohvat pizza
  if ($k > 0) {
    $st = db()->prepare(
      "SELECT p.*, c.name AS category_name
      FROM pizzas p
      JOIN categories c ON c.id=p.category_id
      WHERE p.category_id = ?
      ORDER BY p.name"
    );
    $st->execute([$k]);
    $pizzas = $st->fetchAll();
  } else {
    $pizzas = db()->query(
      "SELECT p.*, c.name AS category_name
      FROM pizzas p
      JOIN categories c ON c.id=p.category_id
      ORDER BY c.name, p.name"
    )->fetchAll();
  }
  ?>

  <!-- filtriranje -->
  <form method="get" class="filter-form">
    <label>
      Kategorija:
      <select name="k" onchange="this.form.submit()">
        <option value="0">Sve</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $k === $c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
  </form>

  <!-- ponuda -->
  <div class="grid">
    <?php foreach ($pizzas as $p): ?>
      <article class="card">
        <img src="<?= BASE ?>slike/<?= htmlspecialchars($p['image'] ?: 'no-photo.jpg') ?>"
          alt="<?= htmlspecialchars($p['name']) ?>">

        <h3><?= htmlspecialchars($p['name']) ?></h3>
        <div class="muted"><?= htmlspecialchars($p['category_name']) ?></div>
        <p><?= htmlspecialchars($p['description'] ?? '') ?></p>

        <div class="row">
          <div class="price"><?= number_format((float)$p['price'], 2, ',', '.') ?> €</div>

          <div class="cart-action">
            <input type="number" id="qty-<?= $p['id'] ?>" min="1" value="1" class="qty-input">
            <button type="button" class="add-btn" onclick="addToCart(<?= $p['id'] ?>)">Dodaj</button>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</main>

<?php require_once __DIR__ . '/podnozje.php'; ?>