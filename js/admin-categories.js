document.addEventListener('DOMContentLoaded', async () => {

    const tbody   = document.querySelector('.admin__table tbody');
    const titleEl = document.querySelector('.admin__table-title');
    const addForm = document.getElementById('addCategoryForm');
    const addInput = addForm?.querySelector('input[type="text"]');
    const addBtn  = addForm?.querySelector('.admin__btn:not(.admin__btn-outline)');

    let allCats = [];

    // ── Load categories ───────────────────────────────────────────────────────
    async function loadCategories() {
        const data = await apiGet('../api/categories.php', { action: 'list' });
        allCats = data.success ? data.data : [];
        if (titleEl) titleEl.textContent = `Список категорий (${allCats.length})`;
        renderCategories(allCats);
    }

    function renderCategories(cats) {
        if (!tbody) return;
        if (cats.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:30px;color:#888;">Категорий нет</td></tr>';
            return;
        }
        tbody.innerHTML = cats.map(c => `
            <tr>
                <td>${c.id}</td>
                <td>
                    <span class="cat-name" data-id="${c.id}">${c.name}</span>
                    <input class="admin__form-input cat-edit-input" data-id="${c.id}"
                        value="${c.name}" style="display:none;max-width:200px;padding:6px 10px;">
                </td>
                <td>${c.product_count}</td>
                <td class="admin__actions">
                    <button class="admin__btn admin__btn-sm admin__btn-outline btn-edit" data-id="${c.id}">
                        <img src="../public/edit_profile.png" alt="Ред.">
                    </button>
                    <button class="admin__btn admin__btn-sm admin__btn-success btn-save" data-id="${c.id}" style="display:none;">
                        Сохранить
                    </button>
                    <button class="admin__btn admin__btn-sm admin__btn-danger btn-delete" data-id="${c.id}">
                        <img src="../public/x-black.svg" alt="Удалить">
                    </button>
                </td>
            </tr>`).join('');

        // Edit toggle
        tbody.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const row = btn.closest('tr');
                row.querySelector('.cat-name').style.display = 'none';
                row.querySelector('.cat-edit-input').style.display = 'inline-block';
                btn.style.display = 'none';
                row.querySelector('.btn-save').style.display = 'inline-flex';
            });
        });

        // Save edit
        tbody.querySelectorAll('.btn-save').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id    = btn.dataset.id;
                const input = tbody.querySelector(`.cat-edit-input[data-id="${id}"]`);
                const name  = input.value.trim();
                if (!name) return;
                btn.disabled = true;
                const result = await apiPost('../api/categories.php?action=update', { id, name });
                if (result.success) {
                    loadCategories();
                } else {
                    alert(result.error || 'Ошибка');
                    btn.disabled = false;
                }
            });
        });

        // Delete
        tbody.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', async () => {
                if (!confirm('Удалить категорию? Товары в ней останутся без категории.')) return;
                const result = await apiPost('../api/categories.php?action=delete', { id: btn.dataset.id });
                if (result.success) {
                    loadCategories();
                } else {
                    alert(result.error || 'Ошибка');
                }
            });
        });
    }

    // ── Add category ──────────────────────────────────────────────────────────
    if (addBtn && addInput) {
        addBtn.addEventListener('click', async () => {
            const name = addInput.value.trim();
            if (!name) { addInput.focus(); return; }
            addBtn.disabled = true;
            const result = await apiPost('../api/categories.php?action=create', { name });
            if (result.success) {
                addInput.value = '';
                if (addForm) addForm.style.display = 'none';
                loadCategories();
            } else {
                alert(result.error || 'Ошибка');
            }
            addBtn.disabled = false;
        });
    }

    loadCategories();
});
