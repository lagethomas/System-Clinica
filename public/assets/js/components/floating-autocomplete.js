/**
 * Floating Autocomplete Utility
 * Creates a dropdown appended to document.body with position:fixed,
 * guaranteeing visibility regardless of parent overflow rules (modals etc).
 *
 * Usage:
 *   setupFloatingAutocomplete({
 *       inputId: 'tutor-search',
 *       hiddenId: 'tutor_id',
 *       data: myArray,          // client-side array OR null
 *       apiUrl: '/api/search',  // used when data is null
 *       filterType: 'pet',      // filter API results by type
 *       searchKey: 'nome',
 *       displayKey: 'nome',
 *       subKey: 'tutor_nome',
 *       icon: 'user'
 *   });
 */
function setupFloatingAutocomplete(config) {
    const input = document.getElementById(config.inputId);
    const hiddenInput = document.getElementById(config.hiddenId);
    if (!input) return;

    // Remove any old floating dropdown for this input
    const oldDrop = document.getElementById('floating-ac-' + config.inputId);
    if (oldDrop) oldDrop.remove();

    // Create dropdown div appended to body
    const dropdown = document.createElement('div');
    dropdown.id = 'floating-ac-' + config.inputId;
    dropdown.className = 'floating-autocomplete-dropdown';
    document.body.appendChild(dropdown);

    function positionDropdown() {
        const rect = input.getBoundingClientRect();
        dropdown.style.position = 'fixed';
        dropdown.style.top = (rect.bottom + 4) + 'px';
        dropdown.style.left = rect.left + 'px';
        dropdown.style.width = rect.width + 'px';
        dropdown.style.zIndex = '99999';
    }

    function hideDropdown() {
        dropdown.style.display = 'none';
    }

    function showDropdown() {
        positionDropdown();
        dropdown.style.display = 'block';
    }

    input.addEventListener('input', function () {
        if (hiddenInput) hiddenInput.value = '';
        const q = this.value.toLowerCase().trim();

        if (q.length < 1) {
            hideDropdown();
            return;
        }

        // Client-side filtering
        if (config.data && Array.isArray(config.data)) {
            const filtered = config.data.filter(function (item) {
                return (item[config.searchKey] || '').toLowerCase().indexOf(q) !== -1;
            }).slice(0, 8);

            renderResults(filtered.map(function (item) {
                return {
                    id: item.id,
                    name: item[config.displayKey],
                    sub: config.subKey ? (item[config.subKey] || '') : ('ID: #' + item.id)
                };
            }));
        }
        // API-based filtering
        else if (config.apiUrl) {
            clearTimeout(input._acTimeout);
            input._acTimeout = setTimeout(function () {
                fetch(config.apiUrl + '?q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (json) {
                        var items = (json.results || []);
                        if (config.filterType) {
                            items = items.filter(function (r) { return r.type === config.filterType; });
                        }
                        renderResults(items);
                    })
                    .catch(function (err) {
                        console.error('Autocomplete error:', err);
                        dropdown.innerHTML = '<div style="padding:12px;text-align:center;color:var(--text-muted);font-size:13px;">Erro na busca.</div>';
                        showDropdown();
                    });
            }, 250);
        }
    });

    function renderResults(items) {
        if (items.length === 0) {
            dropdown.innerHTML = '<div style="padding:12px;text-align:center;color:var(--text-muted);font-size:13px;">Nenhum resultado encontrado.</div>';
            showDropdown();
            return;
        }

        var icon = config.icon || 'search';
        dropdown.innerHTML = items.map(function (item) {
            var safeName = (item.name || '').replace(/"/g, '&quot;');
            return '<div class="floating-ac-item" data-id="' + item.id + '" data-name="' + safeName + '">' +
                '<div class="floating-ac-icon"><i data-lucide="' + icon + '" class="icon-lucide"></i></div>' +
                '<div class="floating-ac-info">' +
                '<span class="floating-ac-name">' + (item.name || '') + '</span>' +
                '<span class="floating-ac-sub">' + (item.sub || '') + '</span>' +
                '</div></div>';
        }).join('');

        showDropdown();
        if (window.lucide) lucide.createIcons({ root: dropdown });

        dropdown.querySelectorAll('.floating-ac-item').forEach(function (el) {
            el.addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                var name = this.getAttribute('data-name');
                if (hiddenInput) hiddenInput.value = id;
                input.value = name;
                hideDropdown();
            });
        });
    }

    // Hide on outside click
    document.addEventListener('click', function (e) {
        if (e.target !== input && !dropdown.contains(e.target)) {
            hideDropdown();
        }
    });

    // Reposition on scroll inside modal
    var modalBody = document.getElementById('modal-body');
    if (modalBody) {
        modalBody.addEventListener('scroll', positionDropdown);
    }

    // Clean up when modal closes
    var modal = document.getElementById('global-modal');
    if (modal) {
        var observer = new MutationObserver(function () {
            if (!modal.classList.contains('active')) {
                hideDropdown();
                dropdown.remove();
                observer.disconnect();
            }
        });
        observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
    }
}
