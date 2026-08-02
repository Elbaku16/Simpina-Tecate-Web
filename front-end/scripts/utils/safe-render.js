export function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

export function safeNumericId(value) {
    const id = Number(value);
    return Number.isInteger(id) && id > 0 ? id : 0;
}

export function safeEditorialAsset(path) {
    const value = String(path ?? '').replace(/^\/+/, '');
    if (!/^(?:uploads\/(?:preguntas|opciones)|front-end\/assets\/img)\/[A-Za-z0-9_(). ñÑáéíóúÁÉÍÓÚ-]+\.(?:jpe?g|png|webp)$/i.test(value)) {
        return '';
    }

    return `${window.BASE_URL}/${value.split('/').map(encodeURIComponent).join('/')}`;
}
