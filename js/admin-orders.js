// управление заказами в админке

document.addEventListener('DOMContentLoaded', async () => {

    const tbody     = document.querySelector('.admin__table tbody');
    const titleEl   = document.querySelector('.admin__table-title');
    const filterSel = document.querySelector('.admin__form-select');

    const statusMap = {
        new:        { label: 'Новый',       cls: 'admin__badge--new' },
        processing: { label: 'В обработке', cls: 'admin__badge--processing' },
        completed:  { label: 'Выполнен',    cls: 'admin__badge--completed' },
        cancelled:  { label: 'Отменён',     cls: 'admin__badge--cancelled' },
    };

    let allOrders = [];

    async function loadOrders() {
        const data = await apiGet('../api/orders.php', { action: 'all' });
        allOrders = data.success ? data.data : [];
        if (titleEl) titleEl.textContent = `Список заказов (${allOrders.length})`;
        renderOrders(allOrders);
    }

    function renderOrders(orders) {
        if (!tbody) return;
        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:#888;">Заказов нет</td></tr>';
            return;
        }

        tbody.innerHTML = orders.map(o => {
            const st    = statusMap[o.status] || { label: o.status, cls: '' };
            const total = Number(o.total).toLocaleString('ru-RU') + ' ₽';
            const date  = new Date(o.created_at).toLocaleDateString('ru-RU');
            let actions = '';
            if (o.status === 'new') {
                actions = `
                    <button class="admin__btn admin__btn-sm admin__btn-success" data-id="${o.id}" data-status="processing">Принять</button>
                    <button class="admin__btn admin__btn-sm admin__btn-danger"  data-id="${o.id}" data-status="cancelled">Отклонить</button>`;
            } else if (o.status === 'processing') {
                actions = `<button class="admin__btn admin__btn-sm admin__btn-success" data-id="${o.id}" data-status="completed">Завершить</button>`;
            } else {
                actions = `<span style="color:#888;font-size:13px;">${st.label}</span>`;
            }

            return `<tr>
                <td>#${o.id}</td>
                <td>
                    <strong>${o.user_name || o.name}</strong><br>
                    <small style="color:#888;">${o.phone}</small>
                </td>
                <td>${o.items_count} шт.</td>
                <td>${total}</td>
                <td><span class="admin__badge ${st.cls}">${st.label}</span></td>
                <td>${date}</td>
                <td class="admin__actions">${actions}</td>
            </tr>`;
        }).join('');

        tbody.querySelectorAll('[data-status]').forEach(btn => {
            btn.addEventListener('click', async () => {
                btn.disabled = true;
                const result = await apiPost('../api/orders.php?action=update_status', {
                    id: btn.dataset.id,
                    status: btn.dataset.status
                });
                if (result.success) {
                    loadOrders();
                } else {
                    alert(result.error || 'Ошибка');
                    btn.disabled = false;
                }
            });
        });
    }

    // фильтр по статусу
    if (filterSel) {
        filterSel.addEventListener('change', () => {
            const statusByLabel = {
                'Новые': 'new', 'В обработке': 'processing',
                'Выполненные': 'completed', 'Отменённые': 'cancelled'
            };
            const statusKey = statusByLabel[filterSel.value];
            if (!statusKey) { renderOrders(allOrders); return; }
            renderOrders(allOrders.filter(o => o.status === statusKey));
        });
    }

    loadOrders();
});
