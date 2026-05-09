import './bootstrap';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import Chart from 'chart.js/auto';

const sidebarScrollStorageKey = 'app-sidebar-scroll-top';

document.addEventListener('DOMContentLoaded', () => {
    const sidebarNav = document.querySelector('.app-sidebar .app-scrollbar');

    if (!sidebarNav) {
        return;
    }

    const storedScrollTop = window.sessionStorage.getItem(sidebarScrollStorageKey);

    if (storedScrollTop !== null) {
        sidebarNav.scrollTop = Number.parseInt(storedScrollTop, 10) || 0;
    }

    const persistSidebarScroll = () => {
        window.sessionStorage.setItem(sidebarScrollStorageKey, String(sidebarNav.scrollTop));
    };

    sidebarNav.addEventListener('scroll', persistSidebarScroll, { passive: true });

    document.querySelectorAll('.app-sidebar a[href]').forEach((link) => {
        link.addEventListener('click', persistSidebarScroll);
    });

    window.addEventListener('beforeunload', persistSidebarScroll);
});

window.Alpine = Alpine;
window.Sortable = Sortable;
window.Chart = Chart;

Alpine.start();
