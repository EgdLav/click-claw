document.addEventListener('DOMContentLoaded', () => {
    initAuthHeader();

    const searchInput  = document.querySelector('.search-field');
    const clearBtn     = document.querySelector('.search-clear-btn');
    const resultsEl    = document.querySelector('.search-results');
    const viewAllLink  = document.querySelector('.search-view-all');

    if (!searchInput || !resultsEl) return;

    // ── Debounced search ──────────────────────────────────────────────────────
    let debounceTimer;

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const q = searchInput.value.trim();

        if (!q) {
            showEmpty();
            if (viewAllLink) viewAllLink.href = 'catalog.html';
            return;
        }

        debounceTimer = setTimeout(() => doSearch(q), 300);
    });

    // Clear button
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            showEmpty();
            searchInput.focus();
        });
    }

    // Enter → go to catalog with search query
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            const q = searchInput.value.trim();
            if (q) window.location.href = `catalog.html?search=${encodeURIComponent(q)}`;
        }
    });

    async function doSearch(q) {
        resultsEl.innerHTML = '<p style="padding:16px;color:#888;">Поиск...</p>';
        if (viewAllLink) viewAllLink.href = `catalog.html?search=${encodeURIComponent(q)}`;

        try {
            const data = await apiGet('api/products.php', { action: 'list', search: q });
            if (!data.success || data.data.length === 0) {
                resultsEl.innerHTML = '<p style="padding:16px;color:#888;">Ничего не найдено</p>';
                return;
            }

            const products = data.data.slice(0, 5); // show max 5 results
            resultsEl.innerHTML = products.map(p => {
                const price = Number(p.price).toLocaleString('ru-RU') + ' ₽';
                const img   = p.image || 'public/mouse.png';
                const stock = p.stock > 0 ? 'В наличии' : 'Нет в наличии';
                return `
                <a href="item.html?id=${p.id}" class="search-item" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f0f0f0;">
                    <div class="search-item-img">
                        <img src="${img}" alt="${p.name}" onerror="this.src='public/mouse.png'">
                    </div>
                    <div class="search-item-info">
                        <h4 class="search-item-title">${p.name}</h4>
                        <p class="search-item-price">${price}</p>
                        <p class="search-item-status" style="color:${p.stock > 0 ? '#4CAF50' : '#e74c3c'};font-size:12px;">${stock}</p>
                    </div>
                </a>`;
            }).join('');

        } catch (e) {
            resultsEl.innerHTML = '<p style="padding:16px;color:red;">Ошибка поиска</p>';
        }
    }

    function showEmpty() {
        resultsEl.innerHTML = '';
    }

    // Auto-focus the search input
    searchInput.focus();
});
