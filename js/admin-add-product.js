document.addEventListener('DOMContentLoaded', async () => {

    const form      = document.querySelector('.admin__form');
    const catSelect = form?.querySelector('select[name="category_id"]');

    // ── Active sidebar link ───────────────────────────────────────────────────
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.admin__sidebar-link').forEach(link => {
        link.classList.remove('active');
        const href = link.getAttribute('href');
        if (href && href.includes(currentPage)) {
            link.classList.add('active');
        }
    });
    // Fallback: mark Товары active on add/edit pages
    if (currentPage === 'admin-add-product.html' || currentPage === 'admin-edit-product.html') {
        document.querySelectorAll('.admin__sidebar-link').forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === 'admin-products.html') link.classList.add('active');
        });
    }

    // ── Populate category dropdown from DB ────────────────────────────────────
    if (catSelect) {
        const data = await apiGet('../api/categories.php', { action: 'list' });
        if (data.success) {
            catSelect.innerHTML = '<option value="">Выберите категорию</option>' +
                data.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        }
    }

    // ── Check if editing (URL has ?id=) ───────────────────────────────────────
    const urlParams = new URLSearchParams(window.location.search);
    const editId    = urlParams.get('id');

    if (editId) {
        const prodData = await apiGet('../api/products.php', { action: 'get', id: editId });
        if (prodData.success) {
            const p = prodData.data;

            const nameEl  = form.querySelector('[name="name"]');
            const brandEl = form.querySelector('[name="brand"]');
            const priceEl = form.querySelector('[name="price"]');
            const stockEl = form.querySelector('[name="stock"]');
            const badgeEl = form.querySelector('[name="badge"]');
            const descEl  = form.querySelector('[name="description"]');

            if (nameEl)  nameEl.value  = p.name        || '';
            if (brandEl) brandEl.value = p.brand       || '';
            if (priceEl) priceEl.value = p.price       || '';
            if (stockEl) stockEl.value = p.stock       || '0';
            if (badgeEl) badgeEl.value = p.badge       || '';
            if (descEl)  descEl.value  = p.description || '';
            if (catSelect) catSelect.value = p.category_id || '';

            // Show current images
            const images = p.images && p.images.length > 0 ? p.images : (p.image ? [p.image] : []);
            renderImagePreviews(images);

            const titleEl = document.querySelector('.admin__title');
            if (titleEl) titleEl.textContent = 'Редактировать товар';
        }
    }

    // ── Image preview management ──────────────────────────────────────────────
    let currentImages = []; // paths already in DB

    function renderImagePreviews(images) {
        currentImages = images.slice();
        const container = document.getElementById('imagePreviewContainer');
        if (!container) return;
        container.innerHTML = currentImages.map((src, i) => `
            <div class="img-preview-item" style="position:relative;display:inline-block;margin:4px;">
                <img src="${src.startsWith('/') ? '..' + src : src}"
                     style="width:80px;height:80px;object-fit:contain;border:1px solid #ddd;border-radius:6px;"
                     onerror="this.src='../public/clava.png'">
                <button type="button" onclick="removePreviewImage(${i})"
                    style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;
                           background:#e74c3c;color:#fff;border:none;cursor:pointer;font-size:12px;
                           display:flex;align-items:center;justify-content:center;line-height:1;">✕</button>
            </div>`).join('');
    }

    window.removePreviewImage = function(index) {
        currentImages.splice(index, 1);
        renderImagePreviews(currentImages);
    };

    // Handle new image path input
    const addImageBtn = document.getElementById('addImageBtn');
    const newImageInput = document.getElementById('newImagePath');
    if (addImageBtn && newImageInput) {
        addImageBtn.addEventListener('click', () => {
            const val = newImageInput.value.trim();
            if (!val) return;
            currentImages.push(val.startsWith('/') ? val : '/' + val);
            renderImagePreviews(currentImages);
            newImageInput.value = '';
        });
    }

    // ── Form submit ───────────────────────────────────────────────────────────
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const name  = form.querySelector('[name="name"]')?.value.trim()        || '';
            const brand = form.querySelector('[name="brand"]')?.value.trim()       || '';
            const price = form.querySelector('[name="price"]')?.value.trim()       || '';
            const stock = form.querySelector('[name="stock"]')?.value.trim()       || '0';
            const badge = form.querySelector('[name="badge"]')?.value.trim()       || '';
            const desc  = form.querySelector('[name="description"]')?.value.trim() || '';
            const catId = catSelect?.value || '';

            if (!name || !price || !catId) {
                alert('Заполните обязательные поля: название, цена, категория');
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Сохраняем...'; }

            // Images as comma-separated string
            const imagesStr = currentImages.join(',');

            const payload = { name, brand, description: desc, price, category_id: catId, stock, badge, images: imagesStr };

            let result;
            try {
                if (editId) {
                    payload.id = editId;
                    result = await apiPost('../api/products.php?action=update', payload);
                } else {
                    result = await apiPost('../api/products.php?action=create', payload);
                }

                if (result.success) {
                    window.location.href = 'admin-products.html';
                } else {
                    alert(result.error || 'Ошибка сохранения');
                }
            } catch (err) {
                alert('Ошибка соединения с сервером');
            } finally {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = editId ? 'Сохранить изменения' : 'Добавить товар'; }
            }
        });

        // Delete button (edit mode only)
        const deleteBtn = form.querySelector('.admin__btn-danger[data-action="delete"]');
        if (deleteBtn && editId) {
            deleteBtn.addEventListener('click', async () => {
                if (!confirm('Удалить товар навсегда?')) return;
                const result = await apiPost('../api/products.php?action=delete', { id: editId });
                if (result.success) {
                    window.location.href = 'admin-products.html';
                } else {
                    alert(result.error || 'Ошибка');
                }
            });
        }
    }
});
