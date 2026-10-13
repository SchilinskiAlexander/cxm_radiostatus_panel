<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: cxm_radiostatus_inc.php
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
require_once dirname(__FILE__).'/../../maincore.php';

header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

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

function cxm_rs_panel_where($panel) {
    return "rs_panel='".(int)$panel."'";
}

function cxm_rs_load_cache($stream) {
    global $cxm_radiostatus_settings;

    $cache = array();
    $cache_file = CXM_RADIOSTATUS.'cache/stream_'.$stream['rs_id'].'_'.$stream['rs_cache'].'.php';
    if (!file_exists($cache_file) || filemtime($cache_file) <= (time() - (int)$cxm_radiostatus_settings['reload_cache'])) {
        require_once CXM_RADIOSTATUS.'cxm_radiostatus_class.php';
        $api = new LautFmAPI($stream['rs_station'], $cxm_radiostatus_settings['api_base']);
        $res = $api->makeCache($stream['rs_id'], $stream['rs_cache'], $stream['rs_stream_url']);
        $cache_dir = dirname($cache_file);
        $f = is_dir($cache_dir) && is_writable($cache_dir) ? @fopen($cache_file, 'w') : false;
        if ($f !== false) {
            fwrite($f, $res);
            fclose($f);
        }
        eval('?>'.$res.'<?php ');
    } elseif (file_exists($cache_file)) {
        include $cache_file;
    }

    return is_array($cache) ? $cache : array();
}

function cxm_rs_has_live_moderator($cache) {
    return !empty($cache['is_live'])
        && !empty($cache['has_moderator'])
        && !empty($cache['onair_name'])
        && $cache['onair_name'] !== 'AutoDJ';
}

function cxm_rs_player($info) {
    global $settings, $locale;

    $base = $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS).'cxm_radiostatus_player.php?id='.(int)$info['rs_id'];
    $popup = "window.open('".$base."&amp;typ=popup','cxmrs_player','height=440,width=500,top=' + Math.round((screen.height - 440) / 2) + ',left=' + Math.round((screen.width - 500) / 2) + ',resizable=yes'); return false;";

    $html = '<span class="cxm-rs-player-icons">';
    $html .= '<a href="'.$base.'&amp;p=m3u" title="'.$locale['cxmrs_player_m3u'].'" class="cxm-rs-player-link">'
        .'<img src="'.CXM_RADIOSTATUS.'images/player_m3u.svg" alt="'.$locale['cxmrs_player_m3u'].'" width="28" height="28" /></a> ';
    $html .= '<a href="'.$base.'&amp;p=pls" title="'.$locale['cxmrs_player_pls'].'" class="cxm-rs-player-link">'
        .'<img src="'.CXM_RADIOSTATUS.'images/player_pls.svg" alt="'.$locale['cxmrs_player_pls'].'" width="28" height="28" /></a> ';
    $html .= '<a href="'.$base.'&amp;typ=popup" onclick="'.$popup.'" title="'.$locale['cxmrs_player_popup'].'" class="cxm-rs-player-link">'
        .'<img src="'.CXM_RADIOSTATUS.'images/player_popup.svg" alt="'.$locale['cxmrs_player_popup'].'" width="28" height="28" /></a>';
    $html .= '</span>';

    return $html;
}

