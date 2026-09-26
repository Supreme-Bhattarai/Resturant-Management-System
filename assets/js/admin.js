document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const sidebarToggle = document.querySelector('.btn-toggle-sidebar');
    const sidebar = document.querySelector('.admin-sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            sidebarToggle.setAttribute('aria-expanded', sidebar.classList.contains('show') ? 'true' : 'false');
        });
    }

    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 && sidebar && sidebarToggle) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
                sidebarToggle.setAttribute('aria-expanded', 'false');
            }
        }
    });

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

    const alerts = document.querySelectorAll('.alert-custom');
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
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    const restoreButtons = document.querySelectorAll('.btn-restore-confirm');
    restoreButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = 'Warning: Restoring a database backup will replace all existing data.\n\nAre you sure you want to continue?';
            if (!confirm(message)) {
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
                    const previewContainer = input.parentElement.querySelector('.image-preview');
                    if (!previewContainer) return;
                    const preview = previewContainer.querySelector('img');
                    const placeholder = previewContainer.querySelector('.placeholder');

                    if (preview) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Wait until the native submit has started before disabling its button.
    // Disabling it during click prevents browsers from sending the POST.
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) return;
            const button = event.submitter;
            if (!button || button.tagName !== 'BUTTON') return;
            window.setTimeout(() => {
                if (event.defaultPrevented) return;
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            }, 0);
        });
    });

    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            if (form) {
                form.submit();
            }
        });
    });

    const searchInputs = document.querySelectorAll('.search-input');
    searchInputs.forEach(input => {
        let timeout;
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }, 500);
        });
    });

    const tableSelect = document.querySelector('#table_select');
    const confirmBtn = document.querySelector('#confirm_booking_btn');

    if (tableSelect && confirmBtn) {
        tableSelect.addEventListener('change', function() {
            if (this.value) {
                confirmBtn.disabled = false;
            } else {
                confirmBtn.disabled = true;
            }
        });
    }

    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            const status = this.value;
            if (status === 'rejected' || status === 'cancelled') {
                if (!confirm(`Are you sure you want to change the status to ${status}?`)) {
                    this.value = this.dataset.previous;
                }
            }
        });
    });

    const bulkSelect = document.querySelector('#bulk_select');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionBtn = document.querySelector('#bulk_action_btn');

    if (bulkSelect) {
        bulkSelect.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionButton();
        });
    }

    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionButton);
    });

    function updateBulkActionButton() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        if (bulkActionBtn) {
            bulkActionBtn.disabled = checkedCount === 0;
        }
    }

    const dateFrom = document.querySelector('#date_from');
    const dateTo = document.querySelector('#date_to');

    if (dateFrom && dateTo) {
        dateFrom.addEventListener('change', function() {
            dateTo.setAttribute('min', this.value);
        });
    }

    const bookingDate = document.querySelector('#booking_date');
    const bookingTime = document.querySelector('#booking_time');
    const guestCount = document.querySelector('#guest_count');
    const tableAvailability = document.querySelector('#table_availability');

    if (bookingDate && bookingTime && guestCount && tableAvailability) {
        function checkTableAvailability() {
            const date = bookingDate.value;
            const time = bookingTime.value;
            const guests = guestCount.value;

            if (date && time && guests) {
                fetch('api/check-table-availability.php', {
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
                    if (data.available) {
                        tableAvailability.innerHTML = `
                            <div class="alert-custom success">
                                <i class="bi bi-check-circle"></i> ${data.tables.length} table(s) available
                            </div>
                        `;
                    } else {
                        tableAvailability.innerHTML = `
                            <div class="alert-custom danger">
                                <i class="bi bi-x-circle"></i> No tables available for this time
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }

        bookingDate.addEventListener('change', checkTableAvailability);
        bookingTime.addEventListener('change', checkTableAvailability);
        guestCount.addEventListener('change', checkTableAvailability);
    }

    const exportButtons = document.querySelectorAll('.btn-export');
    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const format = this.dataset.format;
            const url = this.dataset.url;
            window.location.href = `${url}?format=${format}`;
        });
    });

    const printButtons = document.querySelectorAll('.btn-print');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.print();
        });
    });

    const refreshStatsBtn = document.querySelector('#refresh_stats');
    if (refreshStatsBtn) {
        refreshStatsBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Refreshing...';

            setTimeout(() => {
                location.reload();
            }, 1000);
        });
    }

    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            const form = document.activeElement?.closest('form');
            if (form && form.method.toLowerCase() === 'post') {
                e.preventDefault();
                form.requestSubmit();
            }
        }

        if (e.key === 'Escape') {
            if (sidebar && sidebarToggle && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                sidebarToggle.setAttribute('aria-expanded', 'false');
                sidebarToggle.focus();
            }
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });
        }
    });

    if (typeof Chart !== 'undefined') {
        const bookingChart = document.querySelector('#bookingChart');
        if (bookingChart) {
            new Chart(bookingChart, {
                type: 'line',
                data: {
                    labels: bookingChart.dataset.labels.split(','),
                    datasets: [{
                        label: 'Bookings',
                        data: bookingChart.dataset.data.split(','),
                        borderColor: '#D4AF37',
                        backgroundColor: 'rgba(212, 175, 55, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#b0b0b0'
                            },
                            grid: {
                                color: '#333'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#b0b0b0'
                            },
                            grid: {
                                color: '#333'
                            }
                        }
                    }
                }
            });
        }
    }
});

function showAdminToast(message, type = 'success') {
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

function formatAdminCurrency(amount) {
    return 'Rs. ' + parseFloat(amount).toFixed(2);
}

function exportData(url, format = 'csv') {
    window.location.href = `${url}?format=${format}`;
}
