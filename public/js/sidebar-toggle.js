/* Sidebar toggle icon swap (collapsed / expanded). */
(function () {
    'use strict';

    var TOGGLE = '[data-lte-toggle="sidebar"]';
    var ICON_COLLAPSE = '.ti-layout-sidebar-left-collapse';
    var ICON_EXPAND = '.ti-layout-sidebar-left-expand';

    function isCollapsed() {
        return document.body.classList.contains('sidebar-collapse');
    }

    function sync(toggle) {
        var iconCollapse = toggle.querySelector(ICON_COLLAPSE);
        var iconExpand = toggle.querySelector(ICON_EXPAND);

        if (iconCollapse) {
            iconCollapse.classList.toggle('d-none', isCollapsed());
        }
        if (iconExpand) {
            iconExpand.classList.toggle('d-none', !isCollapsed());
        }
    }

    function syncAll() {
        document.querySelectorAll(TOGGLE).forEach(sync);
    }

    function initSidebarToggle() {
        document.addEventListener('click', function (event) {
            var toggle = event.target.closest(TOGGLE);
            if (toggle) {
                sync(toggle);
            }
        });

        syncAll();
    }

    document.addEventListener('DOMContentLoaded', initSidebarToggle);
})();
