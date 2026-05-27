// список желаний

document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    // проверка авторизации
    const authData = await apiGet('api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in) {
        window.location.href = 'login-modal.html';
        return;
    }

    const user = authData.data;

    const pfName = document.getElementById('pfName');
    const pfRole = document.getElementById('pfRole');
    if (pfName) pfName.textContent = user.user_name;
    if (pfRole) pfRole.textContent = user.user_role === 'admin' ? 'Администратор' : 'Покупатель';

    document.getElementById('pfLogout')?.addEventListener('click', async () => {
        await apiGet('api/auth.php', { action: 'logout' });
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    });

    document.querySelectorAll('a[href="/register-modal.html"], a[href="/login-modal.html"]').forEach(link => {
        link.href = '/profile.html';
        const span = link.querySelector('span');
        if (span) span.textContent = user.user_name;
    });

    const grid    = document.getElementById('wlGrid');
    const countEl = document.getElementById('wlCount');

    async function loadWishlist() {
        const data = await apiGet('api/wishlist.php', { action: 'list' });
        if (!data.success) {
            grid.innerHTML = '<p style="color:red;padding:20px;">Ошибка загрузки</p>';
            return;
        }

        const items = data.data;
        if (countEl) countEl.textContent = `${items.length} ${plural(items.length, 'товар', 'товара', 'товаров')}`;

        if (items.length === 0) {
            grid.innerHTML = `
                <div class="pf__empty">
                    <p>Список желаний пуст</p>
                    <a href="/catalog.html" class="btn" style="margin-top:16px;display:inline-block;">Перейти в каталог</a>
                </div>`;
            return;
        }

        grid.innerHTML = items.map(p => {
            const price = Number(p.price).toLocaleString('ru-RU') + ' ₽';
            const image = p.image || 'public/clava.png';
            return `
            <div class="wl__card" data-id="${p.id}">
                <a href="/item.html?id=${p.id}" class="wl__card-img">
                    <img src="${image}" alt="${p.name}" onerror="this.src='public/clava.png'">
                </a>
                <div class="wl__card-body">
                    <p class="wl__card-brand">${p.brand || ''}</p>
                    <h3 class="wl__card-name"><a href="/item.html?id=${p.id}">${p.name}</a></h3>
                    <p class="wl__card-cat">${p.category_name || ''}</p>
                    <div class="wl__card-footer">
                        <span class="wl__card-price">${price}</span>
                        <div class="wl__card-actions">
                            <button class="btn wl__add-cart" data-id="${p.id}">В корзину</button>
                            <button class="wl__remove" data-id="${p.id}" title="Удалить из желаний">✕</button>
                        </div>
                    </div>
                </div>
            </div>`;
        }).join('');

        grid.querySelectorAll('.wl__add-cart').forEach(btn => {
            btn.addEventListener('click', async () => {
                btn.disabled = true;
                const result = await apiPost('api/cart.php?action=add', {
                    product_id: btn.dataset.id,
                    quantity: 1
                });
                if (result.success) {
                    btn.textContent = 'Добавлено ✓';
                    setTimeout(() => { btn.textContent = 'В корзину'; btn.disabled = false; }, 1500);
                } else {
                    btn.disabled = false;
                }
            });
        });

        grid.querySelectorAll('.wl__remove').forEach(btn => {
            btn.addEventListener('click', async () => {
                await apiPost('api/wishlist.php?action=remove', { product_id: btn.dataset.id });
                loadWishlist();
            });
        });
    }

    loadWishlist();
});

function plural(n, one, few, many) {
    const mod10 = n % 10, mod100 = n % 100;
    if (mod10 === 1 && mod100 !== 11) return one;
    if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return few;
    return many;
}
