// каталог товаров

document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    const grid          = document.querySelector('.products-grid');
    const countEl       = document.querySelector('.catalog-text p');
    const titleEl       = document.querySelector('.catalog-text .title');
    const sortSelect    = document.getElementById('sortSelect');
    const brandList     = document.querySelector('.brand-list');
    const filtersBlock  = document.querySelector('.filters-block');
    const toggleFilters = document.querySelector('.btn_filter');
    const resetBtn      = document.querySelector('.reset-filters');

    // открытие/закрытие фильтров на мобильных
    if (toggleFilters && filtersBlock) {
        toggleFilters.addEventListener('click', () => {
            filtersBlock.classList.toggle('active');
        });
    }

    const closeFiltersBtn = document.getElementById('closeFilters');
    if (closeFiltersBtn && filtersBlock) {
        closeFiltersBtn.addEventListener('click', () => {
            filtersBlock.classList.remove('active');
        });
    }

    // загрузка брендов из БД
    async function loadBrands() {
        if (!brandList) return;
        try {
            const data = await apiGet('api/products.php', { action: 'list' });
            if (!data.success) return;
            const brands = [...new Set(data.data.map(p => p.brand).filter(Boolean).sort())];
            brandList.innerHTML = brands.map(b => `
                <li><label><input type="checkbox" value="${b}"> ${b}</label></li>
            `).join('');
            brandList.querySelectorAll('input').forEach(cb => {
                cb.addEventListener('change', loadProducts);
            });
        } catch (e) {}
    }

    // сбор параметров фильтрации
    function getFilters() {
        const params = { action: 'list' };

        const minVal = document.getElementById('priceMin')?.value.trim();
        const maxVal = document.getElementById('priceMax')?.value.trim();

        if (minVal && Number(minVal) > 0) params.price_min = minVal;
        if (maxVal && Number(maxVal) > 0) params.price_max = maxVal;

        const checkedBrands = [...document.querySelectorAll('.brand-list input:checked')].map(cb => cb.value);
        if (checkedBrands.length === 1) params.brand = checkedBrands[0];
        params._brands = checkedBrands;

        if (sortSelect?.value) params.sort = sortSelect.value;

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('category_id')) params.category_id = urlParams.get('category_id');
        if (urlParams.get('search')) params.search = urlParams.get('search');

        return params;
    }

    // рендер карточки товара
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

    // загрузка и отображение товаров
    async function loadProducts() {
        grid.innerHTML = '<p style="padding:20px;color:#888">Загрузка...</p>';

        const filters = getFilters();
        const multiBrands = filters._brands;
        delete filters._brands;

        try {
            const data = await apiGet('api/products.php', filters);
            if (!data.success) {
                grid.innerHTML = '<p style="padding:20px">Ошибка загрузки товаров</p>';
                return;
            }

            let products = data.data;

            // фильтрация по нескольким брендам на клиенте
            if (multiBrands.length > 1) {
                products = products.filter(p => multiBrands.includes(p.brand));
            }

            if (countEl) countEl.textContent = `Найдено товаров: ${products.length}`;

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

            // кнопки "В корзину"
            grid.querySelectorAll('.add-to-cart').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const spanEl = btn.querySelector('span');
                    btn.disabled = true;

                    const result = await apiPost('api/cart.php?action=add', {
                        product_id: btn.dataset.id,
                        quantity: 1
                    });

                    if (result.success) {
                        spanEl.textContent = 'Добавлено ✓';
                        setTimeout(() => {
                            spanEl.textContent = 'В корзину';
                            btn.disabled = false;
                        }, 1500);
                    } else {
                        window.location.href = 'login-modal.html';
                    }
                });
            });

        } catch (e) {
            grid.innerHTML = '<p style="padding:20px;color:red">Ошибка соединения с сервером</p>';
        }
    }

    // сброс фильтров
    function resetFilters() {
        document.getElementById('priceMin') && (document.getElementById('priceMin').value = '');
        document.getElementById('priceMax') && (document.getElementById('priceMax').value = '');
        if (sortSelect) sortSelect.value = '';
        document.querySelectorAll('.brand-list input').forEach(cb => cb.checked = false);
        const url = new URL(window.location.href);
        url.searchParams.delete('category_id');
        url.searchParams.delete('category_name');
        window.history.replaceState({}, '', url);
        loadProducts();
    }

    // дебаунс для полей цены
    let debounceTimer;
    function debounceLoad() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadProducts, 400);
    }

    document.getElementById('priceMin')?.addEventListener('input', debounceLoad);
    document.getElementById('priceMin')?.addEventListener('change', loadProducts);
    document.getElementById('priceMax')?.addEventListener('input', debounceLoad);
    document.getElementById('priceMax')?.addEventListener('change', loadProducts);

    if (sortSelect) sortSelect.addEventListener('change', loadProducts);
    if (resetBtn) resetBtn.addEventListener('click', resetFilters);

    await loadBrands();
    loadProducts();
});
