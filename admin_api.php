<?php
require_once __DIR__ . '/konfigBP.php';
require_admin();
require_once __DIR__ . '/zaglavlje.php';

$base = rtrim(BASE, '/');
?>

<div class="admin-wrap admin-wide">

  <h1>API pregled sustava</h1>
  <p class="admin-muted admin-wide admin-p">
    Ovdje su prikazani REST API pozivi našeg sustava.
    JSON odgovori su prikazani ispod svakog URL-a.
    Stranica služi <b>za lokalnu demonstraciju i testiranje</b>.
  </p>

  <div class="api-grid">

    <!-- Pizza API -->
    <div class="api-card">
      <h2>Popis pizza (javni)</h2>
      <p>Dohvaća sve pizze: naziv, opis, cijena, kategorija, slika.</p>
      <div class="api-url">GET <?= h($base) ?>/api/pizzas.php</div>
      <pre>{
  "pizzas": [
    { "id": 1, "name": "Margherita", "description": "...", "price": 5.50, "category_name": "Klasične", "image": "margherita.jpg" },
    { "id": 2, "name": "Capricciosa", "description": "...", "price": 8.90, "category_name": "Specijalne", "image": "capricciosa.jpg" }
  ]
}</pre>
    </div>

    <!-- Categories API -->
    <div class="api-card">
      <h2>Kategorije pizza (javni)</h2>
      <p>Dohvaća sve kategorije pizza (Klasične, Specijalne, Vegetarijanske...)</p>
      <div class="api-url">GET <?= h($base) ?>/api/categories.php</div>
      <pre>{
  "categories": [
    { "id": 1, "name": "Klasične" },
    { "id": 2, "name": "Specijalne" },
    { "id": 3, "name": "Vegetarijanske" }
  ]
}</pre>
    </div>

    <!-- Orders API -->
    <div class="api-card">
      <h2>Narudžbe (interni)</h2>
      <p>Prikazuje listu svih narudžbi (samo za admina).</p>
      <div class="api-url">GET <?= h($base) ?>/api/orders.php</div>
      <pre>{
  "orders": [
    { "id": 12, "customer_name": "Marko Markić", "city": "Zagreb", "total": 19.80, "status": "pending", "created_at": "2025-11-19 18:42:10" }
  ]
}</pre>
    </div>

    <!-- Order detail API -->
    <div class="api-card">
      <h2>Detalji narudžbe</h2>
      <p>Pruža detalje narudžbe s stavkama. Kupac koristi secret link.</p>
      <div class="api-url">GET <?= h($base) ?>/api/order.php?id=12&secret=OVDJE_SECRET</div>
      <pre>{
  "order": {
    "id": 12,
    "customer_name": "Marko Markić",
    "city": "Zagreb",
    "phone": "091/123-4567",
    "status": "preparing",
    "total": 19.80,
    "items": [
      { "pizza": "Capricciosa", "qty": 1, "price": 8.90 },
      { "pizza": "Diavola", "qty": 1, "price": 10.90 }
    ]
  }
}</pre>
    </div>

    <!-- Health / Geo API -->
    <div class="api-card">
      <h2>Pomoćni servisi</h2>
      <p>Provjera rada sustava i dohvat grada / poštanskog broja iz adrese.</p>
      <div class="api-url">GET <?= h($base) ?>/api/health.php</div>
      <pre>{ "ok": true, "time": "2026-01-13 10:12:00" }</pre>

      <p>Novi geo API (Nominatim) - po adresi:</p>
      <div class="api-url">GET <?= h($base) ?>/api/geo.php?address=Palinovečka+ulica+31</div>
      <pre>{
  "city": "Zagreb",
  "postal": "10000"
}</pre>
      <p>Automatski dohvaća grad i poštanski broj iz unesene adrese.</p>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/podnozje.php'; ?>