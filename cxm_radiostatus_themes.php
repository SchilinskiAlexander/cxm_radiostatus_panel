<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: cxm_radiostatus_themes.php
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
if (!defined('IN_FUSION')) { die('Access Denied'); }

/*
 * Designs = Layout (rs_theme) + Stil (rs_skin).
 * Layouts ab 10 nutzen theme/layout_<key>.php und funktionieren in Seiten- und Hauptpanels.
 * Das klassische Banner-Design (3) bleibt unverändert: theme/main_3.php, in Seitenpanels theme/side_1.php.
 * Streams mit dem entfernten Design 1 bekommen automatisch "Cover groß".
 */

function cxm_rs_layouts() {
    global $locale;

    return array(
        10 => array('key' => 'cover',   'label' => $locale['cxmrsd_layout_cover'],   'desc' => $locale['cxmrsd_layout_cover_desc']),
        11 => array('key' => 'compact', 'label' => $locale['cxmrsd_layout_compact'], 'desc' => $locale['cxmrsd_layout_compact_desc']),
        12 => array('key' => 'studio',  'label' => $locale['cxmrsd_layout_studio'],  'desc' => $locale['cxmrsd_layout_studio_desc']),
        14 => array('key' => 'host',    'label' => $locale['cxmrsd_layout_host'],    'desc' => $locale['cxmrsd_layout_host_desc']),
        15 => array('key' => 'show',    'label' => $locale['cxmrsd_layout_show'],    'desc' => $locale['cxmrsd_layout_show_desc']),
        16 => array('key' => 'duo',     'label' => $locale['cxmrsd_layout_duo'],     'desc' => $locale['cxmrsd_layout_duo_desc']),
        13 => array('key' => 'history', 'label' => $locale['cxmrsd_layout_history'], 'desc' => $locale['cxmrsd_layout_history_desc']),
        3  => array('key' => 'classic3', 'label' => $locale['cxmrsd_layout_classic3'], 'desc' => $locale['cxmrsd_layout_classic3_desc'], 'classic' => true),
    );
}

function cxm_rs_skins() {
    global $locale;

    return array(
        'modern'    => array('label' => $locale['cxmrsd_skin_modern'],    'desc' => $locale['cxmrsd_skin_modern_desc'],    'swatch' => array('#17181f', '#262833', '#ff5a5f', '#f4f4f6')),
        'hitparade' => array('label' => $locale['cxmrsd_skin_hitparade'], 'desc' => $locale['cxmrsd_skin_hitparade_desc'], 'swatch' => array('#1426a8', '#0b145e', '#ffd23f', '#ff3fa4')),
        'jukebox'   => array('label' => $locale['cxmrsd_skin_jukebox'],   'desc' => $locale['cxmrsd_skin_jukebox_desc'],   'swatch' => array('#3a0d14', '#c9ccd3', '#f6efdc', '#d7262e')),
        'mixtape'   => array('label' => $locale['cxmrsd_skin_mixtape'],   'desc' => $locale['cxmrsd_skin_mixtape_desc'],   'swatch' => array('#1b1b1d', '#efe9dc', '#2847b8', '#ee6a1f')),
        'vinyl'     => array('label' => $locale['cxmrsd_skin_vinyl'],     'desc' => $locale['cxmrsd_skin_vinyl_desc'],     'swatch' => array('#141312', '#2a2826', '#e9b949', '#f2ede4')),
        'autoradio' => array('label' => $locale['cxmrsd_skin_autoradio'], 'desc' => $locale['cxmrsd_skin_autoradio_desc'], 'swatch' => array('#0c0d0e', '#1b1a12', '#ffb21a', '#5fd3ff')),
        'strand'    => array('label' => $locale['cxmrsd_skin_strand'],    'desc' => $locale['cxmrsd_skin_strand_desc'],    'swatch' => array('#0f4a52', '#f4e4c1', '#ff7a45', '#ffd166')),
        'stuebchen' => array('label' => $locale['cxmrsd_skin_stuebchen'], 'desc' => $locale['cxmrsd_skin_stuebchen_desc'], 'swatch' => array('#3a2316', '#5a3a24', '#d6a243', '#f3e6cf')),
    );
}

