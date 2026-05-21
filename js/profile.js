document.addEventListener('DOMContentLoaded', async () => {

    // ── Check auth — redirect to login if not logged in ───────────────────────
    const authData = await apiGet('api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in) {
        window.location.href = 'login-modal.html';
        return;
    }

    const user = authData.data;

    // ── Fill in user name ─────────────────────────────────────────────────────
    document.querySelectorAll('.profile__welcome h1').forEach(el => {
        el.textContent = `Привет, ${user.user_name}`;
    });

    // ── Logout button ─────────────────────────────────────────────────────────
    document.querySelectorAll('.profile__logout-btn').forEach(btn => {
        btn.innerHTML = 'Выйти';
        btn.addEventListener('click', async () => {
            await apiGet('api/auth.php', { action: 'logout' });
            window.location.href = 'index.html';
        });
    });

    // ── Load orders ───────────────────────────────────────────────────────────
    const statusLabels = {
        new:        { text: 'Новый',      color: '#2196F3' },
        processing: { text: 'В обработке', color: '#FF9800' },
        completed:  { text: 'Выполнен',   color: '#4CAF50' },
        cancelled:  { text: 'Отменён',    color: '#F44336' },
    };

    const ordersData = await apiGet('api/orders.php', { action: 'list' });
    const orders = ordersData.success ? ordersData.data : [];

    // Last order block
    const lastOrderBlock = document.querySelector('.profile__last-order');
    if (lastOrderBlock) {
        if (orders.length > 0) {
            const o = orders[0];
            const st = statusLabels[o.status] || { text: o.status, color: '#888' };
            const total = Number(o.total).toLocaleString('ru-RU') + ' ₽';
            const date = new Date(o.created_at).toLocaleDateString('ru-RU');
            lastOrderBlock.innerHTML = `
                <h3>ВАШ ПОСЛЕДНИЙ ЗАКАЗ</h3>
                <div class="order-empty-card" style="text-align:left;">
                    <p><strong>Заказ №${o.id}</strong> от ${date}</p>
                    <p>Товаров: ${o.items_count} · Сумма: ${total}</p>
                    <p>Статус: <span style="color:${st.color};font-weight:600">${st.text}</span></p>
                    <a href="orders.html" class="order-search-link" style="margin-top:12px;display:inline-flex;">
                        МОИ ЗАКАЗЫ <img src="public/search.png" alt="">
                    </a>
                </div>`;
        }
    }

    // Orders grid card
    const ordersCard = document.querySelector('.profile__grid .grid-card:first-child .grid-card__content');
    if (ordersCard) {
        if (orders.length === 0) {
            ordersCard.innerHTML = `
                <div class="empty-state-icon"><img src="public/orders.png" alt=""></div>
                <p>Нет заказов для отображения.</p>`;
        } else {
            ordersCard.innerHTML = orders.slice(0, 3).map(o => {
                const st = statusLabels[o.status] || { text: o.status, color: '#888' };
                const total = Number(o.total).toLocaleString('ru-RU') + ' ₽';
                const date = new Date(o.created_at).toLocaleDateString('ru-RU');
                return `
                <div style="padding:10px 0;border-bottom:1px solid #eee;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <span><strong>№${o.id}</strong> · ${date}</span>
                        <span style="color:${st.color};font-size:13px;font-weight:600">${st.text}</span>
                    </div>
                    <div style="font-size:13px;color:#888;margin-top:4px;">${o.items_count} товар(а) · ${total}</div>
                </div>`;
            }).join('') + `<a href="orders.html" style="display:block;margin-top:12px;font-size:13px;color:#000;text-decoration:underline;">Все заказы →</a>`;
        }
    }
});
