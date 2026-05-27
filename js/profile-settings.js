// настройки профиля

document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    // проверка авторизации
    const authData = await apiGet('api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in) {
        window.location.href = 'login-modal.html';
        return;
    }

    // выход
    document.querySelectorAll('.profile__logout-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            await apiGet('api/auth.php', { action: 'logout' });
            window.location.href = 'index.html';
        });
    });

    // загрузка данных пользователя
    const userData = await apiGet('api/users.php', { action: 'get' });
    if (!userData.success) return;

    const user = userData.data;

    document.querySelectorAll('.profile__welcome h1').forEach(el => {
        el.textContent = `Привет, ${user.name}`;
    });

    document.querySelectorAll('.profile-info-name, .user-name-display').forEach(el => el.textContent = user.name);
    document.querySelectorAll('.profile-info-phone, .user-phone-display').forEach(el => el.textContent = user.phone || 'Не указан');
    document.querySelectorAll('.profile-info-email, .user-email-display').forEach(el => el.textContent = user.email);

    // форма редактирования
    const editForm = document.querySelector('.auth-form, .edit-profile-form');
    if (editForm) {
        const nameInput    = editForm.querySelectorAll('input[type="text"]')[0];
        const surnameInput = editForm.querySelectorAll('input[type="text"]')[1];
        const phoneInput   = editForm.querySelectorAll('input[type="text"]')[2] ||
                             editForm.querySelector('input[placeholder*="телефон"], input[placeholder*="Телефон"]');

        const nameParts = user.name.split(' ');
        if (nameInput)    nameInput.value    = nameParts[0] || '';
        if (surnameInput) surnameInput.value = nameParts.slice(1).join(' ') || '';
        if (phoneInput)   phoneInput.value   = user.phone || '';

        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const firstName = nameInput?.value.trim() || '';
            const lastName  = surnameInput?.value.trim() || '';
            const phone     = phoneInput?.value.trim() || '';
            const fullName  = [firstName, lastName].filter(Boolean).join(' ');

            if (!fullName) { alert('Введите имя'); return; }

            const submitBtn = editForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            const result = await apiPost('api/users.php?action=update', { name: fullName, phone });

            if (result.success) {
                alert('Профиль обновлён');
                window.location.reload();
            } else {
                alert(result.error || 'Ошибка сохранения');
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }
});
