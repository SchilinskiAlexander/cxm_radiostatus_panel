<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: cxm_radiostatus_player.php
| Author: Alexaxander Schilinski
| Homepage: https://schilinskialexander.com
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once '../../maincore.php';

if (!defined('CXM_RADIOSTATUS')) {
    define('CXM_RADIOSTATUS', INFUSIONS.'cxm_radiostatus_panel/');
}

require_once CXM_RADIOSTATUS.'infusion_db.php';
require_once CXM_RADIOSTATUS.'cxm_radiostatus_captcha.php';
if (file_exists(CXM_RADIOSTATUS.'locale/'.LOCALESET.'index.php')) {
    require CXM_RADIOSTATUS.'locale/'.LOCALESET.'index.php';
} else {
    require CXM_RADIOSTATUS.'locale/German/index.php';
}

if (!isset($_GET['id']) || !isnum($_GET['id'])) { die(); }

$stream_id = (int)$_GET['id'];
$mode = isset($_GET['p']) ? stripinput($_GET['p']) : '';
$type = isset($_GET['typ']) ? stripinput($_GET['typ']) : '';

$result = dbquery("SELECT * FROM ".DB_CXM_RADIOSTATUS." WHERE ".groupaccess("rs_access")." AND rs_status='1' AND rs_id='".$stream_id."'");
if (!dbrows($result)) {
    die();
}

$data = dbarray($result);
$cache = array();
$cache_file = CXM_RADIOSTATUS.'cache/stream_'.$data['rs_id'].'_'.$data['rs_cache'].'.php';
if (!file_exists($cache_file) || @filemtime($cache_file) <= (time() - (int)$cxm_radiostatus_settings['reload_cache'])) {
    require_once CXM_RADIOSTATUS.'cxm_radiostatus_class.php';
    $api = cxm_rs_create_api($data);
    $res = $api->makeCache($data['rs_id'], $data['rs_cache'], $data['rs_stream_url']);
    $cache_dir = dirname($cache_file);
    $f = is_dir($cache_dir) && is_writable($cache_dir) ? @fopen($cache_file, 'w') : false;
    if ($f !== false) {
        fwrite($f, $res);
        fclose($f);
    }
    eval('?>'.$res.'<?php ');
}

if (file_exists($cache_file)) {
    require $cache_file;
}

$has_live_moderator = !empty($cache['is_live'])
    && !empty($cache['has_moderator'])
    && !empty($cache['onair_name'])
    && $cache['onair_name'] !== 'AutoDJ';

$stream_url = '';
if (!empty($data['rs_stream_url'])) {
    $stream_url = $data['rs_stream_url'];
} elseif (isset($cache['stream_url'])) {
    $stream_url = $cache['stream_url'];
}

if ($stream_url === '') {
    die();
}

if ($mode === 'm3u' || $mode === 'pls') {
    require_once INCLUDES.'class.httpdownload.php';
    $download = new httpdownload();
    if ($mode === 'm3u') {
        $content = "#EXTM3U\n#EXTINF:-1,".$data['rs_name']."\n".$stream_url."\n";
        $download->set_mime('audio/x-mpegurl');
    } else {
        $content = "[playlist]\nNumberOfEntries=1\nFile1=".$stream_url."\nTitle1=".$data['rs_name']."\nLength1=-1\nVersion=2\n";
        $download->set_mime('audio/x-scpls');
    }
    if (ob_get_level()) {
        ob_end_clean();
    }
    $download->set_bydata($content);
    $download->use_resume = true;
    $download->set_filename(preg_replace('/[^A-Za-z0-9_-]+/', '_', $data['rs_name']).'.'.$mode);
    $download->download();
    exit;
}

function cxm_hp_utf8($value) {
    global $locale;

    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[$key] = cxm_hp_utf8($item);
        }
        return $value;
    }
    if (!is_string($value)) {
        return $value;
    }
    if (function_exists('mb_check_encoding') && mb_check_encoding($value, 'UTF-8')) {
        return $value;
    }
    $charset = !empty($locale['charset']) ? $locale['charset'] : 'ISO-8859-1';
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($value, 'UTF-8', $charset);
    }
    return utf8_encode($value);
}

