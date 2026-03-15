(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // AJAX filtering for archive pages
        var filterForm = document.querySelector('.wpma-filter-form');
        if (!filterForm) return;

        var grid = document.querySelector('.wpma-grid');
        if (!grid) return;

        filterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            loadFilteredMedia(1);
        });

        function loadFilteredMedia(page) {
            var formData = new FormData();
            formData.append('action', 'wpma_filter');
            formData.append('nonce', wpmaAjax.nonce);
            formData.append('page', page);

            var searchInput = filterForm.querySelector('[name="s"]');
            if (searchInput) formData.append('search', searchInput.value);

            var typeSelect = filterForm.querySelector('[name="media_type"]');
            if (typeSelect) formData.append('media_type', typeSelect.value);

            var catSelect = filterForm.querySelector('[name="media_category"]');
            if (catSelect) formData.append('media_category', catSelect.value);

            grid.style.opacity = '0.5';

            fetch(wpmaAjax.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
                .then(function (response) { return response.json(); })
                .then(function (result) {
                    grid.style.opacity = '1';
                    if (!result.success) return;

                    grid.innerHTML = '';
                    var items = result.data.items;

                    if (items.length === 0) {
                        grid.innerHTML = '<p class="wpma-no-results">Keine Medieneinträge gefunden.</p>';
                        return;
                    }

                    items.forEach(function (item) {
                        var card = document.createElement('div');
                        card.className = 'wpma-card ' + (item.isAudio ? 'wpma-card--audio' : 'wpma-card--image');

                        var mediaHtml = '';
                        if (item.thumbnail) {
                            mediaHtml = '<img src="' + escHtml(item.thumbnail) + '" class="wpma-card__img" alt="' + escHtml(item.title) + '">';
                        } else if (item.isAudio) {
                            mediaHtml = '<div class="wpma-card__audio-icon"><svg viewBox="0 0 24 24" width="48" height="48" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg></div>';
                        }

                        var tagsHtml = '';
                        if (item.tags && item.tags.length) {
                            tagsHtml = '<div class="wpma-card__tags">';
                            item.tags.forEach(function (tag) {
                                tagsHtml += '<span class="wpma-tag">' + escHtml(tag) + '</span>';
                            });
                            tagsHtml += '</div>';
                        }

                        card.innerHTML =
                            '<a href="' + escHtml(item.permalink) + '" class="wpma-card__link">' +
                            '<div class="wpma-card__media">' + mediaHtml + '</div>' +
                            '<div class="wpma-card__body">' +
                            '<h3 class="wpma-card__title">' + escHtml(item.title) + '</h3>' +
                            (item.author ? '<p class="wpma-card__meta">' + escHtml(item.author) + '</p>' : '') +
                            tagsHtml +
                            '</div></a>';

                        grid.appendChild(card);
                    });
                })
                .catch(function () {
                    grid.style.opacity = '1';
                });
        }

        function escHtml(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }
    });
})();
