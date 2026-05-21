document.addEventListener('DOMContentLoaded', async () => {

    const form       = document.querySelector('.admin__form');
    const catSelect  = form?.querySelector('.admin__form-select');

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
        // Pre-fill form with existing product data
        const prodData = await apiGet('../api/products.php', { action: 'get', id: editId });
        if (prodData.success) {
            const p = prodData.data;
            const inputs = form.querySelectorAll('.admin__form-input');
            // name, price, stock, description order
            if (inputs[0]) inputs[0].value = p.name;
            if (inputs[1]) inputs[1].value = p.price;
            if (inputs[2]) inputs[2].value = p.stock;

            const textarea = form.querySelector('.admin__form-textarea');
            if (textarea) textarea.value = p.description || '';

            if (catSelect) catSelect.value = p.category_id || '';

            // Show current image
            const imgEl = form.querySelector('img');
            if (imgEl && p.image) imgEl.src = '../' + p.image.replace(/^\//, '');

            // Update title
            const titleEl = document.querySelector('.admin__title');
            if (titleEl) titleEl.textContent = 'Редактировать товар';

            const breadEl = document.querySelector('.admin__breadcrumbs');
            if (breadEl) breadEl.lastChild.textContent = ' ' + p.name;
        }
    }

    // ── Form submit ───────────────────────────────────────────────────────────
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const inputs  = form.querySelectorAll('.admin__form-input[type="text"], .admin__form-input[type="number"], .admin__form-input:not([type])');
            const name    = inputs[0]?.value.trim();
            const price   = inputs[1]?.value.trim();
            const stock   = inputs[2]?.value.trim() || '0';
            const desc    = form.querySelector('.admin__form-textarea')?.value.trim() || '';
            const catId   = catSelect?.value;
            // Image: use existing path or a placeholder
            const image   = editId
                ? (form.querySelector('img')?.src.replace(window.location.origin, '').replace('../', '/') || '')
                : '/public/mouse.png';

            if (!name || !price || !catId) {
                alert('Заполните обязательные поля: название, цена, категория');
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const payload = { name, brand: '', description: desc, price, image, category_id: catId, stock };

            let result;
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
                if (submitBtn) submitBtn.disabled = false;
            }
        });

        // Delete button (edit mode only)
        const deleteBtn = form.querySelector('.admin__btn-danger');
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