// ── ?panel=X  Panel-HTML ausgeben ──────────────────────────────────────────
if (isset($_GET['panel']) && isnum($_GET['panel'])) {

    function cxm_rs_theme($cache, $panel_typ) {
        global $locale, $cxm_radiostatus_settings;
        $name = ($panel_typ == 1 || $panel_typ == 4) ? 'side_' : 'main_';
        ob_start();
        if ($cache['status']) {
            $file = CXM_RADIOSTATUS.'theme/'.$name.$cache['rs_theme'].'.php';
            if (file_exists($file)) {
                require $file;
            }
        } else {
            $file = CXM_RADIOSTATUS.'theme/offline_default.php';
            if (file_exists($file)) {
                require $file;
            }
        }
        $theme = ob_get_contents();
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

        // live_badge
        $live_badge = ($cache['status'] && !empty($cache['is_live']))
            ? '<span class="cxm-rs-live">'.$locale['cxmrs_live'].'</span>'
            : '';

        // Wunschbox-Button
        $wb_button = '';
        if (!empty($cache['wb_button_html'])) {
            $wb_button = $cache['wb_button_html'];
        }

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
            $cache['player_html'],
            $wb_button,
            '',   // wb_captcha wird im Panel gesetzt
            htmlspecialchars($cache['wb_username']),
            (int)$cache['ends_at'],
            (iSUPERADMIN ? htmlspecialchars($cache['error']) : ''),
        );
        return str_replace($search, $replace, $theme);
    }

    $panel   = intval($_GET['panel']);
    $result  = dbquery("SELECT * FROM ".DB_CXM_RADIOSTATUS." WHERE ".groupaccess("rs_access")." AND ".cxm_rs_panel_where($panel)." AND rs_status='1' ORDER BY rs_order");
    $count   = dbrows($result);

    if ($count) {
        while ($data = dbarray($result)) {
            $cache = cxm_rs_load_cache($data);

            // DB-Felder in Cache übernehmen
            $cache['rs_id']      = $data['rs_id'];
            $cache['rs_name']    = $data['rs_name'];
            $cache['rs_theme']   = $data['rs_theme'];
            $cache['rs_panel']   = $data['rs_panel'];
            $cache['show_image'] = $data['rs_show_image'];
            $cache['show_next']  = $data['rs_show_next'];
            $cache['wb_username']= iMEMBER ? $userdata['user_name'] : '';
            $cache['player_html']= cxm_rs_player($data);

            // Felder initialisieren wenn offline-Cache leer
            foreach (array('status','song','title','artist_name','artist_thumb','album','ends_at',
                           'listner','genre','is_live','has_moderator','playlist','station_img','onair_name','stream_url',
                           'history','next_artists','error') as $f) {
                if (!isset($cache[$f])) {
                    $cache[$f] = is_array($cache) ? '' : '';
                }
            }
            if (empty($cache['onair_name'])) {
                $cache['onair_name'] = 'AutoDJ';
            }
            if (empty($cache['station_img']) || empty($cache['onair_name']) || $cache['onair_name'] === 'AutoDJ') {
                $cache['station_img'] = CXM_RADIOSTATUS.'images/autodj.gif';
            }
            if (!isset($cache['history']))      { $cache['history'] = array(); }
            if (!isset($cache['next_artists'])) { $cache['next_artists'] = array(); }

            if ($data['rs_status_wb'] && checkgroup($data['rs_access_wb']) && cxm_rs_has_live_moderator($cache)) {
                if (!empty($data['rs_wb_popup'])) {
                    $wb_url = $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS).'cxm_radiostatus_player.php?id='.(int)$data['rs_id'].'&typ=wb';
                    $cache['wb_button_html'] = '<button type="button" onclick="window.open(\''.$wb_url.'\',\'cxmrs_wb\',\'height=430,width=560,top=\' + Math.round((screen.height - 430) / 2) + \',left=\' + Math.round((screen.width - 560) / 2) + \',resizable=yes\'); return false;" class="button cxm-rs-wb-btn">'
                        .$locale['cxmrswb_open'].'</button>';
                } else {
                    $cache['wb_button_html'] = '<button type="button" onclick="updateWB_'.$data['rs_id'].'()" class="button cxm-rs-wb-btn">'
                        .$locale['cxmrswb_open'].'</button>';
                }
            } else {
                $cache['wb_button_html'] = '';
            }

            echo cxm_rs_theme($cache, $panel);
        }
    } else {
        echo '<div class="cxm-rs-empty">'.$locale['cxmrs_no_stream'].'</div>';
    }

