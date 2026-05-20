/**
 * Shared API helper — all fetch calls go through here
 * Uses relative URLs so it works on any domain/port (OpenServer, localhost, etc.)
 */

async function apiPost(endpoint, data) {
    const form = new FormData();
    for (const key in data) {
        form.append(key, data[key]);
    }
    const res = await fetch(endpoint, { method: 'POST', body: form });
    return res.json();
}

async function apiGet(endpoint, params = {}) {
    const qs = new URLSearchParams(params).toString();
    const url = qs ? `${endpoint}?${qs}` : endpoint;
    const res = await fetch(url);
    return res.json();
}

/**
 * Auth state — check once per page load and update header
 */
async function initAuthHeader() {
    try {
        const data = await apiGet('api/auth.php', { action: 'status' });
        const accountLinks = document.querySelectorAll('a[href="/register-modal.html"], a[href="/login-modal.html"]');

        if (data.success && data.data.logged_in) {
            const user = data.data;
            accountLinks.forEach(link => {
                link.href = 'profile.html';
                const span = link.querySelector('span');
                if (span) span.textContent = user.user_name;
            });

            const logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) {
                logoutBtn.style.display = 'inline-block';
                logoutBtn.addEventListener('click', async () => {
                    await apiGet('api/auth.php', { action: 'logout' });
                    window.location.href = 'index.html';
                });
            }
        }
    } catch (e) {
        // silently fail — not critical
    }
}

/**
 * Show an inline error message inside a form
 */
function showFormError(form, message) {
    let err = form.querySelector('.form-error');
    if (!err) {
        err = document.createElement('p');
        err.className = 'form-error';
        err.style.cssText = 'color:red;margin:8px 0;font-size:14px;';
        form.prepend(err);
    }
    err.textContent = message;
}

function clearFormError(form) {
    const err = form.querySelector('.form-error');
    if (err) err.remove();
}
