// страница заказов

document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    // проверка авторизации
    const authData = await apiGet('api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in) {
        localStorage.removeItem('user');
        window.location.href = 'login-modal.html';
        return;
    }

    const user = authData.data;

    document.querySelectorAll('a[href="/register-modal.html"], a[href="/login-modal.html"]').forEach(link => {
        link.href = '/profile.html';
        const span = link.querySelector('span');
        if (span) span.textContent = user.user_name;
    });

    const pfName = document.getElementById('pfName');
    const pfRole = document.getElementById('pfRole');
    if (pfName) pfName.textContent = user.user_name;
    if (pfRole) pfRole.textContent = user.user_role === 'admin' ? 'Администратор' : 'Покупатель';

    document.getElementById('pfLogout')?.addEventListener('click', async () => {
        await apiGet('api/auth.php', { action: 'logout' });
        localStorage.removeItem('user');
        window.location.href = 'index.html';
    });

    const statusLabels = {
        new:        { text: 'Новый',       cls: 'status--new' },
        processing: { text: 'В обработке', cls: 'status--processing' },
        completed:  { text: 'Выполнен',    cls: 'status--completed' },
        cancelled:  { text: 'Отменён',     cls: 'status--cancelled' },
    };

    const listEl      = document.getElementById('ordList');
    const searchInput = document.getElementById('ordSearch');
    let allOrders     = [];

    async function loadOrders() {
        const data = await apiGet('api/orders.php', { action: 'list' });
        allOrders  = data.success ? data.data : [];
        renderOrders(allOrders);
    }

    function renderOrders(orders) {
        if (!listEl) return;

        if (orders.length === 0) {
            listEl.innerHTML = `
                <div class="pf__empty">
                    <p>Заказов пока нет</p>
                    <a href="/catalog.html" class="btn" style="margin-top:16px;display:inline-block;">Перейти в каталог</a>
                </div>`;
            return;
        }

        listEl.innerHTML = orders.map(o => {
            const st    = statusLabels[o.status] || { text: o.status, cls: '' };
            const total = Number(o.total).toLocaleString('ru-RU') + ' ₽';
            const date  = new Date(o.created_at).toLocaleDateString('ru-RU', {
                day: 'numeric', month: 'long', year: 'numeric'
            });
            const addr = o.address ? `<p class="ord__card-addr">${o.address}</p>` : '';
            return `
            <div class="pf__order-card ord__card">
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

    searchInput?.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        if (!q) { renderOrders(allOrders); return; }
        renderOrders(allOrders.filter(o =>
            String(o.id).includes(q) ||
            (o.address || '').toLowerCase().includes(q) ||
            (statusLabels[o.status]?.text || '').toLowerCase().includes(q)
        ));
    });

    loadOrders();
});