// ── ?js=X  JavaScript ausgeben ─────────────────────────────────────────────
} elseif (isset($_GET['js']) && isnum($_GET['js'])) {

    $panel = intval($_GET['js']);
    header('Content-Type: text/javascript');

    $self     = $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS);
    $reload   = ($panel == 1 || $panel == 4)
                ? (int)$cxm_radiostatus_settings['reload_side']
                : (int)$cxm_radiostatus_settings['reload_main'];

    echo 'var cxmRsTimer_'.$panel.'=null;';
    echo 'var cxmRsBusy_'.$panel.'=false;';
    echo 'var cxmRsLastHtml_'.$panel.'="";';
    echo 'function cxmRsSchedule_'.$panel.'(delay){';
    echo 'if(cxmRsTimer_'.$panel.'){clearTimeout(cxmRsTimer_'.$panel.');}';
    echo 'cxmRsTimer_'.$panel.'=setTimeout(updateCXMRS_'.$panel.',delay);}';
    echo 'function updateCXMRS_'.$panel.'(){';
    echo 'var container=$("#cxm_radiostatus_panel_'.$panel.'");';
    echo 'if(!container.length){return;}';
    echo 'if(cxmRsBusy_'.$panel.'){return;}';
    echo 'if(document.hidden){cxmRsSchedule_'.$panel.'('.$reload.');return;}';
    echo 'cxmRsBusy_'.$panel.'=true;';
    echo '$.ajax({';
    echo 'url:"'.$self.'cxm_radiostatus_inc.php?panel='.$panel.'&_="+new Date().getTime(),';
    echo 'cache:false,';
    echo 'success:function(html){';
    echo 'var nextHtml=$.trim(html);';
    echo 'if(cxmRsLastHtml_'.$panel.'===""||nextHtml!==cxmRsLastHtml_'.$panel.'){';
    echo 'var currentAudio=container.find("audio").get(0);';
    echo 'var audioIsPlaying=currentAudio&&!currentAudio.paused&&!currentAudio.ended;';
    echo 'if(!audioIsPlaying){container.html(html);cxmRsLastHtml_'.$panel.'=nextHtml;cxmRsUpdateProgress();}';
    echo '}';
    echo '},';
    echo 'complete:function(){cxmRsBusy_'.$panel.'=false;cxmRsSchedule_'.$panel.'('.$reload.');}';
    echo '});}';
    echo '$(document).ready(function(){updateCXMRS_'.$panel.'();';
    echo '$(document).on("visibilitychange",function(){if(!document.hidden){updateCXMRS_'.$panel.'();}});';
    echo '});';

    // Pro Stream Wunschbox-Funktionen
    $result = dbquery("SELECT rs_id, rs_status_wb, rs_access_wb FROM ".DB_CXM_RADIOSTATUS
        ." WHERE ".groupaccess("rs_access")." AND ".cxm_rs_panel_where($panel)." AND rs_status='1'");
    while ($data = dbarray($result)) {
        $id = (int)$data['rs_id'];
        if ($data['rs_status_wb'] && checkgroup($data['rs_access_wb'])) {
            echo 'function updateWB_'.$id.'(){$("#cxm_rs_wb_'.$id.'").toggle("slow");}';
            echo 'function check_wb_'.$id.'(){'
                .'var formData=$("#cxm_wb_form_'.$id.'").serialize()+"&wb='.$id.'";'
                .'$.ajax({url:"'.$self.'cxm_radiostatus_inc.php",type:"POST",data:formData,'
                .'success:function(r){$("#cxm_rs_wb_check_'.$id.'").html(r).show();'
                .cxm_rs_captcha_reset_js('"#cxm_wb_form_'.$id.'"')
                .'}});'
                .'return false;}';
        }
    }

    // Fortschrittsbalken-Aktualisierung
    echo 'function cxmRsUpdateProgress(){'
        .'$(".cxm-rs-progress-bar").each(function(){'
        .'var ends=parseInt($(this).data("ends"));'
        .'if(ends>0){'
        .'var now=Math.floor(Date.now()/1000);'
        .'var diff=ends-now;'
        .'if(diff>0&&diff<600){'
        .'var pct=Math.max(0,Math.min(100,Math.round((1-diff/600)*100)));'
        .'$(this).css("width",pct+"%");'
        .'}}'
        .'});}';
    echo 'setInterval(cxmRsUpdateProgress,1000);';

