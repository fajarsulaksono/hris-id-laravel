/* Master Data (Fase 1): DataTables server-side + select dependen. */
(function () {
    'use strict';

    var lang = {
        processing: 'Memproses...',
        loadingRecords: 'Memuat...',
        lengthMenu: 'Tampilkan _MENU_ data',
        search: '<i class="ti ti-search"></i>',
        zeroRecords: 'Tidak ditemukan data yang cocok',
        emptyTable: 'Belum ada data',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        infoFiltered: '(difilter dari total _MAX_ data)',
        paginate: {
            first: '<i class="ti ti-chevron-left-pipe"></i>',
            previous: '<i class="ti ti-chevron-left"></i>',
            next: '<i class="ti ti-chevron-right"></i>',
            last: '<i class="ti ti-chevron-right-pipe"></i>',
        },
    };

    function initMasterTable(selector, columns) {
        var table = document.querySelector(selector);
        if (!table) {
            return;
        }

        window.hris._tables = window.hris._tables || {};

        return new DataTable(table, {
            serverSide: true,
            processing: true,
            ajax: {
                url: table.dataset.dtUrl,
                data: function (d) {
                    d.trashed = table.dataset.dtTrashed || '0';
                },
            },
            columns: columns.map(function (c) {
                return {
                    data: c.data,
                    title: c.title,
                    orderable: !!c.orderable !== false,
                    searchable: !!c.searchable !== false,
                    className: c.data === 'actions' ? 'text-end' : '',
                };
            }),
            columnDefs: columns.map(function (c, i) {
                return {
                    targets: i,
                    orderable: c.data === 'actions' ? false : (c.orderable !== false),
                    searchable: c.data === 'actions' ? false : (c.searchable !== false),
                };
            }),
            order: [],
            pageLength: 10,
            language: lang,
        });
    }

    function loadDepends(child, parent) {
        var url = child.dataset.dependsUrl;
        var parentId = parent.value;

        if (!parentId) {
            child.innerHTML = '<option value="">---</option>';
            return;
        }

        child.disabled = true;
        fetch(url + (url.indexOf('?') >= 0 ? '&' : '?') + 'parent_id=' + encodeURIComponent(parentId), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.json(); })
            .then(function (items) {
                var previous = child.value;
                var html = '<option value="">---</option>';

                items.forEach(function (item) {
                    html += '<option value="' + item.id + '">' + item.text + '</option>';
                });

                child.innerHTML = html;
                if (previous !== '') {
                    child.value = previous;
                }
            })
            .finally(function () {
                child.disabled = false;
            });
    }

    function initDependencies(root) {
        root = root || document;

        root.querySelectorAll('select[data-depends-on]').forEach(function (child) {
            var parent = root.querySelector('[name="' + child.dataset.dependsOn + '"]');
            if (!parent) {
                return;
            }

            parent.addEventListener('change', function () {
                loadDepends(child, parent);
            });

            if (parent.value) {
                loadDepends(child, parent);
            }
        });
    }

    window.hris = window.hris || {};
    window.hris.initMasterTable = initMasterTable;
    window.hris.initDependencies = initDependencies;

    document.addEventListener('DOMContentLoaded', function () {
        initDependencies(document);
    });
})();