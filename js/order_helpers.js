function save_last_order(order_receipt) {
    localStorage.setItem(LAST_ORDER_STORAGE_KEY, JSON.stringify(order_receipt));
}

function load_last_order() {
    try {
        const raw = localStorage.getItem(LAST_ORDER_STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch (error) {
        console.error(error);
        return null;
    }
}

function build_order_whatsapp_message(order_receipt) {
    const lines = [
        `New Gold Scents order #${order_receipt.order_id}`,
        `Customer: ${order_receipt.customer_name}`,
        `Phone: ${order_receipt.customer_phone}`,
        `Email: ${order_receipt.customer_email}`,
        `Address: ${order_receipt.delivery_address}`,
        `MoMo ID: ${order_receipt.momo_transaction_id}`,
        `Total: ${CURRENCY_PREFIX}${parseFloat(order_receipt.total_price).toFixed(2)}`,
        'Items:',
    ];

    (order_receipt.items || []).forEach((item) => {
        lines.push(`- ${item.name} x${item.quantity}`);
    });

    return lines.join('\n');
}

function build_seller_whatsapp_link(order_receipt) {
    return `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(build_order_whatsapp_message(order_receipt))}`;
}

function get_status_step_index(status_value) {
    const index = ORDER_STATUS_STEPS.indexOf(status_value);
    return index < 0 ? 0 : index;
}

function render_status_tracker(current_status) {
    const current_index = get_status_step_index(current_status);

    const steps_html = ORDER_STATUS_STEPS.map((status_value, index) => {
        const state_class = index <= current_index ? 'is-complete' : '';
        const current_class = index === current_index ? 'is-current' : '';
        return `
            <div class="tracker-step ${state_class} ${current_class}">
                <span class="tracker-dot"></span>
                <span class="tracker-label">${ORDER_STATUS_LABELS[status_value]}</span>
            </div>
        `;
    }).join('');

    return `<div class="status-tracker">${steps_html}</div>`;
}
