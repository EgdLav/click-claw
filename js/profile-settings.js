document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    // ── Auth check ────────────────────────────────────────────────────────────
    const authData = await apiGet('api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in) {
        window.location.href = 'login-modal.html';
        return;
    }

    // Logout
    document.querySelectorAll('.profile__logout-btn').forEach(btn => {
        btn.innerHTML = 'Выйти';
        btn.addEventListener('click', async () => {
            await apiGet('api/auth.php', { action: 'logout' });
            window.location.href = 'index.html';
        });
    });

    // ── Load user data ────────────────────────────────────────────────────────
    const userData = await apiGet('api/users.php', { action: 'get' });
    if (!userData.success) return;

    const user = userData.data;

    // Fill welcome heading
    document.querySelectorAll('.profile__welcome h1').forEach(el => {
        el.textContent = `Привет, ${user.name}`;
    });

    // Fill profile info display fields
    const nameDisplays = document.querySelectorAll('.profile-info-name, .user-name-display');
    nameDisplays.forEach(el => el.textContent = user.name);

    const phoneDisplays = document.querySelectorAll('.profile-info-phone, .user-phone-display');
    phoneDisplays.forEach(el => el.textContent = user.phone || 'Не указан');

    const emailDisplays = document.querySelectorAll('.profile-info-email, .user-email-display');
    emailDisplays.forEach(el => el.textContent = user.email);

    // ── Edit profile modal ────────────────────────────────────────────────────
    // Find the edit form (in edit_profile-modal.html structure embedded or linked)
    const editForm = document.querySelector('.auth-form, .edit-profile-form');
    if (editForm) {
        // Pre-fill inputs
        const nameInput  = editForm.querySelectorAll('input[type="text"]')[0];
        const surnameInput = editForm.querySelectorAll('input[type="text"]')[1];
        const phoneInput = editForm.querySelectorAll('input[type="text"]')[2] ||
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

            const result = await apiPost('api/users.php?action=update', {
                name: fullName,
                phone
            });

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
