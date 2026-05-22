document.addEventListener('DOMContentLoaded', async () => {
    initAuthHeader();

    // ── Auth check ────────────────────────────────────────────────────────────
    const authData = await apiGet('api/auth.php', { action: 'status' });
    if (!authData.success || !authData.data.logged_in) {
        window.location.href = 'login-modal.html';
        return;
    }

    // Hide the "login to continue" subtitle since user is logged in
    const subtitle = document.querySelector('.cart-subtitle');
    if (subtitle) subtitle.style.display = 'none';

    // ── Load cart items into the order summary ────────────────────────────────
    const cartData = await apiGet('api/cart.php', { action: 'get' });
    if (!cartData.success || cartData.data.items.length === 0) {
        window.location.href = 'cart.html';
        return;
    }

    const { items, total } = cartData.data;

    // Render cart items in the order
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

    // ── Wire up the pay button ────────────────────────────────────────────────
    const payBtn = document.querySelector('.make button.btn, #make_pay_card ~ .summary-card .btn, .summary-card .btn');
    if (payBtn) {
        payBtn.addEventListener('click', async () => {
            // Collect form fields
            const phone   = document.querySelector('input[type="text"]')?.value.trim() || '';
            const email   = document.querySelector('input[type="email"]')?.value.trim() || '';

            // Address fields: city, street, house, apartment
            const textInputs = [...document.querySelectorAll('.form-section:last-of-type input[type="text"]')];
            const city     = textInputs[0]?.value.trim() || '';
            const street   = textInputs[1]?.value.trim() || '';
            const house    = textInputs[2]?.value.trim() || '';
            const apt      = textInputs[3]?.value.trim() || '';
            const address  = [city, street, house, apt].filter(Boolean).join(', ');

            const user = authData.data;
            const name = user.user_name;

            if (!phone) {
                alert('Введите номер телефона');
                return;
            }

            payBtn.disabled = true;
            payBtn.textContent = 'Оформляем...';

            const result = await apiPost('api/orders.php?action=create', {
                name,
                phone,
                email,
                address
            });

            if (result.success) {
                window.location.href = `orders.html`;
            } else {
                alert(result.error || 'Ошибка оформления заказа');
                payBtn.disabled = false;
                payBtn.textContent = 'Оплатить';
            }
        });
    }
});
