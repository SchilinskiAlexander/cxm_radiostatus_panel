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

$result = dbquery("SELECT rs_id, rs_name, rs_station, rs_stream_url, rs_cache, rs_status_wb, rs_access_wb FROM ".DB_CXM_RADIOSTATUS." WHERE ".groupaccess("rs_access")." AND rs_status='1' AND rs_id='".$stream_id."'");
if (!dbrows($result)) {
    die();
}

$data = dbarray($result);
$cache = array();
$cache_file = CXM_RADIOSTATUS.'cache/stream_'.$data['rs_id'].'_'.$data['rs_cache'].'.php';
if (!file_exists($cache_file) || filemtime($cache_file) <= (time() - (int)$cxm_radiostatus_settings['reload_cache'])) {
    require_once CXM_RADIOSTATUS.'cxm_radiostatus_class.php';
    $api = new LautFmAPI($data['rs_station'], $cxm_radiostatus_settings['api_base']);
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
    $download->set_filename($data['rs_station'].'.'.$mode);
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

$default_image = CXM_RADIOSTATUS.'images/autodj.gif';
$station_image = !empty($cache['station_img']) ? $cache['station_img'] : $default_image;
$onair_name = !empty($cache['onair_name']) ? $cache['onair_name'] : 'AutoDJ';
$song_title = !empty($cache['song']) ? $cache['song'] : $data['rs_name'];
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
        'onair_name' => $onair_name,
        'image'      => $station_image,
        'is_live'    => !empty($cache['is_live']) ? 1 : 0,
        'listener'   => isset($cache['listner']) ? (int)$cache['listner'] : 0,
    ));
    exit;
}

if ($type !== 'popup' && $type !== 'wb' && $type !== 'embed') {
    redirect(BASEDIR.'index.php');
}

$base_player_url = $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS).'cxm_radiostatus_player.php?id='.(int)$data['rs_id'];
$popup_js = "window.open(this.href,'cxmrs_player','height=440,width=500,top=' + Math.round((screen.height - 440) / 2) + ',left=' + Math.round((screen.width - 500) / 2) + ',resizable=yes'); return false;";
$player_links = '<a href="'.$base_player_url.'&amp;p=m3u" title="'.htmlspecialchars($locale['cxmrs_player_m3u']).'">'
    .'<img src="'.htmlspecialchars(CXM_RADIOSTATUS.'images/player_m3u.svg').'" alt="'.htmlspecialchars($locale['cxmrs_player_m3u']).'" width="22" height="22" /></a>'
    .'<a href="'.$base_player_url.'&amp;p=pls" title="'.htmlspecialchars($locale['cxmrs_player_pls']).'">'
    .'<img src="'.htmlspecialchars(CXM_RADIOSTATUS.'images/player_pls.svg').'" alt="'.htmlspecialchars($locale['cxmrs_player_pls']).'" width="22" height="22" /></a>';