function cxm_rs_skin_key($skin) {
    $skins = cxm_rs_skins();
    return isset($skins[$skin]) ? $skin : 'modern';
}

function cxm_rs_layout_id($theme) {
    $layouts = cxm_rs_layouts();
    return isset($layouts[(int)$theme]) ? (int)$theme : 10;
}

/**
 * Template-Datei für Layout und Panel-Position
 */
function cxm_rs_theme_file($theme, $panel_typ) {
    $theme = cxm_rs_layout_id($theme);
    $is_side = ($panel_typ == 1 || $panel_typ == 4);
    if ($theme === 3) {
        $file = CXM_RADIOSTATUS.'theme/'.($is_side ? 'side_' : 'main_').$theme.'.php';
        if (!file_exists($file)) {
            $file = CXM_RADIOSTATUS.'theme/side_1.php';
        }
        return $file;
    }
    $layouts = cxm_rs_layouts();
    return CXM_RADIOSTATUS.'theme/layout_'.$layouts[$theme]['key'].'.php';
}

function cxm_rs_icon($name) {
    $paths = array(
        'play'     => '<path d="M8 5.5v13a1 1 0 0 0 1.52.85l10.4-6.5a1 1 0 0 0 0-1.7L9.52 4.65A1 1 0 0 0 8 5.5z" fill="currentColor" stroke="none"/>',
        'pause'    => '<rect x="6.5" y="5" width="4" height="14" rx="1" fill="currentColor" stroke="none"/><rect x="13.5" y="5" width="4" height="14" rx="1" fill="currentColor" stroke="none"/>',
        'popup'    => '<path d="M14 4h6v6"/><path d="M20 4l-8 8"/><path d="M18 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4"/>',
        'download' => '<path d="M12 4v11"/><path d="M7 10l5 5 5-5"/><path d="M5 20h14"/>',
        'listener' => '<path d="M4 15v-3a8 8 0 0 1 16 0v3"/><rect x="3" y="14" width="4" height="6" rx="1.5"/><rect x="17" y="14" width="4" height="6" rx="1.5"/>',
        'wish'     => '<path d="M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H9l-5 4z"/><path d="M9 10h6"/>',
        'note'     => '<path d="M9 18V5l11-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="17" cy="16" r="3"/>',
        'code'     =>'<path d="M9 7l-5 5 5 5"/><path d="M15 7l5 5-5 5"/>',
        'up'       => '<path d="M6 15l6-6 6 6"/>',
        'down'     => '<path d="M6 9l6 6 6-6"/>',
        'edit'     => '<path d="M4 20h4L19 9a2.83 2.83 0 0 0-4-4L4 16z"/><path d="M13.5 6.5l4 4"/>',
        'power'    => '<path d="M12 3v8"/><path d="M6.4 6.6a8 8 0 1 0 11.2 0"/>',
        'trash'    => '<path d="M4 7h16"/><path d="M10 11v6M14 11v6"/><path d="M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12"/><path d="M9 7V4h6v3"/>',
        'close'    => '<path d="M6 6l12 12M18 6L6 18"/>',
    );
    if (!isset($paths[$name])) {
        return '';
    }
    return '<svg class="cxm-x-icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'.$paths[$name].'</svg>';
}

/**
 * Werte für die neuen Layouts (alles bereits HTML-escaped)
 */
