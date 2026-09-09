/* Password show/hide toggle (non-inline). */
(function () {
    'use strict';

    function initPasswordToggles(root) {
        if (!root) {
            return;
        }

        root.querySelectorAll('[data-password-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var group = button.closest('.input-group');
                var input = group ? group.querySelector('input[type="password"], input[type="text"]') : null;

                if (!input) {
                    return;
                }

                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';

                var iconShow = button.querySelector('.ti-eye');
                var iconHide = button.querySelector('.ti-eye-off');

                if (iconShow) {
                    iconShow.classList.toggle('d-none', show);
                }
                if (iconHide) {
                    iconHide.classList.toggle('d-none', !show);
                }

                button.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                button.setAttribute('aria-pressed', show ? 'true' : 'false');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initPasswordToggles(document);
    });
})();
