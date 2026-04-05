<script>
(function () {
    var $modal = $('#playerClubHistoryModal');
    if (!$modal.length) {
        return;
    }
    var $load = $('#playerClubHistoryModalLoading');
    var $err = $('#playerClubHistoryModalError');
    var $content = $('#playerClubHistoryModalContent');

    function fetchUrlForPage(url) {
        if (!url || typeof url !== 'string') {
            return url;
        }
        if (window.location.protocol !== 'https:') {
            return url;
        }
        try {
            var u = new URL(url, window.location.href);
            if (u.protocol === 'http:' && u.host === window.location.host) {
                u.protocol = 'https:';
                return u.href;
            }
        } catch (e) {}
        return url;
    }

    function esc(s) {
        if (s == null) {
            return '';
        }
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function openClubHistoryModal(url) {
        url = fetchUrlForPage(url);
        if (!url) {
            return;
        }
        $err.hide().text('');
        $content.hide().empty();
        $load.show();
        $modal.modal('show');
        $('#playerClubHistoryModalLabel').text(@json(__('Club history & performance')));

        fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) {
            return r.text().then(function (text) {
                var data = {};
                try {
                    data = text ? JSON.parse(text) : {};
                } catch (e) {
                    data = { message: 'Invalid response' };
                }
                return { ok: r.ok, data: data };
            });
        }).then(function (res) {
            $load.hide();
            if (!res.ok) {
                $err.text((res.data && res.data.message) ? res.data.message : 'Could not load.').show();
                return;
            }
            var d = res.data;
            var html = '';
            if (d.player) {
                html += '<h6 class="mb-3">' + esc(d.player.name) + '</h6>';
            }
            html += '<h6 class="text-secondary border-bottom pb-1">' + @json(__('Club history')) + '</h6>';
            if (d.history && d.history.length) {
                html += '<div class="table-responsive mb-4"><table class="table table-sm table-bordered"><thead><tr><th>' + @json(__('When')) + '</th><th>' + @json(__('Event')) + '</th><th>' + @json(__('Club')) + '</th><th>' + @json(__('By')) + '</th><th>' + @json(__('Note')) + '</th></tr></thead><tbody>';
                d.history.forEach(function (h) {
                    html += '<tr><td>' + esc(h.event_at) + '</td><td>' + esc(h.event_label) + '</td><td>' + esc(h.club_name) + '</td><td>' + esc(h.admin_name || '—') + '</td><td class="small">' + esc(h.remark || '—') + '</td></tr>';
                });
                html += '</tbody></table></div>';
            } else {
                html += '<p class="text-muted small mb-4">' + @json(__('No club history recorded yet.')) + '</p>';
            }

            var perf = d.performance || {};
            html += '<h6 class="text-secondary border-bottom pb-1">' + @json(__('Match performance')) + '</h6>';
            if (!perf.available) {
                html += '<p class="text-muted small">' + esc(perf.message || '') + '</p>';
            } else {
                html += '<p class="small mb-2"><strong>' + @json(__('Totals (all matches)')) + ':</strong> ';
                var parts = [];
                if (perf.totals) {
                    Object.keys(perf.totals).forEach(function (k) {
                        parts.push(esc(k) + ': ' + esc(perf.totals[k]));
                    });
                }
                html += (parts.length ? parts.join(', ') : '—') + '</p>';
                if (perf.recent && perf.recent.length) {
                    html += '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>' + @json(__('Date')) + '</th><th>' + @json(__('Competition')) + '</th><th>' + @json(__('Opponent')) + '</th><th>' + @json(__('Event')) + '</th><th>' + @json(__('Min')) + '</th></tr></thead><tbody>';
                    perf.recent.forEach(function (r) {
                        html += '<tr><td>' + esc(r.match_date || '—') + '</td><td>' + esc(r.competition || '—') + '</td><td>' + esc(r.opponent || '—') + '</td><td>' + esc(r.event_label || r.event_type) + '</td><td>' + esc(r.minute_in_match) + '</td></tr>';
                    });
                    html += '</tbody></table></div>';
                } else {
                    html += '<p class="text-muted small mb-0">' + @json(__('No match events yet.')) + '</p>';
                }
            }
            $content.html(html).show();
        }).catch(function () {
            $load.hide();
            $err.text(@json(__('Network error'))).show();
        });
    }

    $(document).on('click', '.js-club-history-performance', function (e) {
        e.preventDefault();
        openClubHistoryModal($(this).data('url'));
    });

    $(document).on('dblclick', '.player-inline-row input.js-player-name-inline[name="name"]', function () {
        var url = $(this).closest('tr').data('club-history-url');
        openClubHistoryModal(url);
    });
})();
</script>
