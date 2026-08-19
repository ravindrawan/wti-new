document.addEventListener('DOMContentLoaded', function () {
    // Mobile nav toggle (public site)
    var navToggle = document.querySelector('.nav-toggle');
    var navLinks = document.querySelector('.nav-links');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function () {
            navLinks.classList.toggle('open');
        });
    }

    // Admin sidebar toggle (mobile)
    var adminToggle = document.querySelector('.admin-toggle');
    var sidebar = document.querySelector('.admin-sidebar');
    if (adminToggle && sidebar) {
        adminToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
    }

    // Booking form: prevent choosing a past date
    var dateInput = document.getElementById('event_date');
    if (dateInput) {
        var today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }

    // Booking form: show the selected hall's price + AC availability
    var hallSelect = document.getElementById('hall_id');
    var priceBox = document.getElementById('hall-price-preview');
    if (hallSelect && priceBox) {
        var updatePreview = function () {
            var opt = hallSelect.options[hallSelect.selectedIndex];
            if (!opt || !opt.value) { priceBox.innerHTML = ''; return; }
            var ac = opt.getAttribute('data-ac');
            var nonAc = opt.getAttribute('data-nonac');
            var cap = opt.getAttribute('data-cap');
            var hasAc = opt.getAttribute('data-hasac') === '1';
            var html = '<div class="notice"><strong>' + opt.text + '</strong><br>';
            html += 'Capacity: up to ' + cap + ' persons<br>';
            if (ac) html += 'With A/C: Rs. ' + ac + ' / day<br>';
            if (nonAc) html += 'Without A/C: Rs. ' + nonAc + ' / day';
            html += '</div>';
            priceBox.innerHTML = html;

            var acField = document.getElementById('ac_required_wrap');
            if (acField) acField.style.display = hasAc ? 'flex' : 'none';
        };
        hallSelect.addEventListener('change', updatePreview);
        updatePreview();
    }
});
