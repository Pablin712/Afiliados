import * as XLSX from 'xlsx';
import { jsPDF } from 'jspdf';
import autoTable from 'jspdf-autotable';

const TABLE_SELECTOR = 'table[data-table]';

document.addEventListener('DOMContentLoaded', () => {
    addTableStyles();
    document.querySelectorAll(TABLE_SELECTOR).forEach(initEnhancedTable);
});

function addTableStyles() {
    if (document.getElementById('enhanced-table-styles')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'enhanced-table-styles';
    style.textContent = `
        .overflow-x-auto {
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
            scroll-behavior: smooth;
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        @media (max-width: 767px) {
            .overflow-x-auto {
                border-left: 3px solid #3f5fff;
                border-right: 3px solid #3f5fff;
            }

            .overflow-x-auto::-webkit-scrollbar-thumb {
                background: #3f5fff;
            }

            .overflow-x-auto::-webkit-scrollbar-track {
                background: #dbe5ff;
            }

            table th, table td {
                padding: 0.75rem 1rem !important;
            }
        }

        .sort-arrow {
            transition: all 0.2s ease;
            display: inline-block;
            margin-left: 4px;
        }

        .sortable:hover .sort-arrow {
            opacity: 1 !important;
            transform: scale(1.1);
        }

        .sortable {
            cursor: pointer;
            user-select: none;
        }

        .table-loading {
            position: relative;
            pointer-events: none;
        }

        .table-loading::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            z-index: 20;
        }

        .dark .table-loading::after {
            background: rgba(17, 18, 22, 0.7);
        }

        .table-loading-spinner {
            border: 3px solid #e2e8f0;
            border-top: 3px solid #3f5fff;
            border-radius: 9999px;
            width: 30px;
            height: 30px;
            animation: table-spin 1s linear infinite;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 30;
        }

        @keyframes table-spin {
            from { transform: translate(-50%, -50%) rotate(0deg); }
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }
    `;

    document.head.appendChild(style);
}

function initEnhancedTable(table) {
    const tbody = table.tBodies[0];
    if (!tbody) {
        return;
    }

    const allRows = Array.from(tbody.querySelectorAll('tr'));
    const explicitServerSide = table.dataset.serverSide;
    const isServerSide = explicitServerSide === 'true' || (explicitServerSide !== 'false' && allRows.length >= 500);

    const config = {
        table,
        tableId: table.dataset.table,
        isServerSide,
        searchUrl: table.dataset.searchUrl || window.location.href,
        currentPage: 1,
        rowsPerPage: 10,
        sortBy: null,
        sortDirection: 'asc',
        searchTerm: '',
        totalRecords: Number.parseInt(table.dataset.totalRecords || '0', 10) || allRows.length,
        allRows,
        filteredRows: allRows.slice(),
        sortStateByColumn: {},
        loading: false,
        searchInput: document.querySelector(`#${table.dataset.table}-search`),
        paginationSelect: document.querySelector(`#${table.dataset.table}-rows-per-page`),
        exportCsvBtn: document.querySelector(`#${table.dataset.table}-export-csv`),
        exportExcelBtn: document.querySelector(`#${table.dataset.table}-export-excel`),
        exportJsonBtn: document.querySelector(`#${table.dataset.table}-export-json`),
        exportPdfBtn: document.querySelector(`#${table.dataset.table}-export-pdf`),
        printBtn: document.querySelector(`#${table.dataset.table}-print`),
        columnToggles: document.querySelectorAll(`[data-toggle-col-${table.dataset.table}]`),
        paginationContainer: document.querySelector(`#${table.dataset.table}-pagination`),
        rowInfoContainer: document.querySelector(`#${table.dataset.table}-row-info`),
    };

    table._config = config;

    initTableEvents(config);
    initResponsiveFeatures(config);

    if (isServerSide) {
        const hasRenderedRows = config.allRows.length > 0;

        if (hasRenderedRows) {
            renderServerPagination(config, config.totalRecords);
            updateRowInfo(config);
        } else {
            loadServerData(config);
        }
    } else {
        renderClientPage(config);
    }
}

function initTableEvents(config) {
    const headers = config.table.querySelectorAll('th.sortable');

    headers.forEach((th, index) => {
        th.addEventListener('click', () => {
            const type = th.dataset.type || 'string';
            const columnState = config.sortStateByColumn[index] === 'asc' ? 'desc' : 'asc';
            config.sortStateByColumn[index] = columnState;

            if (config.isServerSide) {
                config.sortBy = th.dataset.sortBy || th.dataset.col || String(index);
                config.sortDirection = columnState;
                config.currentPage = 1;
                loadServerData(config);
            } else {
                sortTableByColumn(config, index, type, columnState);
                renderClientPage(config);
            }

            updateSortArrows(config, index, columnState);
        });
    });

    if (config.searchInput) {
        let searchTimeout;
        config.searchInput.addEventListener('input', (event) => {
            window.clearTimeout(searchTimeout);
            searchTimeout = window.setTimeout(() => {
                config.searchTerm = event.target.value.trim();
                config.currentPage = 1;

                if (config.isServerSide) {
                    loadServerData(config);
                } else {
                    filterClientTable(config);
                    renderClientPage(config);
                }
            }, 300);
        });
    }

    if (config.paginationSelect) {
        config.paginationSelect.addEventListener('change', (event) => {
            config.rowsPerPage = Number.parseInt(event.target.value, 10) || 10;
            config.currentPage = 1;

            if (config.isServerSide) {
                loadServerData(config);
            } else {
                renderClientPage(config);
            }
        });
    }

    if (config.exportCsvBtn) {
        config.exportCsvBtn.addEventListener('click', () => exportTableToCSV(config));
    }

    if (config.exportExcelBtn) {
        config.exportExcelBtn.addEventListener('click', () => exportTableToExcel(config));
    }

    if (config.exportJsonBtn) {
        config.exportJsonBtn.addEventListener('click', () => exportTableToJSON(config));
    }

    if (config.exportPdfBtn) {
        config.exportPdfBtn.addEventListener('click', () => exportTableToPDF(config));
    }

    if (config.printBtn) {
        config.printBtn.addEventListener('click', () => printTable(config));
    }

    config.columnToggles.forEach((toggle, columnIndex) => {
        toggle.addEventListener('change', () => {
            toggleColumn(config, columnIndex, toggle.checked);
        });
    });

    attachRowSelectionEvents(config.allRows);
}

function attachRowSelectionEvents(rows) {
    rows.forEach((row) => {
        row.addEventListener('click', () => {
            row.classList.toggle('selected');
        });
    });
}

async function loadServerData(config) {
    try {
        setLoading(config, true);

        const params = new URLSearchParams({
            page: String(config.currentPage),
            per_page: String(config.rowsPerPage),
            search: config.searchTerm,
            sort_by: config.sortBy || '',
            sort_order: config.sortDirection,
            ajax: 'true',
        });

        const response = await fetch(`${config.searchUrl}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            throw new Error('Server response is not JSON');
        }

        const data = await response.json();
        updateTableBody(config, data.html || '');
        config.totalRecords = Number.parseInt(String(data.total_records ?? data.total ?? 0), 10) || 0;

        renderServerPagination(config, config.totalRecords);
        updateRowInfo(config);
    } catch (error) {
        console.error('[Enhanced Table] server-side load error:', error);
        showError(config, 'Error al cargar los datos. Recarga la pagina.');
    } finally {
        setLoading(config, false);
    }
}

function updateTableBody(config, html) {
    const tbody = config.table.tBodies[0];
    tbody.innerHTML = html;

    const newRows = Array.from(tbody.querySelectorAll('tr'));
    config.allRows = newRows;
    config.filteredRows = newRows;
    attachRowSelectionEvents(newRows);
}

function renderServerPagination(config, totalRecords) {
    if (!config.paginationContainer) {
        return;
    }

    const totalPages = Math.max(1, Math.ceil(totalRecords / config.rowsPerPage));
    config.currentPage = Math.min(config.currentPage, totalPages);
    config.paginationContainer.innerHTML = '';

    const createButton = (label, page, disabled = false, active = false) => {
        const button = document.createElement('button');
        button.className = [
            'px-2 py-1 mx-1 rounded transition-colors duration-200',
            active
                ? 'bg-brand-600 text-white font-semibold shadow'
                : 'bg-gray-200 text-gray-700 hover:bg-brand-100 dark:bg-graphite-800 dark:text-graphite-100 dark:hover:bg-graphite-700',
            disabled ? 'opacity-50 cursor-not-allowed' : '',
        ].join(' ');
        button.textContent = label;

        if (!disabled && !active) {
            button.addEventListener('click', () => {
                config.currentPage = page;
                loadServerData(config);
            });
        }

        return button;
    };

    buildPaginationControls(config.paginationContainer, createButton, config.currentPage, totalPages);
}

function sortTableByColumn(config, columnIndex, type = 'string', order = 'asc') {
    const rows = config.filteredRows;

    rows.sort((rowA, rowB) => {
        let cellA = (rowA.cells[columnIndex]?.innerText || '').trim();
        let cellB = (rowB.cells[columnIndex]?.innerText || '').trim();

        if (type === 'number') {
            cellA = Number.parseFloat(cellA.replace(/[^\d.-]/g, '')) || 0;
            cellB = Number.parseFloat(cellB.replace(/[^\d.-]/g, '')) || 0;
            return order === 'asc' ? cellA - cellB : cellB - cellA;
        }

        return order === 'asc'
            ? cellA.localeCompare(cellB, undefined, { numeric: true, sensitivity: 'base' })
            : cellB.localeCompare(cellA, undefined, { numeric: true, sensitivity: 'base' });
    });

    const tbody = config.table.tBodies[0];
    rows.forEach((row) => tbody.appendChild(row));
}

function filterClientTable(config) {
    const term = config.searchTerm.toLowerCase();

    config.filteredRows = config.allRows.filter((row) => {
        const text = row.textContent.toLowerCase();
        return text.includes(term);
    });
}

function renderClientPage(config) {
    const rows = config.filteredRows;
    const start = (config.currentPage - 1) * config.rowsPerPage;
    const end = config.currentPage * config.rowsPerPage;

    rows.forEach((row, index) => {
        row.style.display = index >= start && index < end ? '' : 'none';
    });

    config.allRows.forEach((row) => {
        if (!rows.includes(row)) {
            row.style.display = 'none';
        }
    });

    renderClientPagination(config, rows.length);
    updateRowInfo(config);
}

function renderClientPagination(config, totalRows) {
    if (!config.paginationContainer) {
        return;
    }

    const totalPages = Math.max(1, Math.ceil(totalRows / config.rowsPerPage));
    config.currentPage = Math.min(config.currentPage, totalPages);
    config.paginationContainer.innerHTML = '';

    const createButton = (label, page, disabled = false, active = false) => {
        const button = document.createElement('button');
        button.className = [
            'px-2 py-1 mx-1 rounded transition-colors duration-200',
            active
                ? 'bg-brand-600 text-white font-semibold shadow'
                : 'bg-gray-200 text-gray-700 hover:bg-brand-100 dark:bg-graphite-800 dark:text-graphite-100 dark:hover:bg-graphite-700',
            disabled ? 'opacity-50 cursor-not-allowed' : '',
        ].join(' ');
        button.textContent = label;

        if (!disabled && !active) {
            button.addEventListener('click', () => {
                config.currentPage = page;
                renderClientPage(config);
            });
        }

        return button;
    };

    buildPaginationControls(config.paginationContainer, createButton, config.currentPage, totalPages);
}

function buildPaginationControls(container, createButton, currentPage, totalPages) {
    container.appendChild(createButton('<<', 1, currentPage === 1));
    container.appendChild(createButton('<', Math.max(1, currentPage - 1), currentPage === 1));

    let startPage = 1;
    let endPage = totalPages;

    if (totalPages > 10) {
        if (currentPage <= 6) {
            endPage = 10;
        } else if (currentPage + 4 >= totalPages) {
            startPage = totalPages - 9;
        } else {
            startPage = currentPage - 5;
            endPage = currentPage + 4;
        }
    }

    if (startPage > 1) {
        container.appendChild(createDots());
    }

    for (let page = startPage; page <= endPage; page += 1) {
        container.appendChild(createButton(String(page), page, false, page === currentPage));
    }

    if (endPage < totalPages) {
        container.appendChild(createDots());
    }

    container.appendChild(createButton('>', Math.min(totalPages, currentPage + 1), currentPage === totalPages));
    container.appendChild(createButton('>>', totalPages, currentPage === totalPages));
}

function createDots() {
    const dots = document.createElement('span');
    dots.textContent = '...';
    dots.className = 'mx-1 text-gray-500';
    return dots;
}

function setLoading(config, loading) {
    config.loading = loading;

    const tableContainer = config.table.closest('.overflow-x-auto');
    if (!tableContainer) {
        return;
    }

    if (loading) {
        tableContainer.classList.add('table-loading');

        if (!tableContainer.querySelector('.table-loading-spinner')) {
            const spinner = document.createElement('div');
            spinner.className = 'table-loading-spinner';
            tableContainer.appendChild(spinner);
        }

        return;
    }

    tableContainer.classList.remove('table-loading');
    const spinner = tableContainer.querySelector('.table-loading-spinner');
    if (spinner) {
        spinner.remove();
    }
}

function showError(config, message) {
    const tbody = config.table.tBodies[0];
    const columnsCount = config.table.querySelectorAll('th').length;

    tbody.innerHTML = `
        <tr>
            <td colspan="${columnsCount}" class="text-center p-8 text-red-500">
                <div class="flex flex-col items-center gap-3">
                    <svg class="w-12 h-12 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="text-lg font-medium">${message}</p>
                </div>
            </td>
        </tr>
    `;
}

function updateSortArrows(config, columnIndex, order) {
    const headers = config.table.querySelectorAll('th.sortable');

    headers.forEach((th, idx) => {
        const arrow = th.querySelector('.sort-arrow');
        if (!arrow) {
            return;
        }

        if (idx === columnIndex) {
            arrow.innerHTML = order === 'asc'
                ? '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 15l7-7 7 7"/></svg>'
                : '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>';
            arrow.style.opacity = '1';
        } else {
            arrow.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 11l5-5 5 5M7 13l5 5 5-5"/></svg>';
            arrow.style.opacity = '0.35';
        }
    });
}

function updateRowInfo(config) {
    if (!config.rowInfoContainer) {
        return;
    }

    const totalRows = config.isServerSide ? config.totalRecords : config.filteredRows.length;
    const start = totalRows === 0 ? 0 : (config.currentPage - 1) * config.rowsPerPage + 1;
    const end = Math.min(config.currentPage * config.rowsPerPage, totalRows);

    config.rowInfoContainer.textContent = `${start} a ${end} de ${totalRows}`;
}

function toggleColumn(config, columnIndex, show) {
    config.table.querySelectorAll('tr').forEach((row) => {
        if (row.cells[columnIndex]) {
            row.cells[columnIndex].style.display = show ? '' : 'none';
        }
    });

    const tableContainer = document.getElementById(`${config.tableId}-table-container`);
    if (tableContainer) {
        window.setTimeout(() => {
            if (tableContainer.scrollWidth > tableContainer.clientWidth) {
                tableContainer.setAttribute('data-scrollable', 'true');
            } else {
                tableContainer.removeAttribute('data-scrollable');
            }
        }, 80);
    }
}

function getExportMeta(config) {
    const headers = Array.from(config.table.querySelectorAll('th'));

    const columnIndexes = [];
    const headerLabels = [];

    headers.forEach((th, index) => {
        const isAction = th.dataset.type === 'actions';
        const hidden = th.style.display === 'none';

        if (!isAction && !hidden) {
            columnIndexes.push(index);
            const clone = th.cloneNode(true);
            clone.querySelectorAll('svg, .sort-arrow').forEach((node) => node.remove());
            headerLabels.push(cleanText(clone.textContent));
        }
    });

    const rows = config.isServerSide ? config.allRows : config.filteredRows;
    const dataRows = rows
        .filter((row) => row.style.display !== 'none')
        .map((row) => columnIndexes.map((index) => cleanText(row.cells[index]?.textContent || '')));

    return { headerLabels, dataRows };
}

function exportTableToCSV(config, filename = 'export.csv') {
    if (config.isServerSide) {
        exportFromServer(config, 'csv');
        return;
    }

    const { headerLabels, dataRows } = getExportMeta(config);
    const csvRows = [headerLabels, ...dataRows].map((row) => row.map((value) => `"${value.replace(/"/g, '""')}"`).join(','));
    downloadBlob(new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' }), filename);
}

function exportTableToJSON(config, filename = 'export.json') {
    if (config.isServerSide) {
        exportFromServer(config, 'json');
        return;
    }

    const { headerLabels, dataRows } = getExportMeta(config);
    const data = dataRows.map((row) => {
        const item = {};
        row.forEach((value, index) => {
            item[headerLabels[index]] = value;
        });
        return item;
    });

    const payload = {
        exported_at: new Date().toISOString(),
        total_records: data.length,
        data,
    };

    downloadBlob(new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json;charset=utf-8;' }), filename);
}

function exportTableToExcel(config, filename = 'export.xlsx') {
    if (config.isServerSide) {
        exportFromServer(config, 'excel');
        return;
    }

    const { headerLabels, dataRows } = getExportMeta(config);
    const worksheet = XLSX.utils.aoa_to_sheet([headerLabels, ...dataRows]);
    const workbook = XLSX.utils.book_new();

    worksheet['!cols'] = headerLabels.map((header, index) => {
        const maxLength = Math.max(header.length, ...dataRows.map((row) => (row[index] || '').length));
        return { wch: Math.min(maxLength + 2, 50) };
    });

    XLSX.utils.book_append_sheet(workbook, worksheet, 'Data');
    XLSX.writeFile(workbook, filename);
}

function exportTableToPDF(config, filename = 'export.pdf') {
    if (config.isServerSide) {
        exportFromServer(config, 'pdf');
        return;
    }

    const { headerLabels, dataRows } = getExportMeta(config);

    const doc = new jsPDF({
        orientation: headerLabels.length > 6 ? 'landscape' : 'portrait',
        unit: 'mm',
        format: 'a4',
    });

    doc.setFontSize(16);
    doc.text('Reporte de Datos', 14, 15);
    doc.setFontSize(9);
    doc.text(`Generado: ${new Date().toLocaleString('es-ES')}`, 14, 22);
    doc.text(`Total de registros: ${dataRows.length}`, 14, 27);

    autoTable(doc, {
        head: [headerLabels],
        body: dataRows,
        startY: 32,
        theme: 'striped',
        headStyles: {
            fillColor: [47, 79, 255],
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            fontSize: 10,
            halign: 'left',
        },
        bodyStyles: {
            fontSize: 9,
            cellPadding: 3,
        },
        alternateRowStyles: {
            fillColor: [245, 245, 245],
        },
        margin: { top: 32, right: 14, bottom: 14, left: 14 },
        styles: {
            overflow: 'linebreak',
            cellWidth: 'wrap',
            minCellHeight: 8,
        },
        didDrawPage(data) {
            const pageCount = doc.getNumberOfPages();
            doc.setFontSize(8);
            doc.setTextColor(128, 128, 128);
            doc.text(`Pagina ${data.pageNumber} de ${pageCount}`, doc.internal.pageSize.getWidth() / 2, doc.internal.pageSize.getHeight() - 10, {
                align: 'center',
            });
        },
    });

    doc.save(filename);
}

function exportFromServer(config, format) {
    const params = new URLSearchParams({
        export: format,
        search: config.searchTerm,
        sort_by: config.sortBy || '',
        sort_order: config.sortDirection,
    });

    window.location.href = `${config.searchUrl}?${params.toString()}`;
}

function printTable(config) {
    const { headerLabels, dataRows } = getExportMeta(config);

    const headerHtml = headerLabels.map((header) => `<th>${escapeHtml(header)}</th>`).join('');
    const bodyHtml = dataRows
        .map((row) => `<tr>${row.map((cell) => `<td>${escapeHtml(cell)}</td>`).join('')}</tr>`)
        .join('');

    const printHTML = `
        <html>
            <head>
                <title>Reporte de Tabla</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; background: white; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; font-size: 12px; }
                    th { background: #2f4fff; color: white; font-weight: bold; text-transform: uppercase; }
                    tr:nth-child(even) { background: #f8f9fa; }
                    .print-header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2f4fff; padding-bottom: 10px; }
                    .print-date { text-align: right; font-size: 10px; color: #666; margin-bottom: 10px; }
                    @media print { body { margin: 0; } }
                </style>
            </head>
            <body>
                <div class="print-date">Generado el: ${new Date().toLocaleString('es-ES')}</div>
                <div class="print-header"><h2>Reporte de Datos</h2></div>
                <table>
                    <thead><tr>${headerHtml}</tr></thead>
                    <tbody>${bodyHtml}</tbody>
                </table>
            </body>
        </html>
    `;

    const windowRef = window.open('', '', 'width=1024,height=768');
    if (!windowRef) {
        return;
    }

    windowRef.document.write(printHTML);
    windowRef.document.close();
    windowRef.focus();
    windowRef.print();
}

function initResponsiveFeatures(config) {
    const toggleButton = document.getElementById(`${config.tableId}-toggle-columns`);
    const columnsContainer = document.getElementById(`${config.tableId}-columns-container`);

    if (toggleButton && columnsContainer) {
        toggleButton.addEventListener('click', () => {
            const isHidden = columnsContainer.classList.contains('hidden');
            columnsContainer.classList.toggle('hidden', !isHidden);
            toggleButton.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        });

        toggleButton.setAttribute('aria-expanded', 'false');
    }

    const tableContainer = document.getElementById(`${config.tableId}-table-container`);
    if (tableContainer) {
        addScrollIndicators(tableContainer);
    }
}

function addScrollIndicators(container) {
    let scrollTimeout;

    container.addEventListener('scroll', function onScroll() {
        this.style.borderColor = '#3f5fff';

        window.clearTimeout(scrollTimeout);
        scrollTimeout = window.setTimeout(() => {
            this.style.borderColor = '';
        }, 800);
    });

    const checkScrollNeed = () => {
        if (container.scrollWidth > container.clientWidth) {
            container.setAttribute('data-scrollable', 'true');
        } else {
            container.removeAttribute('data-scrollable');
        }
    };

    checkScrollNeed();
    window.addEventListener('resize', checkScrollNeed);
}

function cleanText(value) {
    return String(value || '')
        .replace(/\s+/g, ' ')
        .trim();
}

function downloadBlob(blob, filename) {
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
}

function escapeHtml(value) {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
