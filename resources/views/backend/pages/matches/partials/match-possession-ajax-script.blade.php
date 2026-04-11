(function () {
    var root = document.getElementById('fp-possession-ajax-root');
    if (!root) return;

    var csrf = root.getAttribute('data-csrf') || '';

    /**
     * If the page is loaded over HTTPS but Laravel emitted http:// URLs (wrong APP_URL, cached routes, or proxy),
     * upgrade same-host requests to https so fetch() never hits mixed-content blocking in production.
     */
    function ensureHttpsWhenPageIsHttps(url) {
        if (!url || typeof url !== 'string') {
            return url;
        }
        if (typeof window === 'undefined' || window.location.protocol !== 'https:') {
            return url;
        }
        try {
            var u = new URL(url, window.location.href);
            if (u.protocol !== 'http:') {
                return url;
            }
            // Same hostname as the current page (ignore port mismatches vs strict u.host === window.location.host).
            if (u.hostname === window.location.hostname) {
                u.protocol = 'https:';
                return u.href;
            }
        } catch (e) { /* ignore */ }
        return url;
    }

    var urlStart = ensureHttpsWhenPageIsHttps(root.getAttribute('data-url-start'));
    var urlPossession = ensureHttpsWhenPageIsHttps(root.getAttribute('data-url-possession'));
    var urlPause = ensureHttpsWhenPageIsHttps(root.getAttribute('data-url-pause'));
    var urlResume = ensureHttpsWhenPageIsHttps(root.getAttribute('data-url-resume'));
    var urlReset = ensureHttpsWhenPageIsHttps(root.getAttribute('data-url-reset'));
    var urlStatusEnd = ensureHttpsWhenPageIsHttps(root.getAttribute('data-url-status-end'));
    var urlStatusOngoing = ensureHttpsWhenPageIsHttps(root.getAttribute('data-url-status-ongoing'));
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
    var elBtnEnd = document.getElementById('fp-btn-match-end');
    var elBtnReopen = document.getElementById('fp-btn-match-reopen');
    var elBtnHome = document.getElementById('fp-btn-possession-home');
    var elBtnAway = document.getElementById('fp-btn-possession-away');
    var elBtnNeutral = document.getElementById('fp-btn-possession-neutral');
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
        var n = (sum.neutral_seconds || 0);
        var hp = sum.home_pct != null ? ' (' + sum.home_pct + '%)' : '';
        var ap = sum.away_pct != null ? ' (' + sum.away_pct + '%)' : '';
        var html = escapeHtml(homeName) + ' ' + fmtDur(h) + hp + ' · ' + escapeHtml(awayName) + ' ' + fmtDur(a) + ap;
        if (n > 0) {
            html += ' · <span class="text-muted">{{ __('Ball out of play') }}: ' + fmtDur(n) + '</span>';
        }
        if (u > 0) {
            html += ' · <span class="text-muted">{{ __('Before first switch') }}: ' + fmtDur(u) + '</span>';
        }
        elMini.innerHTML = html;
    }

    function renderPossessionViz(sum) {
        var elRing = document.getElementById('fp-poss-donut-ring');
        var elHomeInner = document.getElementById('fp-poss-pct-home-inner');
        var elAwayInner = document.getElementById('fp-poss-pct-away-inner');
        if (!elRing || !elHomeInner || !elAwayInner) return;

        var hp = sum && sum.home_pct != null ? parseFloat(sum.home_pct, 10) : NaN;
        var ap = sum && sum.away_pct != null ? parseFloat(sum.away_pct, 10) : NaN;
        var hSec = sum && sum.home_seconds != null ? parseInt(sum.home_seconds, 10) : 0;
        var aSec = sum && sum.away_seconds != null ? parseInt(sum.away_seconds, 10) : 0;
        if (isNaN(hSec)) hSec = 0;
        if (isNaN(aSec)) aSec = 0;

        if (!isNaN(hp) && !isNaN(ap)) {
            elHomeInner.innerHTML = '<span class="fp-poss-pct-num">' + hp + '</span><span class="fp-poss-pct-sup">%</span>';
            elAwayInner.innerHTML = '<span class="fp-poss-pct-num">' + ap + '</span><span class="fp-poss-pct-sup">%</span>';
            elRing.style.setProperty('--home-share', String(Math.max(0, Math.min(1, hp / 100))));
            elRing.classList.remove('fp-poss-donut-ring--empty');
        } else if ((hSec + aSec) > 0) {
            var t = hSec + aSec;
            var hpf = Math.round(1000 * hSec / t) / 10;
            var apf = Math.round(1000 * aSec / t) / 10;
            elHomeInner.innerHTML = '<span class="fp-poss-pct-num">' + hpf + '</span><span class="fp-poss-pct-sup">%</span>';
            elAwayInner.innerHTML = '<span class="fp-poss-pct-num">' + apf + '</span><span class="fp-poss-pct-sup">%</span>';
            elRing.style.setProperty('--home-share', String(hSec / t));
            elRing.classList.remove('fp-poss-donut-ring--empty');
        } else {
            elHomeInner.innerHTML = '<span class="fp-poss-pct-num">\u2014</span>';
            elAwayInner.innerHTML = '<span class="fp-poss-pct-num">\u2014</span>';
            elRing.style.setProperty('--home-share', '0.5');
            elRing.classList.add('fp-poss-donut-ring--empty');
        }
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

        var matchCode = root.getAttribute('data-match-status') || 'NOT_STARTED';
        if (p.match_status != null && String(p.match_status) !== '') {
            matchCode = String(p.match_status);
            root.setAttribute('data-match-status', matchCode);
            renderMatchStatus(matchCode);
        }

        var inProgress = matchCode === 'ONGOING';

        if (elBtnStart) elBtnStart.style.display = startedAtIso ? 'none' : 'inline-block';
        if (elBtnPause) elBtnPause.style.display = (startedAtIso && !isPaused && inProgress) ? 'inline-block' : 'none';
        if (elBtnResume) elBtnResume.style.display = (startedAtIso && isPaused && inProgress) ? 'inline-block' : 'none';
        if (elBtnReset) elBtnReset.style.display = (startedAtIso || (p.possessions && p.possessions.length)) ? 'inline-block' : 'none';
        if (elBtnEnd) elBtnEnd.style.display = inProgress ? 'inline-block' : 'none';
        if (elBtnReopen) elBtnReopen.style.display = (matchCode === 'END') ? 'inline-block' : 'none';
        if (elPausedLabel) elPausedLabel.style.display = isPaused ? 'block' : 'none';

        var canBall = !!startedAtIso && !isPaused && inProgress;
        if (elBtnHome) elBtnHome.disabled = !canBall;
        if (elBtnAway) elBtnAway.disabled = !canBall;
        if (elBtnNeutral) elBtnNeutral.disabled = !canBall;

        if (p.summary) {
            renderMiniStats(p.summary);
            renderPossessionViz(p.summary);
            try {
                root.setAttribute('data-possession-summary', JSON.stringify(p.summary));
            } catch (e) {}
        }
        if (p.possessions) renderLog(p.possessions);

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
        url = ensureHttpsWhenPageIsHttps(url);
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
            return r.text().then(function (text) {
                var data = {};
                if (text) {
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        var snippet = String(text).replace(/\s+/g, ' ').trim().substring(0, 180);
                        data = {
                            success: false,
                            message: r.status === 419
                                ? '{{ __('Page expired. Refresh and try again.') }}'
                                : (r.status >= 500
                                    ? '{{ __('Server error. Try again or contact support.') }}'
                                    : (snippet || '{{ __('Invalid server response') }}'))
                        };
                    }
                } else if (!r.ok) {
                    data = { success: false, message: '{{ __('Request failed') }}' + ' (' + r.status + ')' };
                }
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
            }).catch(function (err) {
                showToast((err && err.message) ? err.message : '{{ __('Network error') }}', true);
            });
        });
    }

    function possessionClick(clubId) {
        postJson(urlPossession, { club_id: parseInt(clubId, 10) }).then(function (res) {
            handleResponse(res, '{{ __('Could not record possession.') }}');
        }).catch(function (err) {
            showToast((err && err.message) ? err.message : '{{ __('Network error') }}', true);
        });
    }

    if (elBtnHome) elBtnHome.addEventListener('click', function () { possessionClick(homeClubId); });
    if (elBtnAway) elBtnAway.addEventListener('click', function () { possessionClick(awayClubId); });

    if (elBtnNeutral) {
        elBtnNeutral.addEventListener('click', function () {
            postJson(urlPossession, { neutral: true }).then(function (res) {
                handleResponse(res, '{{ __('Could not record ball out of play.') }}');
            }).catch(function (err) {
                showToast((err && err.message) ? err.message : '{{ __('Network error') }}', true);
            });
        });
    }

    if (elBtnPause) {
        elBtnPause.addEventListener('click', function () {
            postJson(urlPause, {}).then(function (res) {
                handleResponse(res, '{{ __('Could not pause.') }}');
            }).catch(function (err) {
                showToast((err && err.message) ? err.message : '{{ __('Network error') }}', true);
            });
        });
    }

    if (elBtnResume) {
        elBtnResume.addEventListener('click', function () {
            postJson(urlResume, {}).then(function (res) {
                handleResponse(res, '{{ __('Could not resume.') }}');
            }).catch(function (err) {
                showToast((err && err.message) ? err.message : '{{ __('Network error') }}', true);
            });
        });
    }

    if (elBtnReset) {
        elBtnReset.addEventListener('click', function () {
            if (!confirm('{{ __('Clear match start time, timer pause state, and all possession log rows for this match?') }}')) return;
            postJson(urlReset, {}).then(function (res) {
                handleResponse(res, '{{ __('Could not reset.') }}');
            }).catch(function (err) {
                showToast((err && err.message) ? err.message : '{{ __('Network error') }}', true);
            });
        });
    }

    if (elBtnEnd && urlStatusEnd) {
        elBtnEnd.addEventListener('click', function () {
            if (!confirm('{{ __('Mark this match as ended? You can set it back to ongoing later if needed.') }}')) return;
            postJson(urlStatusEnd, {}).then(function (res) {
                handleResponse(res, '{{ __('Could not end match.') }}');
            }).catch(function (err) {
                showToast((err && err.message) ? err.message : '{{ __('Network error') }}', true);
            });
        });
    }

    if (elBtnReopen && urlStatusOngoing) {
        elBtnReopen.addEventListener('click', function () {
            if (!confirm('{{ __('Set match status back to ongoing?') }}')) return;
            postJson(urlStatusOngoing, {}).then(function (res) {
                handleResponse(res, '{{ __('Could not update match status.') }}');
            }).catch(function (err) {
                showToast((err && err.message) ? err.message : '{{ __('Network error') }}', true);
            });
        });
    }

    renderMatchStatus(root.getAttribute('data-match-status') || 'NOT_STARTED');

    try {
        var initSumRaw = root.getAttribute('data-possession-summary');
        if (initSumRaw) {
            renderPossessionViz(JSON.parse(initSumRaw));
        }
    } catch (e) {}

    tick();
    setInterval(tick, 1000);
})();
