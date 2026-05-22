document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    const totalEl     = document.querySelector('.total-row span:last-child');
    const finalEl     = document.querySelector('.final-total span:last-child');
    const checkoutBtn = document.querySelector('.checkout-btn');
    const subtitle    = document.querySelector('.cart-subtitle');

    // Immediately hide subtitle if localStorage says logged in (no flicker)
    if (subtitle && isUserLoggedIn()) subtitle.style.display = 'none';

    // Confirm with server — source of truth
    const authData   = await apiGet('api/auth.php', { action: 'status' });
    const loggedIn   = authData.success && authData.data.logged_in;
    if (subtitle) subtitle.style.display = loggedIn ? 'none' : 'block';

    async function loadCart() {
        const data = await apiGet('api/cart.php', { action: 'get' });
        if (!data.success) return;

        const { items, total } = data.data;

        const formatted = Number(total).toLocaleString('ru-RU') + ' ₽';
        if (totalEl) totalEl.textContent = formatted;
        if (finalEl) finalEl.textContent = formatted;

        const cartCard = document.querySelector('.cart-card');
        if (!cartCard) return;

        if (items.length === 0) {
            cartCard.innerHTML = `
                <div style="padding:40px;text-align:center;">
                    <p>Корзина пуста</p>
                    <a href="catalog.html" class="btn" style="margin-top:16px;display:inline-block;">Перейти в каталог</a>
                </div>`;
            if (checkoutBtn) checkoutBtn.style.pointerEvents = 'none';
            return;
        }

        cartCard.innerHTML = items.map(item => {
            const image    = item.image || './public/clava.png';
            const subtotal = Number(item.subtotal).toLocaleString('ru-RU') + ' ₽';
            return `
            <div class="cart-item" data-id="${item.product_id}">
                <div class="cart-item__img">
                    <img src="${image}" alt="${item.name}" onerror="this.src='./public/clava.png'">
                </div>
                <div class="cart-item__info">
                    <div class="cart-item__header">
                        <div>
                            <h3>${item.name}</h3>
                            <p class="item-desc">${item.brand || ''}</p>
                        </div>
                        <span class="item-price">${subtotal}</span>
                    </div>
                    <p class="item-stock">В наличии: <span class="red">${item.stock} шт.</span></p>
                    <div class="item-actions">
                        <div class="quantity-control">
                            <button class="qty-minus">−</button>
                            <input type="text" value="${item.quantity}" readonly>
                            <button class="qty-plus">+</button>
                        </div>
                        <button class="remove-item"><img src="public/adress-x.png" alt="Удалить"></button>
                    </div>
                </div>
            </div>`;
        }).join('<hr style="margin:12px 0;border:none;border-top:1px solid #eee;">');

        cartCard.querySelectorAll('.cart-item').forEach(row => {
            const id       = row.dataset.id;
            const qtyInput = row.querySelector('input');
            const item     = items.find(i => i.product_id == id);

            row.querySelector('.qty-minus').addEventListener('click', async () => {
                const newQty = Math.max(1, parseInt(qtyInput.value) - 1);
                await apiPost('api/cart.php?action=update', { product_id: id, quantity: newQty });
                loadCart();
            });

            row.querySelector('.qty-plus').addEventListener('click', async () => {
                const newQty = Math.min(item.stock, parseInt(qtyInput.value) + 1);
                await apiPost('api/cart.php?action=update', { product_id: id, quantity: newQty });
                loadCart();
            });

            row.querySelector('.remove-item').addEventListener('click', async () => {
                await apiPost('api/cart.php?action=remove', { product_id: id });
                loadCart();
            });
        });
    }

    loadCart();
});
