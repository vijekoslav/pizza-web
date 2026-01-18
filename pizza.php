<?php require_once __DIR__.'/zaglavlje.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare("SELECT p.*, c.name AS cat_name FROM pizzas p JOIN categories c ON c.id=p.category_id WHERE p.id=?");
$st->execute([$id]);
$p = $st->fetch();

if (!$p) { http_response_code(404); echo "<h1>Pizza nije pronađena</h1>"; require 'podnozje.php'; exit; }

if (isset($_POST['add'])) {
  check_csrf();
  cart_add($id, (int)$_POST['qty']);
  header('Location: '.BASE.'kosarica.php'); exit;
}
?>
<article class="detail">
  <img src="<?= BASE ?>slike/<?= htmlspecialchars($p['image'] ?: 'noimg.jpg') ?>" alt="">
  <div>
    <h1><?= htmlspecialchars($p['name']) ?></h1>
    <p class="muted"><?= htmlspecialchars($p['cat_name']) ?></p>
    <p><?= nl2br(htmlspecialchars($p['description'] ?? '')) ?></p>
    <strong class="price"><?= number_format((float)$p['price'],2,',','.') ?> €</strong>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <input type="number" name="qty" value="1" min="1" style="width:64px">
      <button name="add">Dodaj u košaricu</button>
    </form>
  </div>
</article>
<?php require_once __DIR__.'/podnozje.php'; ?>