// ── POST ?wb=X  Wunschbox-Eintrag speichern ───────────────────────────────
} elseif (isset($_POST['wb']) && isnum($_POST['wb'])) {

    sleep(3);
    if (iMEMBER || cxm_rs_captcha_valid()) {
        $wb_id = intval($_POST['wb']);
        $result = dbquery("SELECT rs_id, rs_station, rs_stream_url, rs_cache, rs_status_wb, rs_wb_max, rs_wb_max_user, rs_access_wb FROM ".DB_CXM_RADIOSTATUS
            ." WHERE ".groupaccess("rs_access")." AND rs_id='".$wb_id."' AND rs_status='1'");
        if (dbrows($result)) {
            $data = dbarray($result);
            $cache = cxm_rs_load_cache($data);
            if ($data['rs_status_wb'] && checkgroup($data['rs_access_wb']) && cxm_rs_has_live_moderator($cache)) {
                // Rate-Limit prüfen (nach IP und User-ID)
                if (iMEMBER) {
                    $rows = dbcount("(wb_id)", DB_CXM_RADIOSTATUS_WUNSCHBOX,
                        "wb_stream='".$wb_id."' AND wb_user_id='".(int)$userdata['user_id']."'");
                } else {
                    $rows = dbcount("(wb_id)", DB_CXM_RADIOSTATUS_WUNSCHBOX,
                        "wb_stream='".$wb_id."' AND wb_user_ip='".USER_IP."'");
                }
                if ((int)$data['rs_wb_max_user'] > 0 && $rows >= (int)$data['rs_wb_max_user']) {
                    echo $locale['cxmrswb_err_limit'];
                } else {
                    $name      = isset($_POST['name'])      ? stripinput($_POST['name'])      : '';
                    $wunsch    = isset($_POST['wunsch'])    ? stripinput($_POST['wunsch'])    : '';
                    $nachricht = isset($_POST['nachricht']) ? stripinput($_POST['nachricht']) : '';

                    if ($name !== '' && ($wunsch !== '' || $nachricht !== '')) {
                        dbquery("INSERT INTO ".DB_CXM_RADIOSTATUS_WUNSCHBOX
                            ." (wb_stream, wb_user_id, wb_user_ip, wb_user_name, wb_wunsch, wb_nachricht, wb_time)"
                            ." VALUES ('".$wb_id."', '".(iMEMBER ? (int)$userdata['user_id'] : 0)."', '".USER_IP."',"
                            ." '".$name."', '".$wunsch."', '".$nachricht."', '".time()."')");
                        // Gesamtlimit prüfen
                        if ((int)$data['rs_wb_max'] > 0) {
                            $total = dbcount("(wb_id)", DB_CXM_RADIOSTATUS_WUNSCHBOX, "wb_stream='".$wb_id."'");
                            if ($total >= (int)$data['rs_wb_max']) {
                                dbquery("UPDATE ".DB_CXM_RADIOSTATUS." SET rs_status_wb='0' WHERE rs_id='".$wb_id."'");
                            }
                        }
                        echo $locale['cxmrswb_ok'];
                    } else {
                        echo $locale['cxmrswb_err_fields'];
                    }
                }
            } else {
                echo $locale['cxmrswb_err_closed'];
            }
        } else {
            echo $locale['cxmrswb_err_stream'];
        }
    } else {
        echo $locale['cxmrswb_err_cap'];
    }

// ── ?wbadmin=X  Wunschbox-Admin ───────────────────────────────────────────
} elseif ((isset($_GET['wbadmin']) && isnum($_GET['wbadmin'])) || (isset($_POST['wbadmin']) && isnum($_POST['wbadmin']))) {

    $wb_id = isset($_POST['wbadmin']) ? intval($_POST['wbadmin']) : intval($_GET['wbadmin']);
    $request_aid = isset($_POST['aid']) ? $_POST['aid'] : (isset($_GET['aid']) ? $_GET['aid'] : '');

    if (!iADMIN || !checkrights('CXRS') || !defined('iAUTH') || $request_aid != iAUTH) {
        echo $locale['cxmrswb_err_stream'];
        exit;
    }

    $stream_result = dbquery("SELECT rs_id, rs_name FROM ".DB_CXM_RADIOSTATUS." WHERE rs_id='".$wb_id."' LIMIT 1");
    if (!dbrows($stream_result)) {
        echo $locale['cxmrswb_err_stream'];
        exit;
    }
    $stream = dbarray($stream_result);

    $wb_action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $wb_action === 'del_all') {
        dbquery("DELETE FROM ".DB_CXM_RADIOSTATUS_WUNSCHBOX." WHERE wb_stream='".$wb_id."'");
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $wb_action === 'read_all') {
        dbquery("UPDATE ".DB_CXM_RADIOSTATUS_WUNSCHBOX." SET wb_status='1' WHERE wb_stream='".$wb_id."'");
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isnum($_POST['id'])) {
        dbquery("DELETE FROM ".DB_CXM_RADIOSTATUS_WUNSCHBOX." WHERE wb_id='".intval($_POST['id'])."' AND wb_stream='".$wb_id."'");
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uid']) && isnum($_POST['uid']) && isset($_POST['status']) && isnum($_POST['status'])) {
        dbquery("UPDATE ".DB_CXM_RADIOSTATUS_WUNSCHBOX." SET wb_status='".intval($_POST['status'])."'"
            ." WHERE wb_id='".intval($_POST['uid'])."' AND wb_stream='".$wb_id."'");
    }

    $unread = dbcount("(wb_id)", DB_CXM_RADIOSTATUS_WUNSCHBOX, "wb_stream='".$wb_id."' AND wb_status='0'");

    echo '<div class="cxm-rs-wb-admin-head">'
        .'<strong>'.htmlspecialchars($stream['rs_name']).'</strong>'
        .($unread > 0 ? ' <span>('.$unread.' '.$locale['cxmrsa_new_entries'].')</span>' : '')
        .'</div>';
    echo '<div class="cxm-rs-wb-admin-actions">'
        .'<button type="button" class="button" onclick="if (typeof cxmRsWbBulk === \'function\') { cxmRsWbBulk(\'read_all\'); }">'.$locale['cxmrsa_wb_read_all'].'</button> '
        .'<button type="button" class="button" onclick="if (!confirm(\''.$locale['cxmrsa_confirm_del'].'\')) { return false; } if (typeof cxmRsWbBulk === \'function\') { cxmRsWbBulk(\'del_all\'); }">'.$locale['cxmrsa_wb_del_all'].'</button>'
        .'</div>';

    $result = dbquery("SELECT * FROM ".DB_CXM_RADIOSTATUS_WUNSCHBOX." WHERE wb_stream='".$wb_id."' ORDER BY wb_time DESC");
    if (dbrows($result)) {
        echo '<table cellpadding="2" cellspacing="1" width="100%" class="tbl-border">
        <tr>
            <td class="tbl2"><strong>'.$locale['cxmrsa_wb_time_col'].'</strong></td>
            <td class="tbl2"><strong>'.$locale['cxmrsa_wb_name_col'].'</strong></td>
            <td class="tbl2"><strong>'.$locale['cxmrsa_wb_wish_col'].'</strong></td>
            <td class="tbl2"><strong>'.$locale['cxmrsa_wb_status_col'].'</strong></td>
            <td class="tbl2"><strong>'.$locale['cxmrsa_wb_action_col'].'</strong></td>
        </tr>';
        while ($row = dbarray($result)) {
            $name_display = htmlspecialchars($row['wb_user_name']);
            echo '<tr>
                <td class="tbl1">'.showdate($cxm_radiostatus_settings['wb_time'], $row['wb_time']).'</td>
                <td class="tbl1">'.$name_display.'</td>
                <td class="tbl1">'.htmlspecialchars($row['wb_wunsch']).'<br />'.htmlspecialchars($row['wb_nachricht']).'</td>
                <td class="tbl1">'.($row['wb_status'] ? $locale['cxmrsa_wb_read'] : $locale['cxmrsa_wb_unread']).'</td>
                <td class="tbl1">
                    <button type="button" class="button" onclick="if (typeof cxmRsWbStatus === \'function\') { cxmRsWbStatus('.$row['wb_id'].', '.($row['wb_status'] ? 0 : 1).'); }">'
                    .($row['wb_status'] ? $locale['cxmrsa_wb_unread'] : $locale['cxmrsa_wb_read']).'</button>
                    &nbsp;
                    <button type="button" class="button" onclick="if (!confirm(\''.$locale['cxmrsa_confirm_del'].'\')) { return false; } if (typeof cxmRsWbDelete === \'function\') { cxmRsWbDelete('.$row['wb_id'].'); }">'.$locale['cxmrsa_wb_del'].'</button>
                </td>
            </tr>';
        }
        echo '</table>';
    } else {
        echo '<div class="tbl2">'.$locale['cxmrsa_wb_empty'].'</div>';
    }

} else {
    echo $locale['cxmrs_no_stream'];
}
?>
