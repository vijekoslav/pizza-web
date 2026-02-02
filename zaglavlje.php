<?php require_once __DIR__ . '/konfigBP.php'; ?>
<!doctype html>
<html lang="hr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="icon" type="image/png" href="<?= BASE ?>slike/favicon2.png">
  <title>Michelangelo's Pizza</title>
  <link rel="stylesheet" href="<?= BASE ?>style.css">
  <script src="<?= BASE ?>script.js"></script>
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<body>
  <header>
    <a class="brand" href="<?= BASE ?>">Michelangelo's Pizza</a>
    <nav class="topnav">
      <?php if (!is_admin()): ?>
        <a href="<?= BASE ?>ponuda.php">Ponuda</a>
        <a href="<?= BASE ?>recepti.php">Recepti</a>
      <?php endif; ?>

      <?php if (is_admin()): ?>
        <a href="<?= BASE ?>admin_pizze.php">Ponuda</a>
        <a href="<?= BASE ?>admin_kategorije.php">Kategorije</a>
        <a href="<?= BASE ?>admin_narudzbe.php">Narudžbe</a>
        <a href="<?= BASE ?>admin_korisnici.php">Korisnici</a>
        <a href="<?= BASE ?>admin_lozinka.php">Lozinka</a>
        <a href="<?= BASE ?>admin_api.php">API</a>
      <?php endif; ?>

      <?php if (!is_admin()): ?>
        <a href="<?= BASE ?>kosarica.php" class="cart">
          <i class="fas fa-shopping-cart"></i>
          <span class="cart-count" id="cart-count">
            <?= array_sum($_SESSION['cart'] ?? []) ?>
          </span>
        </a>
      <?php endif; ?>

      <?php if (!empty($_SESSION['user_logged_in'])): ?>
        <span class="nav-user">
          <?= h($_SESSION['user_email']) ?>
        </span>
        <a href="<?= BASE ?>oauth/logout.php">
          <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
      <?php else: ?>
        <a href="<?= BASE ?>oauth/google_start.php">
          <i class="fa-brands fa-google"></i>
        </a>
      <?php endif; ?>

      <?php if (is_admin()): ?>
        <a href="<?= BASE ?>odjava.php">
          <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </a>
      <?php endif; ?>
    </nav>
  </header>
  <main>