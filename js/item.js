document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    const params = new URLSearchParams(window.location.search);
    const productId = params.get('id');

    if (!productId) {
        document.querySelector('.product-page').innerHTML = '<p>Товар не найден. <a href="catalog.html">Вернуться в каталог</a></p>';
        return;
    }

    try {
        const data = await apiGet('api/products.php', { action: 'get', id: productId });

        if (!data.success) {
            document.querySelector('.product-page').innerHTML = '<p>Товар не найден. <a href="catalog.html">Вернуться в каталог</a></p>';
            return;
        }

        const p = data.data;
        const image = p.image || './public/clava-big.png';
        const price = Number(p.price).toLocaleString('ru-RU') + ' ₽';

        document.title = p.name;

        const mainImg = document.querySelector('.main-image img');
        if (mainImg) { mainImg.src = image; mainImg.alt = p.name; }

        document.querySelectorAll('.thumbnails img').forEach(img => {
            img.src = image; img.alt = p.name;
        });

        const titleEl = document.querySelector('.product-title-3');
        if (titleEl) titleEl.textContent = p.name;

        const priceEl = document.querySelector('.product-price-3');
        if (priceEl) priceEl.textContent = price;

        const stockEl = document.querySelector('.stock-status .count');
        if (stockEl) stockEl.textContent = `${p.stock} шт.`;

        const descEls = document.querySelectorAll('.product-short-desc');
        descEls.forEach(el => el.textContent = p.description || '');

        // Quantity controls
        const qtyInput = document.querySelector('.quantity-control input');
        const minusBtn = document.querySelector('.quantity-control button:first-child');
        const plusBtn  = document.querySelector('.quantity-control button:last-child');

        if (qtyInput && minusBtn && plusBtn) {
            minusBtn.addEventListener('click', () => {
                const val = parseInt(qtyInput.value) || 1;
                if (val > 1) qtyInput.value = val - 1;
            });
            plusBtn.addEventListener('click', () => {
                const val = parseInt(qtyInput.value) || 1;
                if (val < p.stock) qtyInput.value = val + 1;
            });
        }

        // Add to cart
        const addBtn = document.querySelector('.add-to-cart');
        if (addBtn) {
            const innerLink = addBtn.querySelector('a');
            if (innerLink) addBtn.textContent = innerLink.textContent;

            addBtn.addEventListener('click', async () => {
                const qty = parseInt(qtyInput?.value) || 1;
                const result = await apiPost('api/cart.php?action=add', {
                    product_id: productId,
                    quantity: qty
                });

                if (result.success) {
                    addBtn.textContent = 'Добавлено ✓';
                    setTimeout(() => { addBtn.textContent = 'Добавить в корзину'; }, 1500);
                } else {
                    window.location.href = 'login-modal.html';
                }
            });
        }

    } catch (e) {
        console.error(e);
    }
});
