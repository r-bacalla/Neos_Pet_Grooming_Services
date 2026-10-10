const storageKey = 'neos-pet-grooming-scroll-position';
const anchorStorageKey = 'neos-pet-grooming-scroll-anchor';

try {
    if ('scrollRestoration' in window.history) {
        window.history.scrollRestoration = 'manual';
    }

    const savePosition = () => {
        sessionStorage.setItem(storageKey, JSON.stringify({ top: window.scrollY }));
    };

    let scrollSaveFrame = null;
    window.addEventListener('scroll', () => {
        if (scrollSaveFrame !== null) return;

        scrollSaveFrame = window.requestAnimationFrame(() => {
            savePosition();
            scrollSaveFrame = null;
        });
    }, { passive: true });

    let pendingPosition = null;
    let pendingAnchor = sessionStorage.getItem(anchorStorageKey);
    sessionStorage.removeItem(anchorStorageKey);
    const savedPosition = sessionStorage.getItem(storageKey);
    if (savedPosition) {
        sessionStorage.removeItem(storageKey);
        if (!window.location.hash) pendingPosition = JSON.parse(savedPosition).top;
    }

    const restorePosition = () => {
        if (pendingAnchor !== null) {
            const target = document.getElementById(pendingAnchor);
            if (!target) return;

            window.requestAnimationFrame(() => {
                target.scrollIntoView({ block: 'center', inline: 'nearest' });
                pendingAnchor = null;
                pendingPosition = null;
            });

            return;
        }

        if (pendingPosition === null) return;

        const top = pendingPosition;
        window.requestAnimationFrame(() => {
                window.scrollTo({ top, left: 0, behavior: 'instant' });
            if (window.scrollY >= top || document.readyState === 'complete') {
                pendingPosition = null;
            }
        });
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link || link.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        if (link.closest('nav, aside')) {
            savePosition();
            return;
        }

        const destination = new URL(link.href, window.location.href);
        if (destination.origin === window.location.origin && !destination.hash) {
            savePosition();
        }
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (form.target && form.target !== '_self') return;

        savePosition();
        if (form.dataset.scrollTarget) {
            sessionStorage.setItem(anchorStorageKey, form.dataset.scrollTarget);
        }
        window.setTimeout(() => {
            if (event.defaultPrevented) sessionStorage.removeItem(storageKey);
        }, 0);
    }, true);

    window.addEventListener('pagehide', savePosition);
    window.addEventListener('pageshow', restorePosition);
    window.addEventListener('load', restorePosition, { once: true });
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', restorePosition, { once: true });
    } else {
        restorePosition();
    }
} catch {
    // Scroll restoration is optional when browser storage is unavailable.
}