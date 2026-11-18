function ensure_toast_host() {
    let host = document.getElementById('toast-host');
    if (host) {
        return host;
    }

    host = document.createElement('div');
    host.id = 'toast-host';
    host.className = 'toast-host';
    document.body.appendChild(host);
    return host;
}

function show_toast(message, tone = 'success') {
    const host = ensure_toast_host();
    const toast = document.createElement('div');
    toast.className = `toast toast-${tone}`;
    toast.textContent = message;
    host.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.add('toast-visible');
    });

    setTimeout(() => {
        toast.classList.remove('toast-visible');
        setTimeout(() => toast.remove(), 280);
    }, 2800);
}

function escape_html(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function format_money(amount) {
    return `${CURRENCY_PREFIX}${parseFloat(amount).toFixed(2)}`;
}