function cxm_rs_view($cache, $panel_typ) {
    global $locale;

    $e = function ($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES);
    };
    $default_img = CXM_RADIOSTATUS.'images/autodj.gif';
    $station_img = !empty($cache['station_img']) ? $cache['station_img'] : $default_img;
    $cover = !empty($cache['artist_thumb']) ? $cache['artist_thumb'] : $station_img;
    $title = isset($cache['title']) ? trim((string)$cache['title']) : '';
    $artist = isset($cache['artist_name']) ? trim((string)$cache['artist_name']) : '';
    if ($title === '') {
        $title = isset($cache['song']) ? (string)$cache['song'] : '';
        $artist = '';
    }
    $live = !empty($cache['is_live']) && !empty($cache['has_moderator']);
    $onair = !empty($cache['onair_name']) ? $cache['onair_name'] : 'AutoDJ';

    $history = array();
    if (!empty($cache['history']) && is_array($cache['history'])) {
        foreach ($cache['history'] as $h) {
            $time = '';
            if (!empty($h['started_at'])) {
                $ts = strtotime($h['started_at']);
                $time = $ts ? date('H:i', $ts) : '';
            }
            $history[] = array(
                'artist' => $e(isset($h['artist']) ? $h['artist'] : ''),
                'title'  => $e(isset($h['title']) ? $h['title'] : ''),
                'time'   => $time,
            );
        }
    }
    $next = array();
    if (!empty($cache['show_next']) && !empty($cache['next_artists']) && is_array($cache['next_artists'])) {
        foreach ($cache['next_artists'] as $na) {
            if (!empty($na['name'])) {
                $next[] = $e($na['name']);
            }
        }
    }

    $player_base = isset($cache['player_base']) ? $cache['player_base'] : '';
    $id = (int)$cache['rs_id'];

    return array(
        'id'          => $id,
        'name'        => $e($cache['rs_name']),
        'pos'         => ($panel_typ == 1 || $panel_typ == 4) ? 'side' : 'main',
        'skin'        => cxm_rs_skin_key(isset($cache['rs_skin']) ? $cache['rs_skin'] : ''),
        'song'        => $e(isset($cache['song']) ? $cache['song'] : ''),
        'title'       => $e($title),
        'artist'      => $e($artist),
        'album'       => $e(isset($cache['album']) ? $cache['album'] : ''),
        'cover'       => $e($cover),
        'station_img' => $e($station_img),
        'default_img' => $e($default_img),
        'show_image'  => !isset($cache['show_image']) || !empty($cache['show_image']),
        'onair'       => $e($onair),
        'live'        => $live,
        'listeners'   => isset($cache['listner']) ? (int)$cache['listner'] : 0,
        'genre'       => $e(isset($cache['genre']) ? $cache['genre'] : ''),
        'starts'      => isset($cache['starts_at']) ? (int)$cache['starts_at'] : 0,
        'ends'        => isset($cache['ends_at']) ? (int)$cache['ends_at'] : 0,
        'stream'      => $e(isset($cache['stream_url']) ? $cache['stream_url'] : ''),
        'history'     => $history,
        'next'        => $next,
        'wb_button'   => isset($cache['wb_button_html']) ? $cache['wb_button_html'] : '',
        'popup_url'   => $player_base !== '' ? $e($player_base.'&typ=popup') : '',
        'm3u_url'     => $player_base !== '' ? $e($player_base.'&p=m3u') : '',
        'pls_url'     => $player_base !== '' ? $e($player_base.'&p=pls') : '',
        't'           => array(
            'now'       => $e(rtrim($locale['cxmrs_now'], ': ')),
            'history'   => $e(rtrim($locale['cxmrs_history'], ': ')),
            'next'      => $e(rtrim($locale['cxmrs_next'], ': ')),
            'live'      => $e($locale['cxmrs_live']),
            'onair'     => $e($locale['cxmrs_onair']),
            'listener'  => $e($locale['cxmrs_listener']),
            'play'      => $e($locale['cxmrs_player_play']),
            'pause'     => $e($locale['cxmrs_player_pause']),
            'popup'     => $e($locale['cxmrs_player_popup']),
            'm3u'       => $e($locale['cxmrs_player_m3u']),
            'pls'       => $e($locale['cxmrs_player_pls']),
        ),
    );
}

/**
 * Gemeinsame Bausteine der neuen Layouts
 */
function cxm_rs_part_play($v, $size = '') {
    if ($v['stream'] === '') {
        return '';
    }
    return '<button type="button" class="cxm-x-play'.($size !== '' ? ' cxm-x-play-'.$size : '').'" data-cxm-play="'.$v['id'].'" data-stream="'.$v['stream'].'"'
        .' data-label-play="'.$v['t']['play'].'" data-label-pause="'.$v['t']['pause'].'" aria-label="'.$v['t']['play'].'" title="'.$v['t']['play'].'">'
        .cxm_rs_icon('play').cxm_rs_icon('pause').'</button>';
}

