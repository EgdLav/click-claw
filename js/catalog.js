document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    const grid       = document.querySelector('.products-grid');
    const countEl    = document.querySelector('.catalog-text p');
    const titleEl    = document.querySelector('.catalog-text .title');
    const sortSelect = document.getElementById('sortSelect');
    const priceMinInput = document.getElementById('priceMin');
    const priceMaxInput = document.getElementById('priceMax');
    const resetBtn   = document.querySelector('.reset-filters');
    const brandList  = document.querySelector('.brand-list');
    const filtersBlock = document.querySelector('.filters-block');
    const toggleFiltersBtn = document.querySelector('.btn_filter');

    // ── Mobile filter sidebar toggle ──────────────────────────────────────────
    if (toggleFiltersBtn && filtersBlock) {
        toggleFiltersBtn.addEventListener('click', () => {
            filtersBlock.classList.toggle('active');
        });
    }
    const closeFiltersBtn = document.getElementById('closeFilters');
    if (closeFiltersBtn && filtersBlock) {
        closeFiltersBtn.addEventListener('click', () => {
            filtersBlock.classList.remove('active');
        });
    }

    // ── Load brands from DB and build checkboxes ──────────────────────────────
    async function loadBrands() {
        if (!brandList) return;
        try {
            const data = await apiGet('api/products.php', { action: 'list' });
            if (!data.success) return;
            // Collect unique brands
            const brands = [...new Set(
                data.data.map(p => p.brand).filter(Boolean).sort()
            )];
            brandList.innerHTML = brands.map(b => `
                <li><label><input type="checkbox" value="${b}"> ${b}</label></li>
            `).join('');
            // Re-attach listeners after rebuild
            brandList.querySelectorAll('input').forEach(cb => {
                cb.addEventListener('change', loadProducts);
            });
        } catch (e) { /* silently skip */ }
    }

    // ── Build filter params ───────────────────────────────────────────────────
    function getFilters() {
        const params = { action: 'list' };

        const minEl = document.getElementById('priceMin');
        const maxEl = document.getElementById('priceMax');

        const minVal = minEl ? minEl.value.trim() : '';
        const maxVal = maxEl ? maxEl.value.trim() : '';

        if (minVal !== '' && Number(minVal) > 0) params.price_min = minVal;
        if (maxVal !== '' && Number(maxVal) > 0) params.price_max = maxVal;

        // All checked brands → send as comma-separated; API handles one brand
        // We'll filter client-side for multi-brand since API supports single brand
        const checkedBrands = [...document.querySelectorAll('.brand-list input:checked')]
            .map(cb => cb.value);
        if (checkedBrands.length === 1) params.brand = checkedBrands[0];
        // multi-brand stored separately for client-side post-filter
        params._brands = checkedBrands;

        const sortVal = sortSelect?.value;
        if (sortVal) params.sort = sortVal;

        // Category from URL ?category_id=
        const urlParams = new URLSearchParams(window.location.search);
        const catId = urlParams.get('category_id');
        if (catId) params.category_id = catId;

        // Search query from URL ?search=
        const searchQ = urlParams.get('search');
        if (searchQ) params.search = searchQ;

        return params;
    }

    // ── Render one product card ───────────────────────────────────────────────
    function renderCard(p) {
        const image = p.image || 'public/clava.png';
        const badge = p.badge ? `<div class="product-badge">${p.badge}</div>` : '';
        const price = Number(p.price).toLocaleString('ru-RU') + ' ₽';
        return `
        <div class="product-card">
            ${badge}
            <div class="product-image">
                <a href="item.html?id=${p.id}">
                    <img src="${image}" alt="${p.name}" onerror="this.src='public/clava.png'">
                </a>
            </div>
            <div class="product-info">
                <p class="product-brand">${p.brand || ''}</p>
                <h3 class="product-name">${p.name}</h3>
                <p class="product-price">${price}</p>
            </div>
            <button class="add-to-cart btn" data-id="${p.id}">
                <span>В корзину</span>
                <img src="public/bask.svg" alt="" class="cart-icon">
            </button>
        </div>`;
    }

    // ── Load & render products ────────────────────────────────────────────────
    async function loadProducts() {
        grid.innerHTML = '<p style="padding:20px;color:#888">Загрузка...</p>';

        const filters = getFilters();
        const multiBrands = filters._brands;
        delete filters._brands; // don't send to API

        try {
            const data = await apiGet('api/products.php', filters);
            if (!data.success) {
                grid.innerHTML = '<p style="padding:20px">Ошибка загрузки товаров</p>';
                return;
            }

            let products = data.data;

            // Client-side multi-brand filter (API only supports single brand)
            if (multiBrands.length > 1) {
                products = products.filter(p => multiBrands.includes(p.brand));
            }

            // Update count and title
            if (countEl) countEl.textContent = `Найдено товаров: ${products.length}`;

            // Update page title based on active category filter
            if (titleEl) {
                const urlParams = new URLSearchParams(window.location.search);
                const catName = urlParams.get('category_name');
                titleEl.textContent = catName ? decodeURIComponent(catName) : 'Все товары';
            }

            if (products.length === 0) {
                grid.innerHTML = '<p style="padding:20px;color:#888">Товары не найдены. <button class="reset-filters-inline" style="background:none;border:none;color:#000;text-decoration:underline;cursor:pointer;">Сбросить фильтры</button></p>';
                grid.querySelector('.reset-filters-inline')?.addEventListener('click', resetFilters);
                return;
            }

            grid.innerHTML = products.map(renderCard).join('');

            // Wire up "В корзину" buttons
            grid.querySelectorAll('.add-to-cart').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const productId = btn.dataset.id;
                    const spanEl = btn.querySelector('span');
                    btn.disabled = true;

                    const result = await apiPost('api/cart.php?action=add', {
                        product_id: productId,
                        quantity: 1
                    });

                    if (result.success) {
                        spanEl.textContent = 'Добавлено ✓';
                        setTimeout(() => {
                            spanEl.textContent = 'В корзину';
                            btn.disabled = false;
                        }, 1500);
                    } else {
                        // Not logged in — redirect to login
                        window.location.href = 'login-modal.html';
                    }
                });
            });

        } catch (e) {
            console.error(e);
            grid.innerHTML = '<p style="padding:20px;color:red">Ошибка соединения с сервером</p>';
        }
    }

    // ── Reset all filters ─────────────────────────────────────────────────────
    function resetFilters() {
        const minEl = document.getElementById('priceMin');
        const maxEl = document.getElementById('priceMax');
        if (minEl) minEl.value = '';
        if (maxEl) maxEl.value = '';
        if (sortSelect) sortSelect.value = '';
        document.querySelectorAll('.brand-list input').forEach(cb => cb.checked = false);
        const url = new URL(window.location.href);
        url.searchParams.delete('category_id');
        url.searchParams.delete('category_name');
        window.history.replaceState({}, '', url);
        loadProducts();
    }

    // ── Event listeners ───────────────────────────────────────────────────────
    let debounceTimer;
    function debounceLoad() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadProducts, 400);
    }

    document.getElementById('priceMin')?.addEventListener('input',  debounceLoad);
    document.getElementById('priceMin')?.addEventListener('change', loadProducts);
    document.getElementById('priceMax')?.addEventListener('input',  debounceLoad);
    document.getElementById('priceMax')?.addEventListener('change', loadProducts);

    if (sortSelect) sortSelect.addEventListener('change', loadProducts);
    if (resetBtn)   resetBtn.addEventListener('click', resetFilters);

    // ── Init ──────────────────────────────────────────────────────────────────
    await loadBrands();  // build brand checkboxes from real data first
    loadProducts();      // then load products
});
