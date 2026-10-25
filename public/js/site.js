(() => {
    const config = window.checkoutConfig;
    const dialog = document.querySelector('#checkout-dialog');
    const form = document.querySelector('#checkout-form');
    const quantityInput = document.querySelector('#quantity');
    const quantityOutput = document.querySelector('#quantity-output');
    const total = document.querySelector('#checkout-total');
    const error = document.querySelector('#checkout-error');
    const submit = document.querySelector('#checkout-submit');
    const toastElement = document.querySelector('#toast');
    let toastTimeout;

    const formatPrice = (amount) => `Rp ${new Intl.NumberFormat('id-ID').format(amount)}`;
    const toast = (message) => {
        toastElement.textContent = message;
        toastElement.classList.add('on');
        window.clearTimeout(toastTimeout);
        toastTimeout = window.setTimeout(() => toastElement.classList.remove('on'), 4000);
    };
    const renderTotal = () => {
        const quantity = Number(quantityInput.value);
        quantityOutput.textContent = String(quantity);
        total.textContent = formatPrice(quantity * config.price);
    };

    document.querySelectorAll('[data-open-checkout]').forEach((button) => {
        button.addEventListener('click', () => {
            quantityInput.value = '1';
            error.textContent = '';
            form.reset();
            quantityInput.value = '1';
            renderTotal();
            dialog.showModal();
        });
    });

    document.querySelector('[data-close-checkout]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
    document.querySelector('#quantity-minus').addEventListener('click', () => {
        quantityInput.value = String(Math.max(1, Number(quantityInput.value) - 1));
        renderTotal();
    });
    document.querySelector('#quantity-plus').addEventListener('click', () => {
        quantityInput.value = String(Math.min(config.stock, Number(quantityInput.value) + 1));
        renderTotal();
    });

    document.querySelectorAll('[data-unavailable]').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            toast(link.dataset.unavailable);
        });
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        error.textContent = '';
        submit.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': config.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });
            const payload = await response.json();

            if (!response.ok) {
                if (payload.errors) {
                    error.textContent = Object.values(payload.errors).flat().join('\n');
                } else {
                    error.textContent = payload.message || 'Checkout gagal. Silakan coba lagi.';
                }
                return;
            }

            if (!payload.payment_url) {
                error.textContent = 'Halaman pembayaran tidak dapat dibuat. Silakan coba lagi atau hubungi admin.';
                return;
            }

            window.location.assign(payload.payment_url);
        } catch {
            error.textContent = 'Tidak dapat terhubung ke server. Silakan coba lagi.';
        } finally {
            submit.disabled = false;
        }
    });

    renderTotal();
})();
