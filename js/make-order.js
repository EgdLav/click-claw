document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    // ── Auth check ────────────────────────────────────────────────────────────
    const authData = await apiGet('api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in) {
        window.location.href = 'login-modal.html';
        return;
    }

    const subtitle = document.querySelector('.cart-subtitle');
    if (subtitle) subtitle.style.display = 'none';

    // ── Load cart ─────────────────────────────────────────────────────────────
    const cartData = await apiGet('api/cart.php', { action: 'get' });
    if (!cartData.success || cartData.data.items.length === 0) {
        window.location.href = 'cart.html';
        return;
    }

    const { items, total } = cartData.data;

    // Render cart items
    const cartCard = document.querySelector('.cart-card');
    if (cartCard) {
        cartCard.innerHTML = items.map(item => {
            const image    = item.image || 'public/clava.png';
            const subtotal = Number(item.subtotal).toLocaleString('ru-RU') + ' ₽';
            return `
            <div class="cart-item make-order_item-header">
                <div class="cart-item__img">
                    <img src="${image}" alt="${item.name}" onerror="this.src='public/clava.png'">
                </div>
                <div class="cart-item__info">
                    <div class="cart-item__header make-order_item-header">
                        <div>
                            <h3>${item.name}</h3>
                            <p style="margin-top:8px;color:#888;">${item.quantity} шт.</p>
                        </div>
                        <span class="item-price">${subtotal}</span>
                    </div>
                </div>
            </div>`;
        }).join('<hr style="margin:8px 0;border:none;border-top:1px solid #eee;">');
    }

    // Update totals
    const formattedTotal = Number(total).toLocaleString('ru-RU') + ' ₽';
    document.querySelectorAll('.total-row span:last-child, .final-total span:last-child').forEach(el => {
        el.textContent = formattedTotal;
    });

    // ── Validation helper ─────────────────────────────────────────────────────
    function showError(input, message) {
        input.style.borderColor = '#e53935';
        let err = input.parentElement.querySelector('.field-error');
        if (!err) {
            err = document.createElement('span');
            err.className = 'field-error';
            err.style.cssText = 'color:#e53935;font-size:12px;margin-top:4px;display:block;';
            input.parentElement.appendChild(err);
        }
        err.textContent = message;
    }

    function clearError(input) {
        input.style.borderColor = '';
        const err = input.parentElement.querySelector('.field-error');
        if (err) err.remove();
    }

    function validatePhone(val) {
        return /^[\d\s\+\-\(\)]{7,20}$/.test(val);
    }

    // Clear errors on input
    document.querySelectorAll('.input-container input').forEach(inp => {
        inp.addEventListener('input', () => clearError(inp));
    });

    // ── Pay button ────────────────────────────────────────────────────────────
    const payBtn = document.querySelector('.summary-card .btn');
    if (!payBtn) return;

    payBtn.addEventListener('click', async () => {
        // Collect values
        const phoneEl    = document.getElementById('orderPhone');
        const emailEl    = document.getElementById('orderEmail');
        const cityEl     = document.getElementById('orderCity');
        const streetEl   = document.getElementById('orderStreet');
        const houseEl    = document.getElementById('orderHouse');
        const entranceEl = document.getElementById('orderEntrance');
        const aptEl      = document.getElementById('orderApt');

        let valid = true;

        // Required fields
        if (!phoneEl.value.trim()) {
            showError(phoneEl, 'Введите номер телефона'); valid = false;
        } else if (!validatePhone(phoneEl.value.trim())) {
            showError(phoneEl, 'Некорректный номер телефона'); valid = false;
        } else {
            clearError(phoneEl);
        }

        // Email — required, validate format
        if (!emailEl || !emailEl.value.trim()) {
            showError(emailEl, 'Введите адрес почты'); valid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value.trim())) {
            showError(emailEl, 'Некорректный адрес почты'); valid = false;
        } else {
            clearError(emailEl);
        }

        if (!cityEl.value.trim()) {
            showError(cityEl, 'Введите город'); valid = false;
        } else { clearError(cityEl); }

        if (!streetEl.value.trim()) {
            showError(streetEl, 'Введите улицу'); valid = false;
        } else { clearError(streetEl); }

        if (!houseEl.value.trim()) {
            showError(houseEl, 'Введите номер дома'); valid = false;
        } else { clearError(houseEl); }

        // Подъезд — required, only digits
        if (!entranceEl || !entranceEl.value.trim()) {
            showError(entranceEl, 'Введите подъезд'); valid = false;
        } else if (!/^\d+$/.test(entranceEl.value.trim())) {
            showError(entranceEl, 'Только цифры'); valid = false;
        } else {
            clearError(entranceEl);
        }

        // Квартира — required, only digits
        if (!aptEl || !aptEl.value.trim()) {
            showError(aptEl, 'Введите квартиру'); valid = false;
        } else if (!/^\d+$/.test(aptEl.value.trim())) {
            showError(aptEl, 'Только цифры'); valid = false;
        } else {
            clearError(aptEl);
        }

        if (!valid) return;

        // Build address string
        let address = `${cityEl.value.trim()}, ${streetEl.value.trim()}, д. ${houseEl.value.trim()}`;
        if (entranceEl.value.trim()) address += `, подъезд ${entranceEl.value.trim()}`;
        if (aptEl.value.trim())      address += `, кв. ${aptEl.value.trim()}`;

        payBtn.disabled = true;
        payBtn.textContent = 'Оформляем...';

        const result = await apiPost('api/orders.php?action=create', {
            name:    authData.data.user_name,
            phone:   phoneEl.value.trim(),
            email:   emailEl?.value.trim() || '',
            address: address
        });

        if (result.success) {
            // Show success modal
            const modal = document.getElementById('orderSuccessModal');
            if (modal) {
                modal.style.display = 'flex';
                // Close on backdrop click
                modal.addEventListener('click', e => {
                    if (e.target === modal) window.location.href = 'profile.html';
                });
            } else {
                window.location.href = 'profile.html';
            }
        } else {
            alert(result.error || 'Ошибка оформления заказа');
            payBtn.disabled = false;
            payBtn.textContent = 'Оплатить';
        }
    });
});
