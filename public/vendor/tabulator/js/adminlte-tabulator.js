/**
 * AdminLTE v4 Tabulator Helper
 * Standardized DataTables integration for AdminLTE v4 (Bootstrap 5, Vanilla JS)
 */

function stripHtml(html) {
  if (!html || typeof html !== 'string') return html || '';
  const tmp = document.createElement('div');
  tmp.innerHTML = html;
  return (tmp.textContent || tmp.innerText || '').trim().replace(/\s+/g, ' ');
}

window.initAdminLteDataTable = function(tableSelector, userOptions = {}) {
  const tableEl = typeof tableSelector === 'string' ? document.querySelector(tableSelector) : tableSelector;
  if (!tableEl) return null;

  const defaultOptions = {
    layout: "fitColumns",
    pagination: true,
    paginationSize: 10,
    paginationSizeSelector: [10, 25, 50, 100],
    placeholder: '<div class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No records found</div>',
  };

  const options = { ...defaultOptions, ...userOptions };

  // Initialize Tabulator from the HTML table
  const table = new Tabulator(tableEl, options);

  table.on("tableBuilt", function() {
    // 1. Attach download/print exclusions and clean HTML download accessors to columns
    const columns = table.getColumns();
    columns.forEach(col => {
      const def = col.getDefinition();
      const title = (def.title || '').toLowerCase().trim();

      if (title === '#' || title === 'actions') {
        if (title === 'actions') {
          col.updateDefinition({
            headerSort: false,
            download: false,
            print: false
          });
        }
      } else {
        // Strip HTML when downloading CSV or JSON
        if (def.formatter === 'html') {
          col.updateDefinition({
            accessorDownload: function(value) {
              return stripHtml(value);
            }
          });
        }
      }
    });

    // 2. Global search filter
    if (options.filterInput) {
      const filterEl = typeof options.filterInput === 'string'
        ? document.querySelector(options.filterInput)
        : options.filterInput;

      if (filterEl) {
        filterEl.addEventListener("input", function(e) {
          const val = e.target.value.trim();
          if (!val) {
            table.clearFilter();
            return;
          }

          // Search all non-action, non-# columns
          const searchableCols = table.getColumns().filter(c => {
            const t = (c.getDefinition().title || '').toLowerCase().trim();
            return t !== '#' && t !== 'actions';
          });

          const orFilters = searchableCols.map(c => ({
            field: c.getField(),
            type: "like",
            value: val
          }));

          if (orFilters.length > 0) {
            table.setFilter([orFilters]);
          }
        });
      }
    }

    // 3. Export CSV Button
    if (options.btnCsv) {
      const btn = typeof options.btnCsv === 'string' ? document.querySelector(options.btnCsv) : options.btnCsv;
      if (btn) {
        btn.addEventListener("click", function() {
          const name = (options.filename || 'export') + '.csv';
          table.download("csv", name);
        });
      }
    }

    // 4. Export JSON Button
    if (options.btnJson) {
      const btn = typeof options.btnJson === 'string' ? document.querySelector(options.btnJson) : options.btnJson;
      if (btn) {
        btn.addEventListener("click", function() {
          const name = (options.filename || 'export') + '.json';
          table.download("json", name);
        });
      }
    }

    // 5. Print Button
    if (options.btnPrint) {
      const btn = typeof options.btnPrint === 'string' ? document.querySelector(options.btnPrint) : options.btnPrint;
      if (btn) {
        btn.addEventListener("click", function() {
          table.print(false, true);
        });
      }
    }
  });

  return table;
};
