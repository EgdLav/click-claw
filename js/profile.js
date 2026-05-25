document.addEventListener('DOMContentLoaded', async () => {

    // ── Auth check ────────────────────────────────────────────────────────────
    const authData = await apiGet('api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in) {
        localStorage.removeItem('user');
        window.location.href = 'login-modal.html';
        return;
    }

    const user = authData.data;

    // Update header account link
    document.querySelectorAll('a[href="/register-modal.html"], a[href="/login-modal.html"]').forEach(link => {
        link.href = '/profile.html';
        const span = link.querySelector('span');
        if (span) span.textContent = user.user_name;
    });

    // ── Load full user data ───────────────────────────────────────────────────
    let userData = {};
    const userResp = await apiGet('api/users.php', { action: 'get' });
    if (userResp.success) {
        userData = userResp.data;
        const welcome = document.getElementById('pfWelcome');
        if (welcome) welcome.textContent = `Привет, ${userData.name}!`;

        // Info tab
        const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '—'; };
        set('infoName',  userData.name);
        set('infoEmail', userData.email);
        set('infoPhone', userData.phone || 'Не указан');
        set('infoDate',  userData.created_at
            ? new Date(userData.created_at).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
            : '—');
    }

    // ── Logout ────────────────────────────────────────────────────────────────
    document.getElementById('pfLogout')?.addEventListener('click', async () => {
        await apiGet('api/auth.php', { action: 'logout' });
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    });

    // ── Tabs ──────────────────────────────────────────────────────────────────
    const tabs = document.querySelectorAll('.tab-item[data-tab]');
    tabs.forEach(tab => {
        tab.addEventListener('click', e => {
            e.preventDefault();
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.profile__grid[id^="tab-"]').forEach(p => p.style.display = 'none');
            const panel = document.getElementById('tab-' + tab.dataset.tab);
            if (panel) panel.style.display = '';
        });
    });

    // ── Status labels ─────────────────────────────────────────────────────────
    const statusLabels = {
        new:        { text: 'Новый',       cls: 'status--new' },
        processing: { text: 'В обработке', cls: 'status--processing' },
        completed:  { text: 'Выполнен',    cls: 'status--completed' },
        cancelled:  { text: 'Отменён',     cls: 'status--cancelled' },
    };

    // ── Orders ────────────────────────────────────────────────────────────────
    const ordersEl   = document.getElementById('pfOrders');
    const lastOrder  = document.getElementById('pfLastOrder');
    const ordersData = await apiGet('api/orders.php', { action: 'list' });
    const orders     = ordersData.success ? ordersData.data : [];

    // Last order block (top section)
    if (lastOrder && orders.length > 0) {
        const o  = orders[0];
        const st = statusLabels[o.status] || { text: o.status, cls: '' };
        const total = Number(o.total).toLocaleString('ru-RU') + ' ₽';
        const date  = new Date(o.created_at).toLocaleDateString('ru-RU');
        lastOrder.innerHTML = `
            <h3>ВАШ ПОСЛЕДНИЙ ЗАКАЗ</h3>
            <div class="order-empty-card" style="text-align:left;">
                <p><strong>Заказ №${o.id}</strong> от ${date}</p>
                <p>Товаров: ${o.items_count} · Сумма: ${total}</p>
                <p>Статус: <span class="pf__status ${st.cls}" style="display:inline-block;margin-top:4px;">${st.text}</span></p>
            </div>`;
    }

    // Orders tab
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
    const wishEl    = document.getElementById('pfWishlist');
    const wishData  = await apiGet('api/wishlist.php', { action: 'list' });
    const wishItems = wishData.success ? wishData.data : [];

    function renderWishlist() {
        if (!wishEl) return;
        if (wishItems.length === 0) {
            wishEl.innerHTML = `
                <div class="pf__empty">
                    <p>Список желаний пуст</p>
                    <a href="/catalog.html" style="margin-top:8px;display:inline-block;text-decoration:underline;">Перейти в каталог</a>
                </div>`;
            return;
        }
        wishEl.innerHTML = `<div class="pf__wish-grid">` +
            wishItems.map(p => {
                const price = Number(p.price).toLocaleString('ru-RU') + ' ₽';
                const image = p.image || 'public/clava.png';
                return `
                <div class="pf__wish-item-wrap" data-id="${p.id}">
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
            }).join('') + `</div>`;

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

        wishEl.querySelectorAll('.pf__wish-remove').forEach(btn => {
            btn.addEventListener('click', async () => {
                await apiPost('api/wishlist.php?action=remove', { product_id: btn.dataset.id });
                const idx = wishItems.findIndex(p => p.id == btn.dataset.id);
                if (idx !== -1) wishItems.splice(idx, 1);
                renderWishlist();
            });
        });
    }

    renderWishlist();

    // ── Edit profile modal ────────────────────────────────────────────────────
    const modal       = document.getElementById('editProfileModal');
    const closeBtn    = document.getElementById('closeEditModal');
    const editForm    = document.getElementById('editProfileForm');

    function openModal() {
        if (!modal) return;
        // Reset button state in case it was stuck
        const submitBtn = editForm?.querySelector('button[type="submit"]');
        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Сохранить'; }
        // Pre-fill form with current data
        const parts = (userData.name || '').split(' ');
        const fn = document.getElementById('editFirstName');
        const ln = document.getElementById('editLastName');
        const ph = document.getElementById('editPhone');
        if (fn) fn.value = parts[0] || '';
        if (ln) ln.value = parts.slice(1).join(' ') || '';
        if (ph) ph.value = userData.phone || '';
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    document.getElementById('openEditModal')?.addEventListener('click', openModal);
    document.getElementById('openEditModal2')?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    editForm?.addEventListener('submit', async e => {
        e.preventDefault();
        const firstName = document.getElementById('editFirstName')?.value.trim() || '';
        const lastName  = document.getElementById('editLastName')?.value.trim()  || '';
        const phone     = document.getElementById('editPhone')?.value.trim()     || '';
        const fullName  = [firstName, lastName].filter(Boolean).join(' ');

        if (!firstName) { alert('Введите имя'); return; }

        const submitBtn = editForm.querySelector('button[type="submit"]');
        if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Сохраняем...'; }

        try {
            const result = await apiPost('api/users.php?action=update', { name: fullName, phone });

            if (result.success) {
                userData.name  = fullName;
                userData.phone = phone;
                const welcome = document.getElementById('pfWelcome');
                if (welcome) welcome.textContent = `Привет, ${fullName}!`;
                document.querySelectorAll('a[href="/profile.html"] span').forEach(s => s.textContent = fullName);
                const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val || '—'; };
                set('infoName',  fullName);
                set('infoPhone', phone || 'Не указан');
                closeModal();
            } else {
                alert(result.error || 'Ошибка сохранения');
            }
        } catch (err) {
            alert('Ошибка соединения с сервером');
        } finally {
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Сохранить'; }
        }
    });
});