if ($type === 'embed') {
    $player_links .= '<a href="'.$base_player_url.'&amp;typ=popup" onclick="'.$popup_js.'" target="_blank" title="'.htmlspecialchars($locale['cxmrs_player_popup']).'">'
        .'<img src="'.htmlspecialchars(CXM_RADIOSTATUS.'images/player_popup.svg').'" alt="'.htmlspecialchars($locale['cxmrs_player_popup']).'" width="22" height="22" /></a>';
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
);
?><!DOCTYPE html>
<html lang="<?php echo isset($locale['xml_lang']) ? htmlspecialchars($locale['xml_lang']) : 'de'; ?>">
<head>
    <meta charset="<?php echo $locale['charset']; ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars($type === 'popup' ? $song_title.' | '.$data['rs_name'] : $data['rs_name']); ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo CXM_RADIOSTATUS; ?>css/cxm_radiostatus.css" />
    <?php if ($type === 'wb'): ?>
    <script type="text/javascript" src="<?php echo INCLUDES; ?>jquery/jquery.js"></script>
    <script type="text/javascript">
    function cxmSubmitPopupWB() {
        var form = $("#cxm_wb_popup_form");
        $.ajax({
            url: "<?php echo $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS); ?>cxm_radiostatus_inc.php",
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
<body class="cxm-player-body">
    <div class="cxm-player cxm-form">
        <h1><?php echo htmlspecialchars($locale['cxmrswb_title']); ?></h1>
        <?php if (!$data['rs_status_wb'] || !checkgroup($data['rs_access_wb']) || !$has_live_moderator): ?>
        <div class="cxm-message"><?php echo htmlspecialchars($locale['cxmrswb_err_closed']); ?></div>
        <?php else: ?>
        <form id="cxm_wb_popup_form" method="post" action="" onsubmit="return cxmSubmitPopupWB();">
            <input type="hidden" name="wb" value="<?php echo (int)$data['rs_id']; ?>" />
            <table>
                <tr>
                    <td><?php echo $locale['cxmrswb_name']; ?> *</td>
                    <td><input type="text" name="name" class="textbox"<?php echo iMEMBER ? ' value="'.htmlspecialchars($userdata['user_name']).'"' : ''; ?> /></td>
                </tr>
                <tr>
                    <td><?php echo $locale['cxmrswb_wunsch']; ?></td>
                    <td><input type="text" name="wunsch" class="textbox" /></td>
                </tr>
                <tr>
                    <td><?php echo $locale['cxmrswb_nachricht']; ?></td>
                    <td><textarea name="nachricht" rows="4" class="textbox"></textarea></td>
                </tr>
                <?php if (!iMEMBER): ?>
                <tr>
                    <td><?php echo $locale['cxmrswb_captcha']; ?> *</td>
                    <td><?php echo cxm_rs_captcha_html('cxm_wb_popup_code'); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="2" align="right"><input type="submit" value="<?php echo htmlspecialchars($locale['cxmrswb_send']); ?>" class="button" /></td>
                </tr>
            </table>
        </form>
        <div id="cxm_wb_popup_result" class="cxm-message cxm-rs-hidden"></div>
        <?php endif; ?>
    </div>
</body>
<?php else: ?>
<body class="cxm-hp-body<?php echo $type === 'embed' ? ' cxm-hp-body-embed' : ''; ?>">
    <div class="cxm-hp<?php echo $type === 'embed' ? ' cxm-hp-embed' : ''; ?>" id="cxm-hp">
        <div class="cxm-hp-backdrop" id="cxm-hp-backdrop" style="background-image: url('<?php echo htmlspecialchars($station_image, ENT_QUOTES); ?>');"></div>

        <div class="cxm-hp-head">
            <div class="cxm-hp-cover">
                <img id="cxm-hp-image" src="<?php echo htmlspecialchars($station_image); ?>" alt=""
                     onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($default_image); ?>';" />
            </div>
            <div class="cxm-hp-meta">
                <div class="cxm-hp-station"><?php echo htmlspecialchars($data['rs_name']); ?></div>
                <div class="cxm-hp-onair">
                    <span class="cxm-hp-onair-label"><?php echo htmlspecialchars($locale['cxmrs_onair']); ?></span>
                    <span class="cxm-hp-onair-name" id="cxm-hp-onair"><?php echo htmlspecialchars($onair_name); ?></span>
                </div>
                <div class="cxm-hp-badges">
                    <span class="cxm-hp-live<?php echo empty($cache['is_live']) ? ' cxm-hp-hidden' : ''; ?>" id="cxm-hp-live"><?php echo htmlspecialchars($locale['cxmrs_live']); ?></span>
                    <span class="cxm-hp-listener<?php echo empty($cache['listner']) ? ' cxm-hp-hidden' : ''; ?>" id="cxm-hp-listener-wrap">
                        <span id="cxm-hp-listener"><?php echo isset($cache['listner']) ? (int)$cache['listner'] : 0; ?></span> <?php echo htmlspecialchars($locale['cxmrs_listener']); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="cxm-hp-song">
            <span class="cxm-hp-song-label"><?php echo htmlspecialchars(rtrim($locale['cxmrs_now'], ': ')); ?></span>
            <div class="cxm-hp-song-viewport" id="cxm-hp-song-viewport">
                <span class="cxm-hp-song-text" id="cxm-hp-song"><?php echo htmlspecialchars($song_title); ?></span>
            </div>
        </div>

        <div class="cxm-hp-controls">
            <button type="button" class="cxm-hp-play" id="cxm-hp-play" aria-label="<?php echo htmlspecialchars($locale['cxmrs_player_play']); ?>" title="<?php echo htmlspecialchars($locale['cxmrs_player_play']); ?>">
                <svg class="cxm-hp-icon-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5.14v13.72a1 1 0 0 0 1.5.86l11-6.86a1 1 0 0 0 0-1.72l-11-6.86A1 1 0 0 0 8 5.14z"/></svg>
                <svg class="cxm-hp-icon-pause" viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                <svg class="cxm-hp-icon-spinner" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a9 9 0 1 0 9 9h-2.5A6.5 6.5 0 1 1 12 5.5V3z"/></svg>
            </button>
            <div class="cxm-hp-eq" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
            <div class="cxm-hp-volume-wrap">
                <button type="button" class="cxm-hp-mute" id="cxm-hp-mute" aria-label="<?php echo htmlspecialchars($locale['cxmrs_player_mute']); ?>" title="<?php echo htmlspecialchars($locale['cxmrs_player_mute']); ?>">
                    <svg class="cxm-hp-icon-volume" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10v4a1 1 0 0 0 1 1h3l4.3 3.6A1 1 0 0 0 13 17.8V6.2a1 1 0 0 0-1.7-.8L7 9H4a1 1 0 0 0-1 1zm13.5 2A4.5 4.5 0 0 0 14 8v8a4.5 4.5 0 0 0 2.5-4zM14 3.2v2.1a7 7 0 0 1 0 13.4v2.1a9 9 0 0 0 0-17.6z"/></svg>
                    <svg class="cxm-hp-icon-muted" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10v4a1 1 0 0 0 1 1h3l4.3 3.6A1 1 0 0 0 13 17.8V6.2a1 1 0 0 0-1.7-.8L7 9H4a1 1 0 0 0-1 1zm13.6-1.4L15.2 10l2 2-2 2 1.4 1.4 2-2 2 2 1.4-1.4-2-2 2-2-1.4-1.4-2 2-2-2z"/></svg>
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
        var songViewport = document.getElementById('cxm-hp-song-viewport');
        var onairEl = document.getElementById('cxm-hp-onair');
        var liveEl = document.getElementById('cxm-hp-live');
        var listenerWrap = document.getElementById('cxm-hp-listener-wrap');
        var listenerEl = document.getElementById('cxm-hp-listener');
        var imageEl = document.getElementById('cxm-hp-image');
        var backdrop = document.getElementById('cxm-hp-backdrop');
        var errorEl = document.getElementById('cxm-hp-error');
        var audio = new Audio();
        var state = 'idle';
        var currentSong = songEl.textContent;
        var currentImage = imageEl.getAttribute('src');

        audio.preload = 'none';

        function hasClass(el, name) {
            return (' ' + el.className + ' ').indexOf(' ' + name + ' ') > -1;
        }

        function toggleClass(el, name, on) {
            if (on && !hasClass(el, name)) {
                el.className += ' ' + name;
            } else if (!on && hasClass(el, name)) {
                el.className = (' ' + el.className + ' ').replace(' ' + name + ' ', ' ').replace(/^\s+|\s+$/g, '');
            }
        }

        function setState(next) {
            state = next;
            toggleClass(root, 'is-playing', next === 'playing');
            toggleClass(root, 'is-loading', next === 'loading');
            var label = next === 'idle' ? cfg.play : cfg.pause;
            playBtn.setAttribute('aria-label', label);
            playBtn.setAttribute('title', label);
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
            toggleClass(songEl, 'is-scrolling', false);
            if (songEl.offsetWidth > songViewport.clientWidth) {
                songEl.style.animationDuration = Math.max(8, Math.round(songEl.textContent.length / 3)) + 's';
                toggleClass(songEl, 'is-scrolling', true);
            }
        }

        function setSong(text) {
            if (text === currentSong) {
                return;
            }
            currentSong = text;
            toggleClass(songEl, 'is-changing', true);
            window.setTimeout(function () {
                songEl.textContent = text;
                updateMarquee();
                toggleClass(songEl, 'is-changing', false);
            }, 300);
            if (cfg.popup) {
                document.title = text + ' | ' + cfg.station;
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
                setSong(info.song || cfg.station);
                onairEl.textContent = info.onair_name || 'AutoDJ';
                toggleClass(liveEl, 'cxm-hp-hidden', !info.is_live);
                listenerEl.textContent = info.listener || 0;
                toggleClass(listenerWrap, 'cxm-hp-hidden', !info.listener);
                setImage(info.image);
            };
            xhr.send(null);
        }

        updateMarquee();
        window.setInterval(refresh, cfg.interval);

        if (cfg.autoplay) {
            play();
        }
    })();
    </script>
</body>
<?php endif; ?>
</html>
