<?php
require_once __DIR__ . '/konfigBP.php';
require_once __DIR__ . '/zaglavlje.php';
?>

<div class="recipes-page">
  <h1>Recepti</h1>

  <form class="recipes-search" onsubmit="return false;">
    <input id="recipe-q" type="text" placeholder="Upiši npr. pizza, pepperoni..." value="pizza" autocomplete="off">
    <button id="recipe-btn" type="button">Traži</button>
  </form>

  <div id="recipes-status" class="muted"></div>
  <div id="recipes-grid" class="recipes-grid"></div>
</div>

<script>
  (function() {
    const qEl = document.getElementById('recipe-q');
    const btn = document.getElementById('recipe-btn');
    const grid = document.getElementById('recipes-grid');
    const status = document.getElementById('recipes-status');

    async function load() {
      const q = (qEl.value || 'pizza').trim();
      status.textContent = 'Učitavam...';
      grid.innerHTML = '';

      try {
        const r = await fetch('<?= BASE ?>api/recipes.php?q=' + encodeURIComponent(q));
        const j = await r.json();

        if (!r.ok || j.error) {
          status.textContent = j.error || 'Greška.';
          return;
        }

        const recipes = j.recipes || [];
        if (!recipes.length) {
          status.textContent = 'Nema rezultata.';
          return;
        }

        status.textContent = '';

        for (const it of recipes) {
          const card = document.createElement('article');
          card.className = 'recipe-card';
          card.innerHTML = `
          <img src="${it.thumb}" alt="">
          <div class="recipe-card__body">
            <h3>${it.name}</h3>
            <div class="muted">${[it.category, it.area].filter(Boolean).join(' • ')}</div>
            <div class="recipe-links">
              ${it.source ? `<a href="${it.source}" target="_blank" rel="noopener">Izvor</a>` : ''}
              ${it.youtube ? `<a href="${it.youtube}" target="_blank" rel="noopener">YouTube</a>` : ''}
            </div>
          </div>
        `;
          grid.appendChild(card);
        }
      } catch (e) {
        status.textContent = 'Greška pri dohvaćanju.';
      }
    }

    btn.addEventListener('click', load);
    qEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') load();
    });

    load();
  })();
</script>

<?php require_once __DIR__ . '/podnozje.php'; ?>