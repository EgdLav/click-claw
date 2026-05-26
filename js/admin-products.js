document.addEventListener('DOMContentLoaded', async () => {

    // ── Active sidebar link ───────────────────────────────────────────────────
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.admin__sidebar-link').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === currentPage) link.classList.add('active');
    });

    const tbody      = document.querySelector('.admin__table tbody');
    const titleEl    = document.querySelector('.admin__table-title');
    const searchInput = document.querySelector('.admin__table-header input');

    let allProducts = [];

    // ── Load products ─────────────────────────────────────────────────────────
    async function loadProducts() {
        const data = await apiGet('../api/products.php', { action: 'list' });
        allProducts = data.success ? data.data : [];
        if (titleEl) titleEl.textContent = `Список товаров (${allProducts.length})`;
        renderProducts(allProducts);
    }

    function renderProducts(products) {
        if (!tbody) return;
        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;color:#888;">Товары не найдены</td></tr>';
            return;
        }
        tbody.innerHTML = products.map(p => {
            const price = Number(p.price).toLocaleString('ru-RU') + ' ₽';
            const img   = p.image || '../public/mouse.png';
            return `<tr>
                <td>${p.id}</td>
                <td><img src="${img}" style="width:40px;height:40px;object-fit:contain;" alt="" onerror="this.src='../public/mouse.png'"></td>
                <td>${p.name}</td>
                <td>${p.category_name || '—'}</td>
                <td>${price}</td>
                <td class="admin__actions">
                    <a href="admin-edit-product.html?id=${p.id}" class="admin__btn admin__btn-sm admin__btn-outline">
                        <img src="../public/edit_profile.png" alt="Ред.">
                    </a>
                    <button class="admin__btn admin__btn-sm admin__btn-danger" data-id="${p.id}">
                        <img src="../public/x-black.svg" alt="Удалить">
                    </button>
                </td>
            </tr>`;
        }).join('');

        // Delete buttons
        tbody.querySelectorAll('[data-id]').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Удалить товар?')) return;
                const result = await apiPost('../api/products.php?action=delete', { id: btn.dataset.id });
                if (result.success) {
                    loadProducts();
                } else {
                    alert(result.error || 'Ошибка удаления');
                }
            });
        });
    }

    // ── Search ────────────────────────────────────────────────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim().toLowerCase();
            if (!q) { renderProducts(allProducts); return; }
            renderProducts(allProducts.filter(p =>
                p.name.toLowerCase().includes(q) ||
                (p.brand || '').toLowerCase().includes(q) ||
                (p.category_name || '').toLowerCase().includes(q)
            ));
        });
    }

    loadProducts();
});
