<?php require_once __DIR__ . '/zaglavlje.php'; ?>

<main class="hero">
  <div class="container hero-content">
    <div class="hero-text">
      <h1>Cowabunga!</h1>
      <p class="tagline">Najsvježije pizze u hrvatskim kanalizacijama!</p>
      <a href="<?= BASE ?>ponuda.php" class="btn-primary">Čekiraj ponudu</a>
    </div>

    <div class="hero-image">
      <img src="<?= BASE ?>slike/hero-pizza.png" alt="It's pizza time!">
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/podnozje.php'; ?>