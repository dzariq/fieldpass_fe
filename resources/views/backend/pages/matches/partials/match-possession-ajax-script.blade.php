(function () {
    var root = document.getElementById('fp-possession-ajax-root');
    if (!root) return;

    var csrf = root.getAttribute('data-csrf') || '';
    var urlStart = root.getAttribute('data-url-start');
    var urlPossession = root.getAttribute('data-url-possession');
    var urlPause = root.getAttribute('data-url-pause');
    var urlResume = root.getAttribute('data-url-resume');
    var urlReset = root.getAttribute('data-url-reset');
    var homeClubId = root.getAttribute('data-home-club-id');
    var awayClubId = root.getAttribute('data-away-club-id');
    var homeName = root.getAttribute('data-home-name') || '';
    var awayName = root.getAttribute('data-away-name') || '';

    var elTimer = document.getElementById('match-live-timer');
    var elToast = document.getElementById('fp-possession-toast');
    var elKickoff = document.getElementById('fp-possession-kickoff-line');
    var elKickAt = document.getElementById('fp-kickoff-at');
    var elPausedLabel = document.getElementById('fp-timer-paused-label');
    var elBtnStart = document.getElementById('fp-btn-match-start');
    var elBtnPause = document.getElementById('fp-btn-timer-pause');
    var elBtnResume = document.getElementById('fp-btn-timer-resume');
    var elBtnReset = document.getElementById('fp-btn-possession-reset');
    var elBtnHome = document.getElementById('fp-btn-possession-home');
    var elBtnAway = document.getElementById('fp-btn-possession-away');
    var elMini = document.getElementById('fp-mini-stats-inner');
    var elTbody = document.getElementById('fp-possession-log-tbody');

    var basePlayingSeconds = parseInt(root.getAttribute('data-playing-seconds') || '0', 10);
    if (isNaN(basePlayingSeconds)) basePlayingSeconds = 0;
    var lastSyncMs = Date.now();
    var isPaused = root.getAttribute('data-is-paused') === '1';
    var startedAtIso = root.getAttribute('data-started-at') || '';

    function renderMatchStatus(code) {
        var el = document.getElementById('fp-match-status-badge');
        if (!el) return;
        var labels = {};
        try {
            labels = JSON.parse(root.getAttribute('data-status-labels') || '{}');
        } catch (e) {}
        el.textContent = labels[code] || code;
        var map = { NOT_STARTED: 'badge-secondary', ONGOING: 'badge-success', END: 'badge-dark', POSTPONED: 'badge-warning' };
        el.className = 'badge fp-match-status-badge ' + (map[code] || 'badge-secondary');
    }

    function showToast(msg, isError) {
        if (!elToast || !msg) return;
        elToast.style.display = 'block';
        elToast.className = 'small mb-2 alert ' + (isError ? 'alert-danger' : 'alert-success');
        elToast.textContent = msg;
        clearTimeout(showToast._t);
        showToast._t = setTimeout(function () {
            elToast.style.display = 'none';
        }, 4000);
    }

    function fmtDur(sec) {
        sec = Math.max(0, Math.floor(sec));
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function getCurrentPlayingSeconds() {
        if (!startedAtIso) {
            return null;
        }
        if (isPaused) {
            return basePlayingSeconds;
        }
        return basePlayingSeconds + Math.floor((Date.now() - lastSyncMs) / 1000);
    }

    function dispatchPlayingSeconds(sec) {
        try {
            window.dispatchEvent(new CustomEvent('fp-match-playing-seconds', { detail: { seconds: sec } }));
        } catch (e) {}
    }

    function renderMiniStats(sum) {
        if (!elMini || !sum) return;
        var h = (sum.home_seconds || 0);
        var a = (sum.away_seconds || 0);
        var u = (sum.unknown_seconds || 0);
        var hp = sum.home_pct != null ? ' (' + sum.home_pct + '%)' : '';
        var ap = sum.away_pct != null ? ' (' + sum.away_pct + '%)' : '';
        var html = escapeHtml(homeName) + ' ' + fmtDur(h) + hp + ' · ' + escapeHtml(awayName) + ' ' + fmtDur(a) + ap;
        if (u > 0) {
            html += ' · <span class="text-muted">{{ __('Before first switch') }}: ' + fmtDur(u) + '</span>';
        }
        elMini.innerHTML = html;
    }

    function renderLog(rows) {
        if (!elTbody) return;
        elTbody.innerHTML = '';
        if (!rows || !rows.length) {
            var tr = document.createElement('tr');
            tr.className = 'fp-possession-empty';
            tr.innerHTML = '<td colspan="3" class="text-muted text-center">{{ __('No possession entries yet.') }}</td>';
            elTbody.appendChild(tr);
            return;
        }
        rows.forEach(function (r) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + escapeHtml(r.event_at || '—') + '</td><td>' + escapeHtml(r.club_name || '—') + '</td><td>' + escapeHtml(r.admin_name || '—') + '</td>';
            elTbody.appendChild(tr);
        });
    }

    function escapeHtml(s) {
        if (s == null) return '';
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function syncFromPayload(p) {
        if (!p || p.success === false) return;
        startedAtIso = p.started_at || '';
        root.setAttribute('data-started-at', startedAtIso);
        isPaused = !!p.is_paused;
        root.setAttribute('data-is-paused', isPaused ? '1' : '0');
        basePlayingSeconds = parseInt(p.playing_seconds || 0, 10);
        if (isNaN(basePlayingSeconds)) basePlayingSeconds = 0;
        lastSyncMs = Date.now();

        if (elKickoff) {
            if (startedAtIso) {
                var d = new Date(startedAtIso);
                var txt = isNaN(d.getTime()) ? startedAtIso : d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0') + ' ' + String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0') + ':' + String(d.getSeconds()).padStart(2, '0');
                elKickoff.innerHTML = '<strong>{{ __('Kickoff recorded') }}:</strong> <span id="fp-kickoff-at">' + escapeHtml(txt) + '</span>';
            } else {
                elKickoff.textContent = '{{ __('Kickoff not recorded yet.') }}';
            }
        }

        if (elBtnStart) elBtnStart.style.display = startedAtIso ? 'none' : 'inline-block';
        if (elBtnPause) elBtnPause.style.display = (startedAtIso && !isPaused) ? 'inline-block' : 'none';
        if (elBtnResume) elBtnResume.style.display = (startedAtIso && isPaused) ? 'inline-block' : 'none';
        if (elBtnReset) elBtnReset.style.display = (startedAtIso || (p.possessions && p.possessions.length)) ? 'inline-block' : 'none';
        if (elPausedLabel) elPausedLabel.style.display = isPaused ? 'block' : 'none';

        var canBall = !!startedAtIso;
        if (elBtnHome) elBtnHome.disabled = !canBall;
        if (elBtnAway) elBtnAway.disabled = !canBall;

        if (p.summary) renderMiniStats(p.summary);
        if (p.possessions) renderLog(p.possessions);

        if (p.match_status != null && String(p.match_status) !== '') {
            root.setAttribute('data-match-status', String(p.match_status));
            renderMatchStatus(String(p.match_status));
        }

        if (!startedAtIso) {
            document.querySelectorAll('input.js-match-minute-sync').forEach(function (el) {
                delete el.dataset.fpMinuteUserEdited;
                if (document.activeElement !== el) {
                    el.value = '';
                }
            });
        }

        dispatchPlayingSeconds(getCurrentPlayingSeconds());
    }

    function tick() {
        if (!elTimer) return;
        var sec = getCurrentPlayingSeconds();
        if (sec === null) {
            elTimer.textContent = '—';
        } else {
            elTimer.textContent = fmtDur(sec);
        }
        dispatchPlayingSeconds(sec);
    }

    function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf
            },
            body: body ? JSON.stringify(body) : JSON.stringify({})
        }).then(function (r) {
            return r.json().then(function (data) {
                return { ok: r.ok, status: r.status, data: data };
            });
        });
    }

    function handleResponse(res, fallbackErr) {
        var d = res.data;
        if (!res.ok || !d || d.success === false) {
            showToast((d && d.message) ? d.message : fallbackErr, true);
            return;
        }
        if (d.message) showToast(d.message, false);
        syncFromPayload(d);
    }

    if (elBtnStart) {
        elBtnStart.addEventListener('click', function () {
            postJson(urlStart, {}).then(function (res) {
                handleResponse(res, '{{ __('Could not start match.') }}');
            }).catch(function () {
                showToast('{{ __('Network error') }}', true);
            });
        });
    }

    function possessionClick(clubId) {
        postJson(urlPossession, { club_id: parseInt(clubId, 10) }).then(function (res) {
            handleResponse(res, '{{ __('Could not record possession.') }}');
        }).catch(function () {
            showToast('{{ __('Network error') }}', true);
        });
    }

    if (elBtnHome) elBtnHome.addEventListener('click', function () { possessionClick(homeClubId); });
    if (elBtnAway) elBtnAway.addEventListener('click', function () { possessionClick(awayClubId); });

    if (elBtnPause) {
        elBtnPause.addEventListener('click', function () {
            postJson(urlPause, {}).then(function (res) {
                handleResponse(res, '{{ __('Could not pause.') }}');
            }).catch(function () {
                showToast('{{ __('Network error') }}', true);
            });
        });
    }

    if (elBtnResume) {
        elBtnResume.addEventListener('click', function () {
            postJson(urlResume, {}).then(function (res) {
                handleResponse(res, '{{ __('Could not resume.') }}');
            }).catch(function () {
                showToast('{{ __('Network error') }}', true);
            });
        });
    }

    if (elBtnReset) {
        elBtnReset.addEventListener('click', function () {
            if (!confirm('{{ __('Clear match start time, timer pause state, and all possession log rows for this match?') }}')) return;
            postJson(urlReset, {}).then(function (res) {
                handleResponse(res, '{{ __('Could not reset.') }}');
            }).catch(function () {
                showToast('{{ __('Network error') }}', true);
            });
        });
    }

    renderMatchStatus(root.getAttribute('data-match-status') || 'NOT_STARTED');

    tick();
    setInterval(tick, 1000);
})();