function cxm_hp_json($value) {
    $flags = defined('JSON_HEX_TAG') ? (JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : 0;
    return json_encode(cxm_hp_utf8($value), $flags);
}

require_once CXM_RADIOSTATUS.'cxm_radiostatus_themes.php';

$default_image = CXM_RADIOSTATUS.'images/autodj.gif';
$station_image = !empty($cache['station_img']) ? $cache['station_img'] : $default_image;
$onair_name = !empty($cache['onair_name']) ? $cache['onair_name'] : 'AutoDJ';
if ($onair_name === 'AutoDJ') {
    $station_image = $default_image;
}
$cover_image = !empty($cache['artist_thumb']) ? $cache['artist_thumb'] : $station_image;
$song_title = !empty($cache['song']) ? $cache['song'] : $data['rs_name'];
$song_name = !empty($cache['title']) ? $cache['title'] : $song_title;
$song_artist = !empty($cache['title']) && !empty($cache['artist_name']) ? $cache['artist_name'] : '';
$is_live = !empty($cache['is_live']) && !empty($cache['has_moderator']);
$starts_at = isset($cache['starts_at']) ? (int)$cache['starts_at'] : 0;
$ends_at = isset($cache['ends_at']) ? (int)$cache['ends_at'] : 0;
$skin = cxm_rs_skin_key(!empty($data['rs_player_skin']) ? $data['rs_player_skin'] : (isset($data['rs_skin']) ? $data['rs_skin'] : 'modern'));
$reload_ms = max(15000, (int)$cxm_radiostatus_settings['reload_main']);

// ?typ=json  Aktuelle Titel-Infos fuer den HTML-Player
if ($type === 'json') {
    if (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    echo cxm_hp_json(array(
        'song'       => $song_title,
        'title'      => $song_name,
        'artist'     => $song_artist,
        'onair_name' => $onair_name,
        'image'      => $station_image,
        'cover'      => $cover_image,
        'is_live'    => $is_live ? 1 : 0,
        'listener'   => isset($cache['listner']) ? (int)$cache['listner'] : 0,
        'starts'     => $starts_at,
        'ends'       => $ends_at,
    ));
    exit;
}

if ($type !== 'popup' && $type !== 'wb' && $type !== 'embed') {
    redirect(BASEDIR.'index.php');
}

$base_player_url = cxm_rs_web_path().'cxm_radiostatus_player.php?id='.(int)$data['rs_id'];
$popup_js = "window.open(this.href,'cxmrs_player','height=480,width=520,top=' + Math.round((screen.height - 480) / 2) + ',left=' + Math.round((screen.width - 520) / 2) + ',resizable=yes'); return false;";
$player_links = '<a href="'.$base_player_url.'&amp;p=m3u" title="'.htmlspecialchars($locale['cxmrs_player_m3u']).'">M3U</a>'
    .'<a href="'.$base_player_url.'&amp;p=pls" title="'.htmlspecialchars($locale['cxmrs_player_pls']).'">PLS</a>';
if ($type === 'embed') {
    $player_links .= '<a href="'.$base_player_url.'&amp;typ=popup" onclick="'.$popup_js.'" target="_blank" title="'.htmlspecialchars($locale['cxmrs_player_popup']).'" aria-label="'.htmlspecialchars($locale['cxmrs_player_popup']).'">'
        .cxm_rs_icon('popup').'</a>';
}

$player_config = array(
    'stream'   => $stream_url,
    'json'     => $base_player_url.'&typ=json',
    'interval' => $reload_ms,
    'autoplay' => $type === 'popup',
    'fallback' => $default_image,
    'station'  => $data['rs_name'],
    'popup'    => $type === 'popup',
    'play'     => $locale['cxmrs_player_play'],
    'pause'    => $locale['cxmrs_player_pause'],
    'live'     => $locale['cxmrs_live'],
);
?><!DOCTYPE html>
<html lang="<?php echo isset($locale['xml_lang']) ? htmlspecialchars($locale['xml_lang']) : 'de'; ?>">
<head>
    <meta charset="<?php echo $locale['charset']; ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="color-scheme" content="dark" />
    <title><?php echo htmlspecialchars($type === 'popup' ? $song_title.' | '.$data['rs_name'] : $data['rs_name']); ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo CXM_RADIOSTATUS; ?>css/cxm_radiostatus.css" />
    <?php if ($type === 'wb'): ?>
    <script type="text/javascript" src="<?php echo INCLUDES; ?>jquery/jquery.js"></script>
    <script type="text/javascript">
    function cxmSubmitPopupWB() {
        var form = $("#cxm_wb_popup_form");
        $.ajax({
            url: "<?php echo cxm_rs_web_path(); ?>cxm_radiostatus_inc.php",
            type: "POST",
            data: form.serialize(),
            success: function (response) {
                $("#cxm_wb_popup_result").html(response).show();
                <?php echo cxm_rs_captcha_reset_js('"#cxm_wb_popup_form"'); ?>

            }
        });
        return false;
    }
    </script>
    <?php endif; ?>
</head>
<?php if ($type === 'wb'): ?>
<body class="cxm-hp-body cxm-skin-<?php echo $skin; ?>">
    <div class="cxm-hp cxm-hp-wb">
        <div class="cxm-hp-wb-head">
            <h1><?php echo htmlspecialchars($locale['cxmrswb_title']); ?></h1>
            <p><?php echo htmlspecialchars($data['rs_name']); ?> &middot; <?php echo htmlspecialchars($onair_name); ?></p>
        </div>
        <?php if (!$data['rs_status_wb'] || !checkgroup($data['rs_access_wb']) || !$has_live_moderator): ?>
        <div class="cxm-hp-message"><?php echo htmlspecialchars($locale['cxmrswb_err_closed']); ?></div>
        <?php else: ?>
        <form id="cxm_wb_popup_form" class="cxm-hp-form" method="post" action="" onsubmit="return cxmSubmitPopupWB();">
            <input type="hidden" name="wb" value="<?php echo (int)$data['rs_id']; ?>" />
            <label for="cxm_wb_popup_name"><?php echo $locale['cxmrswb_name']; ?> <span class="cxm-rs-required">*</span></label>
            <input type="text" id="cxm_wb_popup_name" name="name" maxlength="100" class="textbox"<?php echo iMEMBER ? ' value="'.htmlspecialchars($userdata['user_name']).'"' : ''; ?> />
            <label for="cxm_wb_popup_wunsch"><?php echo $locale['cxmrswb_wunsch']; ?></label>
            <input type="text" id="cxm_wb_popup_wunsch" name="wunsch" maxlength="255" class="textbox" />
            <label for="cxm_wb_popup_nachricht"><?php echo $locale['cxmrswb_nachricht']; ?></label>
            <textarea id="cxm_wb_popup_nachricht" name="nachricht" rows="4" class="textbox"></textarea>
            <?php if (!iMEMBER): ?>
            <div class="cxm-hp-captcha">
                <span><?php echo $locale['cxmrswb_captcha']; ?> <span class="cxm-rs-required">*</span></span>
                <?php echo cxm_rs_captcha_html('cxm_wb_popup_code'); ?>
            </div>
            <?php endif; ?>
            <button type="submit" class="cxm-hp-submit"><?php echo cxm_rs_icon('wish'); ?><span><?php echo htmlspecialchars($locale['cxmrswb_send']); ?></span></button>
        </form>
        <div id="cxm_wb_popup_result" class="cxm-hp-message cxm-rs-hidden" role="status"></div>
        <?php endif; ?>
    </div>
</body>
<?php else: ?>
<body class="cxm-hp-body cxm-skin-<?php echo $skin; ?><?php echo $type === 'embed' ? ' cxm-hp-body-embed' : ''; ?>">
    <div class="cxm-hp<?php echo $type === 'embed' ? ' cxm-hp-embed' : ''; ?><?php echo $is_live ? ' is-live' : ''; ?>" id="cxm-hp">
        <div class="cxm-hp-backdrop" id="cxm-hp-backdrop" style="background-image: url('<?php echo htmlspecialchars($cover_image, ENT_QUOTES); ?>');"></div>

        <div class="cxm-hp-head">
            <div class="cxm-hp-cover">
                <img id="cxm-hp-image" src="<?php echo htmlspecialchars($cover_image); ?>" alt=""
                     onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($default_image); ?>';" />
            </div>
            <div class="cxm-hp-meta">
                <div class="cxm-hp-station"><?php echo htmlspecialchars($data['rs_name']); ?></div>
                <div class="cxm-hp-onair" id="cxm-hp-onair-wrap">
                    <span class="cxm-hp-onair-light" aria-hidden="true"></span>
                    <span class="cxm-hp-live<?php echo $is_live ? '' : ' cxm-hp-hidden'; ?>" id="cxm-hp-live"><?php echo htmlspecialchars($locale['cxmrs_live']); ?></span>
                    <span class="cxm-hp-onair-name" id="cxm-hp-onair"><?php echo htmlspecialchars($onair_name); ?></span>
                </div>
                <div class="cxm-hp-listener<?php echo empty($cache['listner']) ? ' cxm-hp-hidden' : ''; ?>" id="cxm-hp-listener-wrap">
                    <?php echo cxm_rs_icon('listener'); ?><b id="cxm-hp-listener"><?php echo isset($cache['listner']) ? (int)$cache['listner'] : 0; ?></b> <?php echo htmlspecialchars($locale['cxmrs_listener']); ?>
                </div>
            </div>
        </div>

        <div class="cxm-hp-song" id="cxm-hp-plate">
            <span class="cxm-hp-song-label"><?php echo htmlspecialchars(rtrim($locale['cxmrs_now'], ': ')); ?></span>
            <div class="cxm-hp-song-viewport" id="cxm-hp-song-viewport">
                <span class="cxm-hp-song-text" id="cxm-hp-song"><?php echo htmlspecialchars($song_name); ?></span>
            </div>
            <div class="cxm-hp-song-artist<?php echo $song_artist === '' ? ' cxm-hp-hidden' : ''; ?>" id="cxm-hp-artist"><?php echo htmlspecialchars($song_artist); ?></div>
        </div>

        <div class="cxm-hp-progress<?php echo $ends_at > 0 ? '' : ' cxm-hp-hidden'; ?>" id="cxm-hp-progress" data-starts="<?php echo $starts_at; ?>" data-ends="<?php echo $ends_at; ?>">
            <div class="cxm-hp-progress-track"><span id="cxm-hp-progress-fill"></span></div>
            <span class="cxm-hp-progress-time" id="cxm-hp-progress-time"></span>
        </div>

        <div class="cxm-hp-controls">
            <button type="button" class="cxm-hp-play" id="cxm-hp-play" aria-label="<?php echo htmlspecialchars($locale['cxmrs_player_play']); ?>" title="<?php echo htmlspecialchars($locale['cxmrs_player_play']); ?>">
                <svg class="cxm-hp-icon-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.52.85l10.4-6.5a1 1 0 0 0 0-1.7L9.52 4.65A1 1 0 0 0 8 5.5z"/></svg>
                <svg class="cxm-hp-icon-pause" viewBox="0 0 24 24" aria-hidden="true"><rect x="6.5" y="5" width="4" height="14" rx="1"/><rect x="13.5" y="5" width="4" height="14" rx="1"/></svg>
            </button>
            <div class="cxm-hp-eq" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
            <div class="cxm-hp-volume-wrap">
                <button type="button" class="cxm-hp-mute" id="cxm-hp-mute" aria-label="<?php echo htmlspecialchars($locale['cxmrs_player_mute']); ?>" title="<?php echo htmlspecialchars($locale['cxmrs_player_mute']); ?>">
                    <svg class="cxm-hp-icon-volume" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9.5h3.2L12 5.6v12.8l-4.8-3.9H4z"/><path class="cxm-hp-wave" d="M15.5 9a4 4 0 0 1 0 6M18 6.5a7.5 7.5 0 0 1 0 11"/></svg>
                    <svg class="cxm-hp-icon-muted" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9.5h3.2L12 5.6v12.8l-4.8-3.9H4z"/><path class="cxm-hp-wave" d="M16 9.5l5 5M21 9.5l-5 5"/></svg>
                </button>
                <input type="range" class="cxm-hp-volume" id="cxm-hp-volume" min="0" max="100" value="80" aria-label="<?php echo htmlspecialchars($locale['cxmrs_player_volume']); ?>" />
            </div>
        </div>
        <div class="cxm-hp-error cxm-hp-hidden" id="cxm-hp-error" role="alert"><?php echo htmlspecialchars($locale['cxmrs_player_error']); ?></div>

        <div class="cxm-hp-links">
            <?php echo $player_links; ?>
        </div>
    </div>

    <script type="text/javascript">
    (function () {
        var cfg = <?php echo cxm_hp_json($player_config); ?>;
        var root = document.getElementById('cxm-hp');
        var playBtn = document.getElementById('cxm-hp-play');
        var muteBtn = document.getElementById('cxm-hp-mute');
        var volume = document.getElementById('cxm-hp-volume');
        var songEl = document.getElementById('cxm-hp-song');
        var artistEl = document.getElementById('cxm-hp-artist');
        var plate = document.getElementById('cxm-hp-plate');
        var songViewport = document.getElementById('cxm-hp-song-viewport');
        var onairEl = document.getElementById('cxm-hp-onair');
        var liveEl = document.getElementById('cxm-hp-live');
        var listenerWrap = document.getElementById('cxm-hp-listener-wrap');
        var listenerEl = document.getElementById('cxm-hp-listener');
        var imageEl = document.getElementById('cxm-hp-image');
        var backdrop = document.getElementById('cxm-hp-backdrop');
        var errorEl = document.getElementById('cxm-hp-error');
        var progress = document.getElementById('cxm-hp-progress');
        var progressFill = document.getElementById('cxm-hp-progress-fill');
        var progressTime = document.getElementById('cxm-hp-progress-time');
        var audio = new Audio();
        var state = 'idle';
        var currentSong = songEl.textContent;
        var currentImage = imageEl.getAttribute('src');

        audio.preload = 'none';

        function toggleClass(el, name, on) {
            el.classList[on ? 'add' : 'remove'](name);
        }

        function setState(next) {
            state = next;
            toggleClass(root, 'is-playing', next === 'playing');
            toggleClass(root, 'is-loading', next === 'loading');
            var label = next === 'idle' ? cfg.play : cfg.pause;
            playBtn.setAttribute('aria-label', label);
            playBtn.setAttribute('title', label);
            playBtn.setAttribute('aria-pressed', next === 'idle' ? 'false' : 'true');
        }

        function play() {
            toggleClass(errorEl, 'cxm-hp-hidden', true);
            setState('loading');
            audio.src = cfg.stream;
            var promise = audio.play();
            if (promise && promise.then) {
                promise.then(null, function () {
                    if (state === 'loading') {
                        setState('idle');
                    }
                });
            }
        }

        function stop() {
            audio.pause();
            audio.removeAttribute('src');
            audio.load();
            setState('idle');
        }

        playBtn.onclick = function () {
            if (state === 'idle') {
                play();
            } else {
                stop();
            }
        };

        audio.addEventListener('playing', function () { setState('playing'); });
        audio.addEventListener('waiting', function () { if (state !== 'idle') { setState('loading'); } });
        audio.addEventListener('error', function () {
            if (state !== 'idle' && audio.getAttribute('src')) {
                setState('idle');
                toggleClass(errorEl, 'cxm-hp-hidden', false);
            }
        });

        function applyVolume(value) {
            value = Math.max(0, Math.min(100, parseInt(value, 10) || 0));
            volume.value = value;
            audio.volume = value / 100;
            volume.style.setProperty('--cxm-hp-vol', value + '%');
            toggleClass(root, 'is-muted', audio.muted || value === 0);
            try { window.localStorage.setItem('cxm_hp_volume', value); } catch (e) {}
        }

        volume.oninput = volume.onchange = function () {
            audio.muted = false;
            applyVolume(volume.value);
        };

        muteBtn.onclick = function () {
            audio.muted = !audio.muted;
            toggleClass(root, 'is-muted', audio.muted || parseInt(volume.value, 10) === 0);
        };

        var storedVolume = null;
        try { storedVolume = window.localStorage.getItem('cxm_hp_volume'); } catch (e) {}
        applyVolume(storedVolume !== null ? storedVolume : 80);

        // Lauftext nur, wenn der Titel nicht in die Zeile passt
        function updateMarquee() {
            toggleClass(songViewport, 'is-scrolling', false);
            var overflow = songEl.scrollWidth - songViewport.clientWidth;
            if (overflow > 4) {
                songViewport.style.setProperty('--cxm-x-shift', (-overflow - 16) + 'px');
                songViewport.style.setProperty('--cxm-x-duration', Math.max(6, Math.round(overflow / 18) + 4) + 's');
                toggleClass(songViewport, 'is-scrolling', true);
            }
        }

        function pad(n) {
            return (n < 10 ? '0' : '') + n;
        }

        function tick() {
            var starts = parseInt(progress.getAttribute('data-starts'), 10) || 0;
            var ends = parseInt(progress.getAttribute('data-ends'), 10) || 0;
            var now = Date.now() / 1000;
            toggleClass(progress, 'cxm-hp-hidden', ends <= 0);
            if (ends <= 0) {
                return;
            }
            var pct = starts > 0 && ends > starts ? (now - starts) / (ends - starts) * 100 : (1 - (ends - now) / 600) * 100;
            var left = Math.max(0, Math.round(ends - now));
            progressFill.style.transform = 'scaleX(' + (Math.max(0, Math.min(100, pct)) / 100).toFixed(4) + ')';
            progressTime.textContent = left > 0 ? '-' + Math.floor(left / 60) + ':' + pad(left % 60) : '';
        }

        function setSong(title, artist, full) {
            if (full === currentSong) {
                return;
            }
            currentSong = full;
            songEl.textContent = title;
            artistEl.textContent = artist;
            toggleClass(artistEl, 'cxm-hp-hidden', !artist);
            toggleClass(root, 'is-changed', false);
            void root.offsetWidth;
            toggleClass(root, 'is-changed', true);
            updateMarquee();
            if (cfg.popup) {
                document.title = full + ' | ' + cfg.station;
            }
        }

        function setImage(src) {
            if (!src || src === currentImage) {
                return;
            }
            currentImage = src;
            imageEl.onerror = function () { this.onerror = null; this.src = cfg.fallback; };
            imageEl.src = src;
            backdrop.style.backgroundImage = 'url("' + src.replace(/"/g, '%22') + '")';
        }

        function refresh() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', cfg.json + '&_=' + new Date().getTime(), true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4 || xhr.status !== 200) {
                    return;
                }
                var info;
                try { info = JSON.parse(xhr.responseText); } catch (e) { return; }
                if (!info) {
                    return;
                }
                setSong(info.title || info.song || cfg.station, info.artist || '', info.song || cfg.station);
                onairEl.textContent = info.onair_name || 'AutoDJ';
                toggleClass(liveEl, 'cxm-hp-hidden', !info.is_live);
                toggleClass(root, 'is-live', !!info.is_live);
                listenerEl.textContent = info.listener || 0;
                toggleClass(listenerWrap, 'cxm-hp-hidden', !info.listener);
                progress.setAttribute('data-starts', info.starts || 0);
                progress.setAttribute('data-ends', info.ends || 0);
                setImage(info.cover || info.image);
                tick();
            };
            xhr.send(null);
        }

        updateMarquee();
        tick();
        window.setInterval(tick, 1000);
        window.setInterval(refresh, cfg.interval);

        if (cfg.autoplay) {
            play();
        }
    })();
    </script>
</body>
<?php endif; ?>
</html>
