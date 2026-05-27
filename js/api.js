// вспомогательные функции для fetch-запросов

async function apiPost(endpoint, data) {
    const form = new FormData();
    for (const key in data) {
        form.append(key, data[key]);
    }
    const res = await fetch(endpoint, {
        method: 'POST',
        body: form,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    return res.json();
}

async function apiGet(endpoint, params = {}) {
    const qs = new URLSearchParams(params).toString();
    const url = qs ? `${endpoint}?${qs}` : endpoint;
    const res = await fetch(url, {
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    return res.json();
}

// проверка авторизации из localStorage (быстрая, без запроса)
function isUserLoggedIn() {
    return !!localStorage.getItem('user');
}

// обновление шапки в зависимости от авторизации
async function initAuthHeader() {
    try {
        const data = await apiGet('api/auth.php', { action: 'status' });

        if (data.success && data.data.logged_in) {
            localStorage.setItem('user', JSON.stringify(data.data));

            const user = data.data;

            // меняем ссылки на профиль
            document.querySelectorAll('a[href="/register-modal.html"], a[href="/login-modal.html"]').forEach(link => {
                link.href = '/profile.html';
                const span = link.querySelector('span');
                if (span) span.textContent = user.user_name;
            });

            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.style.display = 'block';
                logoutBtn.addEventListener('click', async () => {
                    await apiGet('api/auth.php', { action: 'logout' });
                    localStorage.removeItem('user');
                    window.location.href = 'index.html';
                });
            }
        } else {
            localStorage.removeItem('user');
        }
    } catch (e) {
        // ошибка соединения — ничего не делаем
    }
}

// показать ошибку в форме
function showFormError(form, message) {
    let err = form.querySelector('.form-error');
    if (!err) {
        err = document.createElement('p');
        err.className = 'form-error';
        form.prepend(err);
    }
    err.textContent = message;
}

// очистить ошибку формы
function clearFormError(form) {
    const err = form.querySelector('.form-error');
    if (err) err.remove();
}