function cxm_rs_part_progress($v) {
    if ($v['ends'] <= 0) {
        return '';
    }
    return '<div class="cxm-x-progress" data-starts="'.$v['starts'].'" data-ends="'.$v['ends'].'">'
        .'<div class="cxm-x-progress-track"><span class="cxm-x-progress-fill"></span></div>'
        .'<span class="cxm-x-progress-time" data-cxm-remaining></span>'
        .'</div>';
}

function cxm_rs_part_onair($v) {
    if ($v['live']) {
        return '<span class="cxm-x-onair is-live"><span class="cxm-x-onair-light" aria-hidden="true"></span>'.$v['t']['live'].' <b>'.$v['onair'].'</b></span>';
    }
    return '<span class="cxm-x-onair"><span class="cxm-x-onair-light" aria-hidden="true"></span>'.$v['onair'].'</span>';
}

function cxm_rs_part_listeners($v) {
    return '<span class="cxm-x-listeners" title="'.$v['t']['listener'].'">'.cxm_rs_icon('listener').'<b>'.$v['listeners'].'</b><span class="cxm-x-sr"> '.$v['t']['listener'].'</span></span>';
}

function cxm_rs_part_links($v) {
    if ($v['popup_url'] === '') {
        return '';
    }
    $popup_js = "window.open(this.href,'cxmrs_player','height=480,width=520,top=' + Math.round((screen.height - 480) / 2) + ',left=' + Math.round((screen.width - 520) / 2) + ',resizable=yes'); return false;";
    return '<span class="cxm-x-links">'
        .'<a href="'.$v['popup_url'].'" onclick="'.$popup_js.'" title="'.$v['t']['popup'].'" aria-label="'.$v['t']['popup'].'">'.cxm_rs_icon('popup').'</a>'
        .'<a href="'.$v['m3u_url'].'" title="'.$v['t']['m3u'].'">M3U</a>'
        .'<a href="'.$v['pls_url'].'" title="'.$v['t']['pls'].'">PLS</a>'
        .'</span>';
}

function cxm_rs_part_titles($v, $marquee = false) {
    $html = '<div class="cxm-x-titles">';
    $html .= '<div class="cxm-x-title'.($marquee ? ' cxm-x-marquee' : '').'"><span>'.$v['title'].'</span></div>';
    if ($v['artist'] !== '') {
        $html .= '<div class="cxm-x-artist'.($marquee ? ' cxm-x-marquee' : '').'"><span>'.$v['artist'].'</span></div>';
    }
    $html .= '</div>';
    return $html;
}

function cxm_rs_part_cover($v, $class = 'cxm-x-cover') {
    return '<div class="'.$class.'"><img src="'.$v['cover'].'" alt="" loading="lazy" onerror="this.onerror=null;this.src=\''.$v['default_img'].'\';" /></div>';
}

/**
 * Panel-HTML eines Streams (klassisch mit ###platzhaltern###, neu mit $v)
 */
