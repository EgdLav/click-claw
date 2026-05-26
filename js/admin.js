/**
 * Admin panel — shared JS for all admin pages
 * Handles auth check, stats, logout, active sidebar
 */
document.addEventListener('DOMContentLoaded', async () => {

    // ── Active sidebar link ───────────────────────────────────────────────────
    const currentPage = window.location.pathname.split('/').pop();
    const pageToLink = {
        'admin.html':              'admin.html',
        'admin-products.html':     'admin-products.html',
        'admin-add-product.html':  'admin-products.html',
        'admin-edit-product.html': 'admin-products.html',
        'admin-categories.html':   'admin-categories.html',
        'admin-orders.html':       'admin-orders.html',
    };
    const activeHref = pageToLink[currentPage] || 'admin.html';
    document.querySelectorAll('.admin__sidebar-link').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === activeHref) link.classList.add('active');
    });

    // ── Auth: must be admin ───────────────────────────────────────────────────
    const authData = await apiGet('../api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in || authData.data.user_role !== 'admin') {
        window.location.href = '../login-modal.html';
        return;
    }

    // ── Logout link ───────────────────────────────────────────────────────────
    document.querySelectorAll('.admin__sidebar-link.logout').forEach(link => {
        link.addEventListener('click', async (e) => {
            e.preventDefault();
            await apiGet('../api/auth.php', { action: 'logout' });
            window.location.href = '../index.html';
        });
    });

    // ── Load stats on dashboard ───────────────────────────────────────────────
    const statValues = document.querySelectorAll('.admin__stat-card-value');
    if (statValues.length >= 3) {
        try {
            const [prodData, catData, orderData] = await Promise.all([
                apiGet('../api/products.php',   { action: 'list' }),
                apiGet('../api/categories.php', { action: 'list' }),
                apiGet('../api/orders.php',     { action: 'all' }),
            ]);

            const products = prodData.success  ? prodData.data  : [];
            const cats     = catData.success   ? catData.data   : [];
            const orders   = orderData.success ? orderData.data : [];

            // Stat cards: товары, категории, заказы, выручка
            if (statValues[0]) statValues[0].textContent = products.length;
            if (statValues[1]) statValues[1].textContent = cats.length;
            if (statValues[2]) statValues[2].textContent = orders.length;
            if (statValues[3]) {
                const revenue = orders
                    .filter(o => o.status !== 'cancelled')
                    .reduce((sum, o) => sum + Number(o.total), 0);
                statValues[3].textContent = revenue.toLocaleString('ru-RU') + ' ₽';
            }

            // Recent orders table on dashboard
            renderRecentOrders(orders.slice(0, 5));
        } catch (e) {
            console.error('Stats load error', e);
        }
    }

    // ── Recent orders table (dashboard only) ─────────────────────────────────
    function renderRecentOrders(orders) {
        const tbody = document.querySelector('.admin__table tbody');
        if (!tbody) return;

        const statusMap = {
            new:        { label: 'Новый',       cls: 'admin__badge--new' },
            processing: { label: 'В обработке', cls: 'admin__badge--processing' },
            completed:  { label: 'Выполнен',    cls: 'admin__badge--completed' },
            cancelled:  { label: 'Отменён',     cls: 'admin__badge--cancelled' },
        };

        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;padding:30px;">Заказов пока нет</td></tr>';
            return;
        }

        tbody.innerHTML = orders.map(o => {
            const st    = statusMap[o.status] || { label: o.status, cls: '' };
            const total = Number(o.total).toLocaleString('ru-RU') + ' ₽';
            const date  = new Date(o.created_at).toLocaleDateString('ru-RU');
            const actions = (o.status === 'new') ? `
                <button class="admin__btn admin__btn-sm admin__btn-success" data-id="${o.id}" data-status="processing">Принять</button>
                <button class="admin__btn admin__btn-sm admin__btn-danger"  data-id="${o.id}" data-status="cancelled">Отклонить</button>
            ` : (o.status === 'processing') ? `
                <button class="admin__btn admin__btn-sm admin__btn-success" data-id="${o.id}" data-status="completed">Завершить</button>
            ` : `<span style="color:#888;font-size:13px;">${st.label}</span>`;

            return `<tr>
                <td>#${o.id}</td>
                <td>${o.user_name || o.name}</td>
                <td>${total}</td>
                <td><span class="admin__badge ${st.cls}">${st.label}</span></td>
                <td>${date}</td>
                <td class="admin__actions">${actions}</td>
            </tr>`;
        }).join('');

        // Wire status buttons
        tbody.querySelectorAll('[data-status]').forEach(btn => {
            btn.addEventListener('click', () => updateOrderStatus(btn.dataset.id, btn.dataset.status, btn));
        });
    }

    // ── Update order status ───────────────────────────────────────────────────
    window.updateOrderStatus = async function(id, status, btn) {
        if (btn) btn.disabled = true;
        const result = await apiPost('../api/orders.php?action=update_status', { id, status });
        if (result.success) {
            // Reload the page section
            location.reload();
        } else {
            alert(result.error || 'Ошибка');
            if (btn) btn.disabled = false;
        }
    };
});
