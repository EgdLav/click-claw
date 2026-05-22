document.addEventListener('DOMContentLoaded', async () => {

    // ── Auth check ────────────────────────────────────────────────────────────
    const authData = await apiGet('api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in) {
        localStorage.removeItem('user');
        window.location.href = 'login-modal.html';
        return;
    }

    const user = authData.data;

    // Update header link
    document.querySelectorAll('a[href="/register-modal.html"], a[href="/login-modal.html"]').forEach(link => {
        link.href = '/profile.html';
        const span = link.querySelector('span');
        if (span) span.textContent = user.user_name;
    });

    // ── Load full user info ───────────────────────────────────────────────────
    const userData = await apiGet('api/users.php', { action: 'get' });
    if (userData.success) {
        const u = userData.data;
        const el = id => document.getElementById(id);
        if (el('pfName'))  el('pfName').textContent  = u.name  || '—';
        if (el('pfEmail')) el('pfEmail').textContent = u.email || '—';
        if (el('pfPhone')) el('pfPhone').textContent = u.phone || 'Не указан';
        if (el('pfRole'))  el('pfRole').textContent  = u.role === 'admin' ? 'Администратор' : 'Покупатель';
        if (el('pfDate'))  el('pfDate').textContent  = u.created_at
            ? new Date(u.created_at).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
            : '—';
    }

    // ── Logout ────────────────────────────────────────────────────────────────
    document.getElementById('pfLogout')?.addEventListener('click', async () => {
        await apiGet('api/auth.php', { action: 'logout' });
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    });

    // ── Orders ────────────────────────────────────────────────────────────────
    const statusLabels = {
        new:        { text: 'Новый',       cls: 'status--new' },
        processing: { text: 'В обработке', cls: 'status--processing' },
        completed:  { text: 'Выполнен',    cls: 'status--completed' },
        cancelled:  { text: 'Отменён',     cls: 'status--cancelled' },
    };

    const ordersEl   = document.getElementById('pfOrders');
    const ordersData = await apiGet('api/orders.php', { action: 'list' });
    const orders     = ordersData.success ? ordersData.data : [];

    if (ordersEl) {
        if (orders.length === 0) {
            ordersEl.innerHTML = `
                <div class="pf__empty">
                    <p>У вас пока нет заказов</p>
                    <a href="/catalog.html" class="btn" style="margin-top:16px;display:inline-block;">Перейти в каталог</a>
                </div>`;
        } else {
            ordersEl.innerHTML = orders.map(o => {
                const st    = statusLabels[o.status] || { text: o.status, cls: '' };
                const total = Number(o.total).toLocaleString('ru-RU') + ' ₽';
                const date  = new Date(o.created_at).toLocaleDateString('ru-RU', {
                    day: 'numeric', month: 'long', year: 'numeric'
                });
                const addr = o.address ? `<p class="ord__card-addr">${o.address}</p>` : '';
                return `
                <div class="pf__order-card">
                    <div class="pf__order-top">
                        <div>
                            <span class="pf__order-num">Заказ №${o.id}</span>
                            <span class="pf__order-date">${date}</span>
                        </div>
                        <span class="pf__status ${st.cls}">${st.text}</span>
                    </div>
                    ${addr}
                    <div class="pf__order-bottom">
                        <span>${o.items_count} товар(а)</span>
                        <span class="pf__order-total">${total}</span>
                    </div>
                </div>`;
            }).join('');
        }
    }

    // ── Wishlist ──────────────────────────────────────────────────────────────
    const wishEl   = document.getElementById('pfWishlist');
    const wishData = await apiGet('api/wishlist.php', { action: 'list' });
    const wishItems = wishData.success ? wishData.data : [];

    if (wishEl) {
        if (wishItems.length === 0) {
            wishEl.innerHTML = `
                <div class="pf__empty">
                    <p>Список желаний пуст</p>
                    <a href="/catalog.html" class="pf__view-all" style="margin-top:8px;display:inline-block;">Перейти в каталог</a>
                </div>`;
        } else {
            wishEl.innerHTML = `<div class="pf__wish-grid">` +
                wishItems.map(p => {
                    const price = Number(p.price).toLocaleString('ru-RU') + ' ₽';
                    const image = p.image || 'public/clava.png';
                    return `
                    <div class="pf__wish-item-wrap">
                        <a href="/item.html?id=${p.id}" class="pf__wish-item">
                            <div class="pf__wish-img">
                                <img src="${image}" alt="${p.name}" onerror="this.src='public/clava.png'">
                            </div>
                            <p class="pf__wish-name">${p.name}</p>
                            <p class="pf__wish-price">${price}</p>
                        </a>
                        <div class="pf__wish-actions">
                            <button class="btn pf__wish-cart" data-id="${p.id}">В корзину</button>
                            <button class="pf__wish-remove" data-id="${p.id}" title="Удалить">✕</button>
                        </div>
                    </div>`;
                }).join('') +
                `</div>`;

            // Add to cart
            wishEl.querySelectorAll('.pf__wish-cart').forEach(btn => {
                btn.addEventListener('click', async () => {
                    btn.disabled = true;
                    const result = await apiPost('api/cart.php?action=add', { product_id: btn.dataset.id, quantity: 1 });
                    if (result.success) {
                        btn.textContent = 'Добавлено ✓';
                        setTimeout(() => { btn.textContent = 'В корзину'; btn.disabled = false; }, 1500);
                    } else {
                        btn.disabled = false;
                    }
                });
            });

            // Remove from wishlist
            wishEl.querySelectorAll('.pf__wish-remove').forEach(btn => {
                btn.addEventListener('click', async () => {
                    await apiPost('api/wishlist.php?action=remove', { product_id: btn.dataset.id });
                    btn.closest('.pf__wish-item-wrap').remove();
                    // If grid is now empty
                    if (!wishEl.querySelector('.pf__wish-item-wrap')) {
                        wishEl.innerHTML = `<div class="pf__empty"><p>Список желаний пуст</p></div>`;
                    }
                });
            });
        }
    }
});
