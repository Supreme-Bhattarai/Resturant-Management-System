(function () {
    'use strict';

    const dialog = document.getElementById('menu-catalog-dialog');
    if (!dialog || typeof dialog.showModal !== 'function') return;

    const book = dialog.querySelector('[data-catalog-book]');
    const pages = Array.from(dialog.querySelectorAll('[data-catalog-page]'));
    const chapters = Array.from(dialog.querySelectorAll('[data-catalog-chapter]'));
    const indexButtons = Array.from(dialog.querySelectorAll('[data-catalog-go]'));
    const chapterSelect = dialog.querySelector('[data-catalog-select]');
    const previousButton = dialog.querySelector('[data-catalog-prev]');
    const nextButton = dialog.querySelector('[data-catalog-next]');
    const status = dialog.querySelector('[data-catalog-status]');
    const closeButton = dialog.querySelector('[data-catalog-close]');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let currentPage = 0;
    let lastTrigger = null;
    let turning = false;
    let changeTimer = null;
    let finishTimer = null;

    function renderPage(index) {
        currentPage = index;
        pages.forEach((page, pageIndex) => { page.hidden = pageIndex !== index; });
        chapters.forEach((chapter, pageIndex) => { chapter.hidden = pageIndex !== index; });
        indexButtons.forEach((button, pageIndex) => {
            if (pageIndex === index) button.setAttribute('aria-current', 'page');
            else button.removeAttribute('aria-current');
        });
        if (chapterSelect) chapterSelect.value = String(index);
        previousButton.disabled = index === 0;
        nextButton.disabled = index === pages.length - 1;
        status.textContent = `Chapter ${index + 1} of ${pages.length}: ${pages[index].querySelector('h3')?.textContent || ''}`;
        const visibleList = pages[index].querySelector('.menu-catalog-page__items');
        if (visibleList) visibleList.scrollTop = 0;
    }

    function clearTurn() {
        window.clearTimeout(changeTimer);
        window.clearTimeout(finishTimer);
        book.classList.remove('is-turning-next', 'is-turning-previous');
        turning = false;
    }

    function closeCatalog() {
        dialog.close();
        lastTrigger?.focus({ preventScroll: true });
    }

    function goToPage(index) {
        if (!Number.isInteger(index) || index < 0 || index >= pages.length || index === currentPage || turning) return;
        if (reducedMotion.matches || window.innerWidth <= 760) {
            renderPage(index);
            return;
        }
        turning = true;
        book.classList.add(index > currentPage ? 'is-turning-next' : 'is-turning-previous');
        changeTimer = window.setTimeout(() => renderPage(index), 280);
        finishTimer = window.setTimeout(clearTurn, 590);
    }

    document.querySelectorAll('[data-catalog-open]').forEach(trigger => {
        trigger.addEventListener('click', () => {
            if (!pages.length) return;
            lastTrigger = trigger;
            renderPage(0);
            dialog.showModal();
            closeButton.focus();
        });
    });
    closeButton.addEventListener('click', closeCatalog);
    dialog.addEventListener('click', event => {
        if (event.target === dialog) closeCatalog();
    });
    dialog.addEventListener('close', () => {
        clearTurn();
        lastTrigger?.focus({ preventScroll: true });
    });
    dialog.addEventListener('cancel', clearTurn);

    previousButton.addEventListener('click', () => goToPage(currentPage - 1));
    nextButton.addEventListener('click', () => goToPage(currentPage + 1));
    indexButtons.forEach(button => {
        button.addEventListener('click', () => goToPage(Number(button.dataset.catalogGo)));
    });
    if (chapterSelect) chapterSelect.addEventListener('change', () => goToPage(Number(chapterSelect.value)));
    dialog.addEventListener('keydown', event => {
        if (event.altKey || event.ctrlKey || event.metaKey || event.target === chapterSelect) return;
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            goToPage(currentPage + 1);
        } else if (event.key === 'ArrowLeft') {
            event.preventDefault();
            goToPage(currentPage - 1);
        }
    });

    if (pages.length) renderPage(0);
})();