function cxm_rs_render($cache, $panel_typ) {
    global $locale;

    $theme = cxm_rs_layout_id(isset($cache['rs_theme']) ? $cache['rs_theme'] : 10);
    $classic = ($theme === 3);

    ob_start();
    if (!$classic) {
        $v = cxm_rs_view($cache, $panel_typ);
        $v['online'] = !empty($cache['status']);
        require CXM_RADIOSTATUS.'theme/'.($v['online'] ? 'layout_'.cxm_rs_layouts()[$theme]['key'] : 'layout_offline').'.php';
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    if ($cache['status']) {
        $file = cxm_rs_theme_file($theme, $panel_typ);
    } else {
        $file = CXM_RADIOSTATUS.'theme/offline_default.php';
    }
    if (file_exists($file)) {
        require $file;
    }
    $theme_html = ob_get_contents();
    ob_end_clean();

    // next_artists_block aufbauen
    $next_block = '';
    if ($cache['status'] && !empty($cache['show_next']) && !empty($cache['next_artists'])) {
        $next_block = '<div class="cxm-rs-next"><strong>'.$locale['cxmrs_next'].'</strong><ul>';
        foreach ($cache['next_artists'] as $na) {
            $next_block .= '<li>';
            if (!empty($na['thumb'])) {
                $next_block .= '<img src="'.htmlspecialchars($na['thumb']).'" alt="" width="30" height="30" /> ';
            }
            $next_block .= htmlspecialchars($na['name']).'</li>';
        }
        $next_block .= '</ul></div>';
    }

    // history_block aufbauen
    $history_block = '';
    if ($cache['status'] && !empty($cache['history'])) {
        $history_block = '<div class="cxm-rs-history"><strong>'.$locale['cxmrs_history'].'</strong><table>';
        foreach ($cache['history'] as $h) {
            $history_block .= '<tr><td>'.htmlspecialchars($h['artist'].' - '.$h['title']).'</td></tr>';
        }
        $history_block .= '</table></div>';
    }

    $live_badge = ($cache['status'] && !empty($cache['is_live']))
        ? '<span class="cxm-rs-live">'.$locale['cxmrs_live'].'</span>'
        : '';

    $search = array(
        '###rs_id###', '###rs_name###', '###song###', '###title###', '###artist###',
        '###artist_thumb###', '###album###', '###listner###', '###genre###',
        '###playlist###', '###is_live###', '###live_badge###', '###station_img###',
        '###onair_name###',
        '###stream_url###', '###next_artists_block###', '###history_block###',
        '###player###', '###wb_button###', '###wb_captcha###', '###wb_username###',
        '###ends_at###', '###error###',
    );
    $replace = array(
        $cache['rs_id'],
        htmlspecialchars($cache['rs_name']),
        htmlspecialchars($cache['song']),
        htmlspecialchars($cache['title']),
        htmlspecialchars($cache['artist_name']),
        htmlspecialchars($cache['artist_thumb']),
        htmlspecialchars($cache['album']),
        (int)$cache['listner'],
        htmlspecialchars($cache['genre']),
        htmlspecialchars($cache['playlist']),
        (int)$cache['is_live'],
        $live_badge,
        htmlspecialchars($cache['station_img']),
        htmlspecialchars($cache['onair_name']),
        htmlspecialchars($cache['stream_url']),
        $next_block,
        $history_block,
        isset($cache['player_html']) ? $cache['player_html'] : '',
        isset($cache['wb_button_html']) ? $cache['wb_button_html'] : '',
        '',
        htmlspecialchars(isset($cache['wb_username']) ? $cache['wb_username'] : ''),
        (int)$cache['ends_at'],
        ((defined('iSUPERADMIN') && iSUPERADMIN) ? htmlspecialchars($cache['error']) : ''),
    );
    return str_replace($search, $replace, $theme_html);
}

/**
 * Beispieldaten für die Vorschau im Admin
 */
function cxm_rs_demo_cache($skin, $theme) {
    global $locale;

    $now = time();
    return array(
        'rs_id' => 0, 'rs_name' => 'Radio Beispiel', 'rs_theme' => $theme, 'rs_skin' => $skin,
        'status' => 1, 'error' => '', 'song' => "The B-52's - Love Shack", 'title' => 'Love Shack',
        'artist_name' => "The B-52's", 'artist_thumb' => '', 'album' => 'Cosmic Thing',
        'starts_at' => $now - 134, 'ends_at' => $now + 187, 'listner' => 12, 'genre' => 'Pop',
        'is_live' => 1, 'has_moderator' => 1, 'playlist' => '', 'onair_name' => 'DJ Nachtfalke',
        'station_img' => CXM_RADIOSTATUS.'images/station_placeholder.svg', 'stream_url' => '#',
        'show_image' => 1, 'show_next' => 1, 'player_html' => '', 'player_base' => '#',
        'history' => array(
            array('artist' => 'Nena', 'title' => '99 Luftballons', 'started_at' => date('c', $now - 360)),
            array('artist' => 'ABBA', 'title' => 'Dancing Queen', 'started_at' => date('c', $now - 590)),
            array('artist' => 'Bee Gees', 'title' => 'Stayin\' Alive', 'started_at' => date('c', $now - 870)),
        ),
        'next_artists' => array(array('name' => 'Queen'), array('name' => 'Roxette')),
        'wb_button_html' => '<button type="button" class="cxm-x-wish" tabindex="-1">'.cxm_rs_icon('wish').'<span>'.$locale['cxmrswb_open'].'</span></button>',
        'wb_username' => '',
    );
}
?>
