document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.auth-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearFormError(form);

        const inputs   = form.querySelectorAll('input[type="text"]');
        const name     = inputs[0]?.value.trim() || '';
        const surname  = inputs[1]?.value.trim() || '';
        const email    = form.querySelector('input[type="email"]').value.trim();
        const password = form.querySelector('input[type="password"]').value;
        const agree    = form.querySelector('input[type="checkbox"]')?.checked;

        if (!name) { showFormError(form, 'Введите имя'); return; }
        if (!email) { showFormError(form, 'Введите email'); return; }
        if (password.length < 6) { showFormError(form, 'Пароль минимум 6 символов'); return; }
        if (!agree) { showFormError(form, 'Необходимо принять политику конфиденциальности'); return; }

        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Регистрируем...';

        try {
            const data = await apiPost('api/auth.php?action=register', {
                name: surname ? `${name} ${surname}` : name,
                email,
                password,
                confirm_password: password,
                agree: '1'
            });

            if (data.success) {
                // Store login state for client-side checks
                localStorage.setItem('user', JSON.stringify(data.data));
                window.location.href = 'profile.html';
            } else {
                showFormError(form, data.error || 'Ошибка регистрации');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Зарегистрироваться';
            }
        } catch (err) {
            showFormError(form, 'Ошибка соединения с сервером');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Зарегистрироваться';
        }
    });
});
