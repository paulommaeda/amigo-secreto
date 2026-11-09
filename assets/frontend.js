(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        const target = event.target.closest('[data-was-password-toggle], .was-app [data-was-view], .was-app [data-was-confirm], .was-app [data-was-copy-link]');
        if (!target) {
            return;
        }

        if (target.hasAttribute('data-was-password-toggle')) {
            const input = document.getElementById(target.getAttribute('aria-controls'));
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            target.setAttribute('aria-pressed', String(show));
            target.textContent = show ? 'Ocultar' : 'Mostrar';
            return;
        }

        if (target.hasAttribute('data-was-confirm')) {
            if (!window.confirm(target.dataset.wasConfirm)) {
                event.preventDefault();
            }
            return;
        }

        if (target.hasAttribute('data-was-view')) {
            event.preventDefault();
            const switcher = target.closest('[data-was-switcher]');
            const view = target.dataset.wasView;
            switcher.querySelectorAll('[data-was-panel]').forEach(function (panel) {
                panel.hidden = panel.dataset.wasPanel !== view;
            });
            return;
        }

        const isMobile = window.innerWidth < 768 || 'ontouchstart' in window;
        if (isMobile && navigator.clipboard) {
            event.preventDefault();
            const url = target.href;
            navigator.clipboard.writeText(url).then(function () {
                target.textContent = '✅ Link copiado!';
                target.classList.add('was-link-copied');
                window.setTimeout(function () {
                    target.textContent = url;
                    target.classList.remove('was-link-copied');
                }, 2500);
            }).catch(function () {
                window.location.assign(url);
            });
        }
    });

    document.addEventListener('input', function (event) {
        if (!event.target.matches('.was-app [data-was-phone]')) {
            return;
        }
        const digits = event.target.value.replace(/\D/g, '').slice(0, 11);
        if (digits.length <= 2) {
            event.target.value = digits;
            return;
        }

        const split = digits.length === 11 ? 7 : 6;
        const localNumber = digits.slice(2, split);
        const ending = digits.slice(split);
        event.target.value = '(' + digits.slice(0, 2) + ') ' + localNumber + (ending ? '-' + ending : '');
    });
}());
