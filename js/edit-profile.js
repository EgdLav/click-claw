// редактирование профиля

document.addEventListener('DOMContentLoaded', async () => {

    // проверка авторизации
    const authData = await apiGet('api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in) {
        window.location.href = 'login-modal.html';
        return;
    }

    // предзаполнение формы
    const userData = await apiGet('api/users.php', { action: 'get' });
    if (userData.success) {
        const u = userData.data;
        const parts = u.name.split(' ');
        const firstNameEl = document.getElementById('editFirstName');
        const lastNameEl  = document.getElementById('editLastName');
        const phoneEl     = document.getElementById('editPhone');

        if (firstNameEl) firstNameEl.value = parts[0] || '';
        if (lastNameEl)  lastNameEl.value  = parts.slice(1).join(' ') || '';
        if (phoneEl)     phoneEl.value     = u.phone || '';
    }

    // отправка формы
    const form = document.getElementById('editProfileForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const firstName = document.getElementById('editFirstName')?.value.trim() || '';
            const lastName  = document.getElementById('editLastName')?.value.trim()  || '';
            const phone     = document.getElementById('editPhone')?.value.trim()     || '';
            const fullName  = [firstName, lastName].filter(Boolean).join(' ');

            if (!firstName) { alert('Введите имя'); return; }

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const result = await apiPost('api/users.php?action=update', { name: fullName, phone });

            if (result.success) {
                window.location.href = 'profile-settings.html';
            } else {
                alert(result.error || 'Ошибка сохранения');
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }
});
