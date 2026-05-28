// страница товара

document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    const params    = new URLSearchParams(window.location.search);
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
        const images = (p.images && p.images.length > 0) ? p.images : [p.image || './public/clava.png'];
        const price  = Number(p.price).toLocaleString('ru-RU') + ' ₽';

        document.title = p.name;

        // обновляем хлебную крошку
        const bc = document.getElementById('breadcrumbProduct');
        if (bc) bc.textContent = p.name;

        // галерея
        const mainImg    = document.querySelector('.main-image img');
        const thumbsWrap = document.querySelector('.thumbnails');

        if (mainImg) {
            mainImg.src = images[0];
            mainImg.alt = p.name;
        }

        if (thumbsWrap) {
            thumbsWrap.innerHTML = images.map((src, i) => `
                <img src="${src}" alt="${p.name}" class="${i === 0 ? 'active' : ''}"
                     onerror="this.src='./public/clava.png'">
            `).join('');

            thumbsWrap.querySelectorAll('img').forEach(thumb => {
                thumb.addEventListener('click', () => {
                    if (mainImg) mainImg.src = thumb.src;
                    thumbsWrap.querySelectorAll('img').forEach(t => t.classList.remove('active'));
                    thumb.classList.add('active');
                });
            });
        }

        const titleEl = document.querySelector('.product-title-3');
        if (titleEl) titleEl.textContent = p.name;

        const priceEl = document.querySelector('.product-price-3');
        if (priceEl) priceEl.textContent = price;

        const stockEl = document.querySelector('.stock-status .count');
        if (stockEl) stockEl.textContent = `${p.stock} шт.`;

        document.querySelectorAll('.product-short-desc').forEach(el => el.textContent = p.description || '');

        // управление количеством
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

        // добавить в корзину
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

        // список желаний
        const wishBtn = document.querySelector('.wishlist-toggle');
        if (wishBtn) {
            const checkData = await apiGet('api/wishlist.php', { action: 'check', product_id: productId });
            let inWishlist = checkData.success && checkData.data.in_wishlist;
            updateWishBtn(wishBtn, inWishlist);

            wishBtn.addEventListener('click', async () => {
                const action = inWishlist ? 'remove' : 'add';
                const result = await apiPost(`api/wishlist.php?action=${action}`, { product_id: productId });
                if (result.success) {
                    inWishlist = !inWishlist;
                    updateWishBtn(wishBtn, inWishlist);
                } else {
                    window.location.href = 'login-modal.html';
                }
            });
        }

    } catch (e) {
        console.error(e);
    }
});

function updateWishBtn(btn, inWishlist) {
    btn.textContent = inWishlist ? '♥ В желаниях' : '♡ В желания';
    btn.classList.toggle('wishlist-toggle--active', inWishlist);
}
