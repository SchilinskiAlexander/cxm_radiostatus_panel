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

if ($type !== 'popup' && $type !== 'wb' && $type !== 'embed') {
    redirect(BASEDIR.'index.php');
}

$base_player_url = $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS).'cxm_radiostatus_player.php?id='.(int)$data['rs_id'];
$player_icons = '<span class="cxm-player-icons">'
    .'<a href="'.$base_player_url.'&amp;p=m3u" title="'.htmlspecialchars($locale['cxmrs_player_m3u']).'" class="cxm-player-icon">'
    .'<img src="'.htmlspecialchars(CXM_RADIOSTATUS.'images/player_m3u.svg').'" alt="'.htmlspecialchars($locale['cxmrs_player_m3u']).'" width="26" height="26" /></a>'
    .'<a href="'.$base_player_url.'&amp;p=pls" title="'.htmlspecialchars($locale['cxmrs_player_pls']).'" class="cxm-player-icon">'
    .'<img src="'.htmlspecialchars(CXM_RADIOSTATUS.'images/player_pls.svg').'" alt="'.htmlspecialchars($locale['cxmrs_player_pls']).'" width="26" height="26" /></a>'
    .'<a href="'.$base_player_url.'&amp;typ=popup" title="'.htmlspecialchars($locale['cxmrs_player_popup']).'" class="cxm-player-icon" target="_blank">'
    .'<img src="'.htmlspecialchars(CXM_RADIOSTATUS.'images/player_popup.svg').'" alt="'.htmlspecialchars($locale['cxmrs_player_popup']).'" width="26" height="26" /></a>'
    .'</span>';

$station_image = !empty($cache['station_img']) ? $cache['station_img'] : (CXM_RADIOSTATUS.'images/autodj.gif');
$onair_name = !empty($cache['onair_name']) ? $cache['onair_name'] : 'AutoDJ';
$song_title = !empty($cache['song']) ? $cache['song'] : $locale['cxmrs_now'];
$reload_ms = max(15000, (int)$cxm_radiostatus_settings['reload_main']);
?><!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="<?php echo $locale['charset']; ?>" />
    <title><?php echo htmlspecialchars($data['rs_name']); ?></title>
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
                var img = $("#cxm_wb_popup_captcha");
                if (img.length) {
                    img.attr("src", img.attr("data-base") + "?sid=" + Math.random());
                }
            }
        });
        return false;
    }
    </script>
    <?php endif; ?>
    <?php if ($type === 'embed'): ?>
    <script type="text/javascript">
    window.setInterval(function () {
        window.location.reload();
    }, <?php echo (int)$reload_ms; ?>);
    </script>
    <?php endif; ?>
</head>
<body class="cxm-player-body<?php echo $type === 'embed' ? ' cxm-embed-body' : ''; ?>">
    <?php if ($type === 'wb'): ?>
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
                <?php $captcha_path = INCLUDES.(!empty($settings['captcha']) ? 'captchas/' : '').'securimage/'; ?>
                <tr>
                    <td><?php echo $locale['cxmrswb_captcha']; ?> *</td>
                    <td>
                        <img id="cxm_wb_popup_captcha" data-base="<?php echo $captcha_path; ?>securimage_show.php" src="<?php echo $captcha_path; ?>securimage_show.php" alt="" /><br />
                        <a href="#" onclick='document.getElementById("cxm_wb_popup_captcha").src="<?php echo $captcha_path; ?>securimage_show.php?sid="+Math.random();return false;'>Neu laden</a><br />
                        <input type="text" name="code" class="textbox" />
                    </td>
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
    <?php elseif ($type === 'embed'): ?>
    <div class="cxm-embed">
        <div class="cxm-embed-head">
            <img src="<?php echo htmlspecialchars($station_image); ?>" alt="<?php echo htmlspecialchars($onair_name); ?>" class="cxm-embed-image"
                 onerror="this.onerror=null;this.src='<?php echo htmlspecialchars(CXM_RADIOSTATUS.'images/autodj.gif'); ?>';" />
            <div class="cxm-embed-meta">
                <div class="cxm-embed-station"><?php echo htmlspecialchars($data['rs_name']); ?></div>
                <span class="cxm-embed-onair-label"><?php echo htmlspecialchars($locale['cxmrs_onair']); ?></span>
                <span class="cxm-embed-onair-name"><?php echo htmlspecialchars($onair_name); ?></span>
                <?php if (!empty($cache['is_live'])): ?>
                <span class="cxm-embed-live"><?php echo htmlspecialchars($locale['cxmrs_live']); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <span class="cxm-embed-song-label"><?php echo htmlspecialchars($locale['cxmrs_now']); ?></span>
        <div class="cxm-embed-song"><span class="cxm-embed-song-inner"><?php echo htmlspecialchars($song_title); ?></span></div>

        <div class="cxm-embed-footer">
            <span class="cxm-embed-footer-label">Player</span>
            <div class="cxm-embed-links">
            <?php echo $player_icons; ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="cxm-player">
        <h1><?php echo htmlspecialchars($data['rs_name']); ?></h1>
        <div class="song"><?php echo !empty($cache['song']) ? htmlspecialchars($cache['song']) : htmlspecialchars($locale['cxmrs_now']); ?></div>
        <audio controls autoplay preload="none">
            <source src="<?php echo htmlspecialchars($stream_url); ?>" type="audio/mpeg" />
        </audio>
    </div>
    <?php endif; ?>
</body>
</html>
