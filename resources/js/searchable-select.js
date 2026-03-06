import TomSelect from 'tom-select';

const SELECTOR = 'select.searchable-select';

function initSearchableSelect(root = document) {
    const selects = root.querySelectorAll(SELECTOR);

    selects.forEach((select) => {
        if (select.tomselect) {
            return;
        }

        const placeholder = select.dataset.placeholder || 'Seleccione una opcion';
        const allowClear = select.dataset.allowClear === 'true';
        const dropdownParent = select.dataset.dropdownParent;

        const config = {
            create: false,
            maxOptions: 500,
            allowEmptyOption: true,
            placeholder,
            plugins: allowClear ? ['clear_button'] : [],
            render: {
                no_results(data, escape) {
                    return `<div class="px-3 py-2 text-sm text-graphite-500">No hay resultados para "${escape(data.input)}"</div>`;
                },
            },
        };

        if (dropdownParent) {
            const parent = document.querySelector(dropdownParent);
            if (parent) {
                config.dropdownParent = parent;
            }
        }

        new TomSelect(select, config);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initSearchableSelect();
});

// Permite reinicializar selects que se agreguen dinamicamente en modales o contenido AJAX.
document.addEventListener('searchable-select:init', (event) => {
    initSearchableSelect(event.detail?.root || document);
});
