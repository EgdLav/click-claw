document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    // ── Auth check ────────────────────────────────────────────────────────────
    const authData = await apiGet('api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in) {
        window.location.href = 'login-modal.html';
        return;
    }

    const user = authData.data;

    // Fill name
    document.querySelectorAll('.profile__welcome h1').forEach(el => {
        el.textContent = `Привет, ${user.user_name}`;
    });

    // Logout
    document.querySelectorAll('.profile__logout-btn').forEach(btn => {
        btn.innerHTML = 'Выйти';
        btn.addEventListener('click', async () => {
            await apiGet('api/auth.php', { action: 'logout' });
            window.location.href = 'index.html';
        });
    });

    // ── Status labels ─────────────────────────────────────────────────────────
    const statusLabels = {
        new:        { text: 'Новый',       color: '#2196F3' },
        processing: { text: 'В обработке', color: '#FF9800' },
        completed:  { text: 'Выполнен',    color: '#4CAF50' },
        cancelled:  { text: 'Отменён',     color: '#F44336' },
    };

    // ── Load & render orders ──────────────────────────────────────────────────
    const container  = document.querySelector('.none_orders_block') || document.querySelector('.orders_wrapper .container');
    const searchInput = document.querySelector('.orders_top input');

    let allOrders = [];

    async function loadOrders() {
        const data = await apiGet('api/orders.php', { action: 'list' });
        allOrders = data.success ? data.data : [];
        renderOrders(allOrders);
    }

    function renderOrders(orders) {
        // Replace the empty-state block with a real list
        const wrapper = document.querySelector('.orders_wrapper .container');
        if (!wrapper) return;

        // Remove old order rows if any
        wrapper.querySelectorAll('.order-row').forEach(el => el.remove());
        const emptyBlock = wrapper.querySelector('.none_orders_block');

        if (orders.length === 0) {
            if (emptyBlock) emptyBlock.style.display = 'block';
            return;
        }

        if (emptyBlock) emptyBlock.style.display = 'none';

        orders.forEach(o => {
            const st = statusLabels[o.status] || { text: o.status, color: '#888' };
            const total = Number(o.total).toLocaleString('ru-RU') + ' ₽';
            const date = new Date(o.created_at).toLocaleDateString('ru-RU');

            const row = document.createElement('div');
            row.className = 'order-row';
            row.style.cssText = 'border:1px solid #eee;border-radius:12px;padding:20px;margin-bottom:16px;background:#fff;';
            row.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
                    <div>
                        <strong style="font-size:16px;">Заказ №${o.id}</strong>
                        <span style="color:#888;font-size:13px;margin-left:12px;">${date}</span>
                    </div>
                    <span style="color:${st.color};font-weight:600;font-size:14px;padding:4px 12px;border-radius:20px;border:1px solid ${st.color};">${st.text}</span>
                </div>
                <div style="margin-top:10px;font-size:14px;color:#555;">
                    Товаров: ${o.items_count} &nbsp;·&nbsp; Сумма: <strong>${total}</strong>
                </div>
                <div style="margin-top:6px;font-size:13px;color:#888;">
                    ${o.address ? 'Адрес: ' + o.address : ''}
                </div>`;
            wrapper.appendChild(row);
        });
    }

    // ── Search filter ─────────────────────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            if (!q) {
                renderOrders(allOrders);
                return;
            }
            const filtered = allOrders.filter(o =>
                String(o.id).includes(q) ||
                (o.address || '').toLowerCase().includes(q) ||
                (statusLabels[o.status]?.text || '').toLowerCase().includes(q)
            );
            renderOrders(filtered);
        });
    }

    loadOrders();
});
