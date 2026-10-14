/*
 * CxM Radiostatus Panel - Laufzeit der Panels
 * Play/Pause direkt im Panel (ein gemeinsamer Audio-Player für alle Streams),
 * exakter Fortschritt aus Start- und Endzeit, Lauftext nur wenn nötig,
 * Titelwechsel-Animation nach jedem AJAX-Update.
 */
(function (window, document) {
    'use strict';

    var audio = null;
    var currentId = null;
    var state = 'idle';

    function each(list, fn) {
        for (var i = 0; i < list.length; i++) {
            fn(list[i], i);
        }
    }

    function toggleClass(el, name, on) {
        if (el.classList) {
            el.classList[on ? 'add' : 'remove'](name);
        }
    }

    function syncButtons() {
        each(document.querySelectorAll('[data-cxm-play]'), function (btn) {
            var mine = btn.getAttribute('data-cxm-play') === currentId;
            var playing = mine && state === 'playing';
            var loading = mine && state === 'loading';
            toggleClass(btn, 'is-playing', playing);
            toggleClass(btn, 'is-loading', loading);
            var label = (playing || loading) ? btn.getAttribute('data-label-pause') : btn.getAttribute('data-label-play');
            if (label) {
                btn.setAttribute('aria-label', label);
                btn.setAttribute('title', label);
            }
            btn.setAttribute('aria-pressed', playing || loading ? 'true' : 'false');
            var root = btn.closest ? btn.closest('.cxm-x') : null;
            if (root) {
                toggleClass(root, 'is-playing', playing);
            }
        });
    }

    function setState(next) {
        state = next;
        syncButtons();
    }

    function stop() {
        if (audio) {
            audio.pause();
            audio.removeAttribute('src');
            audio.load();
        }
        currentId = null;
        setState('idle');
    }

    function play(id, url) {
        if (!audio) {
            audio = new Audio();
            audio.preload = 'none';
            audio.addEventListener('playing', function () { setState('playing'); });
            audio.addEventListener('waiting', function () { if (state !== 'idle') { setState('loading'); } });
            audio.addEventListener('error', function () { if (state !== 'idle' && audio.getAttribute('src')) { stop(); } });
        }
        currentId = id;
        setState('loading');
        audio.src = url;
        var promise = audio.play();
        if (promise && promise.then) {
            promise.then(null, function () {
                if (state === 'loading') {
                    stop();
                }
            });
        }
    }

    document.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest ? e.target.closest('[data-cxm-play]') : null;
        if (!btn) {
            return;
        }
        var url = btn.getAttribute('data-stream');
        if (!url || url === '#') {
            return;
        }
        e.preventDefault();
        var id = btn.getAttribute('data-cxm-play');
        if (currentId === id && state !== 'idle') {
            stop();
        } else {
            play(id, url);
        }
    });

    function pad(n) {
        return (n < 10 ? '0' : '') + n;
    }

    function tick() {
        var now = Date.now() / 1000;
        each(document.querySelectorAll('.cxm-x-progress'), function (el) {
            var starts = parseInt(el.getAttribute('data-starts'), 10) || 0;
            var ends = parseInt(el.getAttribute('data-ends'), 10) || 0;
            var fill = el.querySelector('.cxm-x-progress-fill');
            var time = el.querySelector('[data-cxm-remaining]');
            var left = Math.max(0, Math.round(ends - now));
            var pct = 0;
            if (starts > 0 && ends > starts) {
                pct = Math.max(0, Math.min(100, (now - starts) / (ends - starts) * 100));
            } else if (ends > now) {
                pct = Math.max(0, Math.min(100, (1 - (ends - now) / 600) * 100));
            }
            if (fill) {
                fill.style.transform = 'scaleX(' + (pct / 100).toFixed(4) + ')';
            }
            if (time) {
                time.textContent = '-' + Math.floor(left / 60) + ':' + pad(left % 60);
            }
            toggleClass(el, 'is-done', left <= 0);
        });
        // Klassische Designs
        each(document.querySelectorAll('.cxm-rs-progress-bar'), function (el) {
            var ends = parseInt(el.getAttribute('data-ends'), 10) || 0;
            var diff = ends - Math.floor(now);
            if (ends > 0 && diff > 0 && diff < 600) {
                el.style.width = Math.round((1 - diff / 600) * 100) + '%';
            }
        });
    }

    function marquee(scope) {
        each((scope || document).querySelectorAll('.cxm-x-marquee'), function (el) {
            var inner = el.firstElementChild;
            if (!inner) {
                return;
            }
            toggleClass(el, 'is-scrolling', false);
            var overflow = inner.scrollWidth - el.clientWidth;
            if (overflow > 4) {
                el.style.setProperty('--cxm-x-shift', (-overflow - 16) + 'px');
                el.style.setProperty('--cxm-x-duration', Math.max(6, Math.round(overflow / 18) + 4) + 's');
                toggleClass(el, 'is-scrolling', true);
            }
        });
    }

    function songs(scope) {
        var map = {};
        each(scope.querySelectorAll('.cxm-x[data-cxm-id]'), function (el) {
            map[el.getAttribute('data-cxm-id')] = el.getAttribute('data-song');
        });
        return map;
    }

    function render(container, html) {
        var before = songs(container);
        container.innerHTML = html;
        each(container.querySelectorAll('.cxm-x[data-cxm-id]'), function (el) {
            var id = el.getAttribute('data-cxm-id');
            if (before.hasOwnProperty(id) && before[id] !== el.getAttribute('data-song')) {
                toggleClass(el, 'is-changed', true);
                window.setTimeout(function () { toggleClass(el, 'is-changed', false); }, 1200);
            }
        });
        refresh(container);
    }

    function refresh(scope) {
        syncButtons();
        marquee(scope);
        tick();
    }

    window.cxmRs = { render: render, refresh: refresh };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { refresh(document); });
    } else {
        refresh(document);
    }
    window.setInterval(tick, 1000);
    window.addEventListener('resize', function () { marquee(document); });
})(window, document);
