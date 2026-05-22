document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.auth-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearFormError(form);

        const email    = form.querySelector('input[type="email"]').value.trim();
        const password = form.querySelector('input[type="password"]').value;

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Входим...';

        try {
            const data = await apiPost('api/auth.php?action=login', { email, password });

            if (data.success) {
                // Store login state for client-side checks
                localStorage.setItem('user', JSON.stringify(data.data));
                if (data.data.role === 'admin') {
                    window.location.href = 'admin/admin.html';
                } else {
                    window.location.href = 'profile.html';
                }
            } else {
                showFormError(form, data.error || 'Ошибка входа');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Авторизоваться';
            }
        } catch (err) {
            showFormError(form, 'Ошибка соединения с сервером');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Авторизоваться';
        }
    });
});
