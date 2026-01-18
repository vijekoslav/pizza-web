function addToCart(pizzaId) {
  const qtyInput = document.getElementById(`qty-${pizzaId}`);
  const qty = parseInt(qtyInput.value) || 1;

  const countEl = document.getElementById('cart-count');
  if (countEl) {
    const current = parseInt(countEl.textContent) || 0;
    countEl.textContent = current + qty;
  }

  fetch('/pizza-web/kosarica.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `add=1&pizza_id=${pizzaId}&qty=${qty}`,
  }).catch((err) => console.error('Cart add failed', err));
}
