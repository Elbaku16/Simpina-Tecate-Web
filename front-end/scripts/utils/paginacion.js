// utils/paginacion.js

export const PREGUNTAS_POR_PAGINA = 3;

export function construirPaginas(lista) {
    const pages = [];
    let buffer = [];

    lista.forEach((p, i) => {
        if (p.tipo === 'ranking') {
            if (buffer.length) {
                pages.push([...buffer]);
                buffer = [];
            }
            pages.push([i]);
        } else {
            buffer.push(i);
            if (buffer.length === PREGUNTAS_POR_PAGINA) {
                pages.push([...buffer]);
                buffer = [];
            }
        }
    });

    if (buffer.length) pages.push([...buffer]);
    return pages;
}
