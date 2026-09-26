document.addEventListener('DOMContentLoaded', function() {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const getHashTarget = hash => {
        if (!hash || hash.length < 2) return null;
        try {
            return document.getElementById(decodeURIComponent(hash.slice(1)));
        } catch (error) {
            return null;
        }
    };
    const revealHashRegion = target => {
        if (!target) return;
        const region = target.closest('section') || target;
        region.querySelectorAll('.js-reveal').forEach(element => element.classList.remove('js-reveal'));
        let ancestor = region;
        while (ancestor) {
            ancestor.classList.remove('js-reveal');
            ancestor = ancestor.parentElement;
        }
    };
    const initialHashTarget = getHashTarget(window.location.hash);
    if (initialHashTarget) {
        revealHashRegion(initialHashTarget);
        const alignHashTarget = () => initialHashTarget.scrollIntoView({ behavior: 'auto', block: 'start' });
        alignHashTarget();
        window.addEventListener('load', alignHashTarget, { once: true });
    }
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    document.querySelectorAll('a[href^="#"]:not([data-bs-toggle])').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = getHashTarget(this.hash);
            if (!target) return;
            e.preventDefault();
            revealHashRegion(target);
            target.scrollIntoView({ behavior: reducedMotion.matches ? 'auto' : 'smooth' });
        });
    });

    const menuGrid = document.querySelector('.menu-section .menu-grid');
    const menuItems = Array.from(menuGrid?.querySelectorAll('.menu-item') || []);
    const categoryBtns = Array.from(document.querySelectorAll('.menu-categories .category-btn'));
    const menuSearchInput = document.querySelector('.menu-section .search-form input[name="search"]');
    const menuParams = new URLSearchParams(window.location.search);
    const loadedCategory = (menuParams.get('category') || 'all').toLowerCase();
    const loadedSearch = menuParams.get('search') || '';
    let activeCategory = categoryBtns.find(button => button.classList.contains('active'))?.dataset.category || 'all';

    if (menuGrid && menuItems.length && (categoryBtns.length || menuSearchInput)) {
        const filterFeedback = document.createElement('div');
        filterFeedback.className = 'empty-state menu-filter-feedback';
        filterFeedback.hidden = true;
        filterFeedback.innerHTML = '<i class="bi bi-search" aria-hidden="true"></i><h3>No dishes found</h3><p>Try another category or search term.</p>';
        menuGrid.appendChild(filterFeedback);

        const resultsStatus = document.createElement('p');
        resultsStatus.className = 'visually-hidden';
        resultsStatus.setAttribute('role', 'status');
        resultsStatus.setAttribute('aria-live', 'polite');
        menuGrid.before(resultsStatus);

        const filterMenu = () => {
            const searchTerm = loadedSearch ? '' : (menuSearchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            menuItems.forEach(item => {
                const matchesCategory = activeCategory === 'all' || item.dataset.category === activeCategory;
                const matchesSearch = !searchTerm || item.textContent.toLowerCase().includes(searchTerm);
                const visible = matchesCategory && matchesSearch;
                item.hidden = !visible;
                item.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            filterFeedback.hidden = visibleCount !== 0;
            resultsStatus.textContent = `${visibleCount} menu ${visibleCount === 1 ? 'item' : 'items'} shown`;
        };

        categoryBtns.forEach(button => {
            button.setAttribute('aria-pressed', String(button.dataset.category === activeCategory));
            button.addEventListener('click', event => {
                const nextCategory = button.dataset.category;
                if (!nextCategory) return;

                if (loadedCategory !== 'all' && nextCategory !== loadedCategory) {
                    const url = new URL(window.location.href);
                    if (nextCategory === 'all') url.searchParams.delete('category');
                    else url.searchParams.set('category', nextCategory);
                    url.searchParams.delete('search');
                    window.location.assign(url.toString());
                    return;
                }

                event.preventDefault();
                activeCategory = nextCategory;
                categoryBtns.forEach(otherButton => {
                    const selected = otherButton === button;
                    otherButton.classList.toggle('active', selected);
                    otherButton.setAttribute('aria-pressed', String(selected));
                });
                filterMenu();
            });
        });

        if (menuSearchInput && !loadedSearch) menuSearchInput.addEventListener('input', filterMenu);
        filterMenu();
    }

    const guestMinus = document.querySelector('.guest-minus');
    const guestPlus = document.querySelector('.guest-plus');
    const guestCount = document.querySelector('.guest-count');

    if (guestMinus && guestPlus && guestCount) {
        guestMinus.addEventListener('click', function() {
            let count = parseInt(guestCount.value);
            if (count > 1) {
                guestCount.value = count - 1;
            }
        });

        guestPlus.addEventListener('click', function() {
            let count = parseInt(guestCount.value);
            let maxGuests = parseInt(guestCount.dataset.max) || 20;
            if (count < maxGuests) {
                guestCount.value = count + 1;
            }
        });
    }

    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    const alerts = document.querySelectorAll('.alert-success.alert-dismissible');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });

    const deleteButtons = document.querySelectorAll('.btn-delete-confirm');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    const cancelButtons = document.querySelectorAll('.btn-cancel-booking');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to cancel this booking?')) {
                e.preventDefault();
            }
        });
    });

    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = input.parentElement.querySelector('.image-preview img');
                    if (preview) {
                        preview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });

    const navbar = document.querySelector('#mainNavbar');
    if (navbar) {
        let navTicking = false;
        const updateNavbar = () => {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
            navTicking = false;
        };
        updateNavbar();
        window.addEventListener('scroll', () => {
            if (navTicking) return;
            navTicking = true;
            window.requestAnimationFrame(updateNavbar);
        }, { passive: true });
    }

    if (!reducedMotion.matches && !window.location.hash && 'IntersectionObserver' in window) {
        const revealTargets = document.querySelectorAll([
            '.section-title', '.about-image', '.about-text', '.menu-grid > .menu-item',
            '.gallery-grid > .gallery-item', '.reservation-intro', '.reservation-form',
            '.visit-content', '.contact-lead-card', '.contact-details-card',
            '.contact-booking', '.value-card', '.philosophy-section', '.table-choice',
            '.kids-intro', '.kids-showcase', '.kids-dish', '[data-reveal]'
        ].join(','));
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px 64px 0px', threshold: 0.04 });

        revealTargets.forEach((element, index) => {
            if (element.getBoundingClientRect().top < window.innerHeight * 0.92) return;
            element.classList.add('js-reveal');
            element.style.setProperty('--reveal-delay', `${index % 3 * 85}ms`);
            observer.observe(element);
        });
        document.documentElement.classList.add('motion-ready');

        document.addEventListener('focusin', event => {
            const waiting = event.target.closest('.js-reveal:not(.is-visible)');
            if (waiting) {
                waiting.classList.add('is-visible');
                observer.unobserve(waiting);
            }
        });
    }
    window.addEventListener('hashchange', () => revealHashRegion(getHashTarget(window.location.hash)));
    window.addEventListener('pageshow', () => revealHashRegion(getHashTarget(window.location.hash)));

    const dateFrom = document.querySelector('#date_from');
    const dateTo = document.querySelector('#date_to');

    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function() {
            dateTo.setAttribute('min', this.value);
        });
    }
});

function checkAvailability(date, time, guests) {
    return fetch('api/check-availability.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            date: date,
            time: time,
            guests: guests
        })
    })
    .then(response => response.json())
    .then(data => {
        return data;
    })
    .catch(error => {
        console.error('Error:', error);
        return { available: false, message: 'Error checking availability' };
    });
}

function formatCurrency(amount) {
    return 'Rs. ' + parseFloat(amount).toFixed(2);
}

function showToast(message, type = 'success') {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }

    const toastId = 'toast-' + Date.now();
    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}
