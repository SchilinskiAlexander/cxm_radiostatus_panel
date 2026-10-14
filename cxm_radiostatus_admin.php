<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: cxm_radiostatus_admin.php
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

$request_aid = isset($_GET['aid']) ? $_GET['aid'] : (isset($_POST['aid']) ? $_POST['aid'] : '');
$request_tab = isset($_GET['tab']) ? $_GET['tab'] : (isset($_POST['tab']) ? $_POST['tab'] : 'streams');

if (!checkrights('CXRS') || !defined('iAUTH') || $request_aid != iAUTH) {
    redirect(BASEDIR.'index.php');
}

if (!defined('CXM_RADIOSTATUS')) {
    define('CXM_RADIOSTATUS', INFUSIONS.'cxm_radiostatus_panel/');
}

require_once CXM_RADIOSTATUS.'infusion_db.php';
require_once CXM_RADIOSTATUS.'cxm_radiostatus_class.php';
cxm_rs_ensure_schema();
if (file_exists(CXM_RADIOSTATUS.'locale/'.LOCALESET.'index.php')) {
    require CXM_RADIOSTATUS.'locale/'.LOCALESET.'index.php';
} else {
    require CXM_RADIOSTATUS.'locale/German/index.php';
}
require_once INCLUDES.'output_handling_include.php';
add_to_head('<link rel="stylesheet" type="text/css" href="'.CXM_RADIOSTATUS.'css/cxm_radiostatus.css" />');
add_to_head('<script type="text/javascript">
function cxmRsOpenPreviewModal(id, name, url, code) {
    var modal = $("#cxm-rs-preview-modal");
    modal.find("[data-role=title]").text(name);
    modal.find("[data-role=frame]").attr("src", url);
    modal.find("[data-role=code]").val(code);
    modal.removeClass("cxm-rs-hidden");
    $("body").addClass("cxm-rs-modal-open");
    return false;
}

function cxmRsClosePreviewModal() {
    var modal = $("#cxm-rs-preview-modal");
    modal.addClass("cxm-rs-hidden");
    modal.find("[data-role=frame]").attr("src", "about:blank");
    $("body").removeClass("cxm-rs-modal-open");
    return false;
}

$(document).on("keyup", function(e) {
    if (e.keyCode === 27) {
        cxmRsClosePreviewModal();
    }
});
</script>');

function cxm_rs_access_select($name, $selected = 0, $include_public = true) {
    global $locale;

    $html = '<select name="'.$name.'" class="textbox">';
    if ($include_public) {
        $html .= '<option value="0"'.((string)$selected === '0' ? ' selected="selected"' : '').'>'.$locale['user0'].'</option>';
        $html .= '<option value="101"'.((string)$selected === '101' ? ' selected="selected"' : '').'>'.$locale['user1'].'</option>';
    }
    $html .= '<option value="102"'.((string)$selected === '102' ? ' selected="selected"' : '').'>'.$locale['user2'].'</option>';
    $html .= '<option value="103"'.((string)$selected === '103' ? ' selected="selected"' : '').'>'.$locale['user3'].'</option>';

    $result = dbquery("SELECT group_id, group_name FROM ".DB_USER_GROUPS." ORDER BY group_name");
    while ($data = dbarray($result)) {
        $html .= '<option value="'.$data['group_id'].'"' . ((string)$selected === (string)$data['group_id'] ? ' selected="selected"' : '') . '>'
            .htmlspecialchars($data['group_name']).'</option>';
    }

    $html .= '</select>';

    return $html;
}

function cxm_rs_stream_action_button($id, $action, $label, $class, $title, $confirm = '') {
    global $admin_form_action;

    $onclick = $confirm !== '' ? ' onclick="return confirm(\''.htmlspecialchars(addslashes($confirm), ENT_QUOTES).'\')"' : '';

    return '<form method="post" action="'.$admin_form_action.'" class="cxm-rs-inline-action">'
        .'<input type="hidden" name="aid" value="'.iAUTH.'" />'
        .'<input type="hidden" name="tab" value="streams" />'
        .'<input type="hidden" name="id" value="'.(int)$id.'" />'
        .'<input type="hidden" name="stream_action" value="'.htmlspecialchars($action).'" />'
        .'<button type="submit" class="'.$class.'" title="'.htmlspecialchars($title).'" aria-label="'.htmlspecialchars($title).'"'.$onclick.'>'.$label.'</button>'
        .'</form>';
}

$admin_url = FUSION_SELF.$aidlink;
$admin_form_action = $admin_url;
$tab = stripinput($request_tab);

// ── Tab: Streams – Aktionen ────────────────────────────────────────────────
if ($tab === 'streams' || $tab === 'stream') {

    // Stream löschen
    if (isset($_POST['stream_action']) && $_POST['stream_action'] === 'delete' && isset($_POST['id']) && isnum($_POST['id'])) {
        $del_id = intval($_POST['id']);
        // Cache-Datei löschen
        $result_c = dbquery("SELECT rs_cache FROM ".DB_CXM_RADIOSTATUS." WHERE rs_id='".$del_id."'");
        if (dbrows($result_c)) {
            $cd = dbarray($result_c);
            $cf = CXM_RADIOSTATUS.'cache/stream_'.$del_id.'_'.$cd['rs_cache'].'.php';
            if (file_exists($cf)) { @unlink($cf); }
        }
        dbquery("DELETE FROM ".DB_CXM_RADIOSTATUS." WHERE rs_id='".$del_id."'");
        redirect($admin_url.'&tab=streams');
    }

    // Reihenfolge ändern
    if (isset($_POST['stream_action']) && in_array($_POST['stream_action'], array('up','down')) && isset($_POST['id']) && isnum($_POST['id'])) {
        $mv_id = intval($_POST['id']);
        $r = dbarray(dbquery("SELECT rs_order, rs_panel FROM ".DB_CXM_RADIOSTATUS." WHERE rs_id='".$mv_id."'"));
        if ($r) {
            if ($_POST['stream_action'] === 'up') {
                $swap = dbarray(dbquery("SELECT rs_id, rs_order FROM ".DB_CXM_RADIOSTATUS
                    ." WHERE rs_panel='".$r['rs_panel']."' AND rs_order<'".$r['rs_order']."' ORDER BY rs_order DESC LIMIT 1"));
            } else {
                $swap = dbarray(dbquery("SELECT rs_id, rs_order FROM ".DB_CXM_RADIOSTATUS
                    ." WHERE rs_panel='".$r['rs_panel']."' AND rs_order>'".$r['rs_order']."' ORDER BY rs_order ASC LIMIT 1"));
            }
            if ($swap) {
                dbquery("UPDATE ".DB_CXM_RADIOSTATUS." SET rs_order='".$swap['rs_order']."' WHERE rs_id='".$mv_id."'");
                dbquery("UPDATE ".DB_CXM_RADIOSTATUS." SET rs_order='".$r['rs_order']."' WHERE rs_id='".$swap['rs_id']."'");
            }
        }
        redirect($admin_url.'&tab=streams');
    }

    // Status umschalten
    if (isset($_POST['stream_action']) && $_POST['stream_action'] === 'toggle' && isset($_POST['id']) && isnum($_POST['id'])) {
        $tg_id = intval($_POST['id']);
        $r = dbarray(dbquery("SELECT rs_status FROM ".DB_CXM_RADIOSTATUS." WHERE rs_id='".$tg_id."'"));
        if ($r) {
            dbquery("UPDATE ".DB_CXM_RADIOSTATUS." SET rs_status='".($r['rs_status'] ? 0 : 1)."' WHERE rs_id='".$tg_id."'");
        }
        redirect($admin_url.'&tab=streams');
    }

    // Stream speichern (INSERT / UPDATE)
    if ($tab === 'stream' && isset($_POST['save_stream'])) {
        $rs_name       = stripinput($_POST['rs_name']);
        $rs_source     = (isset($_POST['rs_source']) && $_POST['rs_source'] === 'lexycast') ? 'lexycast' : 'lautfm';
        $rs_station    = $rs_source === 'lexycast'
            ? stripinput(LexyCastAPI::normalizeStation($_POST['rs_station']))
            : stripinput($_POST['rs_station']);
        $rs_panel      = intval($_POST['rs_panel']);
        $rs_show_image = isset($_POST['rs_show_image']) ? 1 : 0;
        $rs_show_next  = isset($_POST['rs_show_next'])  ? 1 : 0;
        $rs_stream_url = stripinput($_POST['rs_stream_url']);
        $rs_status     = isset($_POST['rs_status'])     ? 1 : 0;
        $rs_status_wb  = isset($_POST['rs_status_wb'])  ? 1 : 0;
        $rs_wb_max     = intval($_POST['rs_wb_max']);
        $rs_wb_max_user= intval($_POST['rs_wb_max_user']);
        $rs_wb_popup   = isset($_POST['rs_wb_popup'])   ? 1 : 0;
        $rs_access     = intval($_POST['rs_access']);
        $rs_access_wb  = intval($_POST['rs_access_wb']);
        $rs_theme      = intval($_POST['rs_theme']);

        if (isset($_POST['rs_id']) && isnum($_POST['rs_id'])) {
            $rs_id = intval($_POST['rs_id']);
            // Cache verwerfen, damit Quelle/Station sofort neu abgefragt werden
            $old = dbarray(dbquery("SELECT rs_cache FROM ".DB_CXM_RADIOSTATUS." WHERE rs_id='".$rs_id."'"));
            if ($old) {
                $cf = CXM_RADIOSTATUS.'cache/stream_'.$rs_id.'_'.$old['rs_cache'].'.php';
                if (file_exists($cf)) { @unlink($cf); }
            }
            dbquery("UPDATE ".DB_CXM_RADIOSTATUS." SET"
                ." rs_name='".$rs_name."',"
                ." rs_source='".$rs_source."',"
                ." rs_station='".$rs_station."',"
                ." rs_panel='".$rs_panel."',"
                ." rs_show_image='".$rs_show_image."',"
                ." rs_show_next='".$rs_show_next."',"
                ." rs_stream_url='".$rs_stream_url."',"
                ." rs_status='".$rs_status."',"
                ." rs_status_wb='".$rs_status_wb."',"
                ." rs_wb_max='".$rs_wb_max."',"
                ." rs_wb_max_user='".$rs_wb_max_user."',"
                ." rs_wb_popup='".$rs_wb_popup."',"
                ." rs_access='".$rs_access."',"
                ." rs_access_wb='".$rs_access_wb."',"
                ." rs_theme='".$rs_theme."'"
                ." WHERE rs_id='".$rs_id."'");
        } else {
            $rs_cache = md5(uniqid('', true));
            $rs_order_r = dbarray(dbquery("SELECT MAX(rs_order) AS mo FROM ".DB_CXM_RADIOSTATUS));
            $rs_order = $rs_order_r ? (int)$rs_order_r['mo'] + 1 : 1;
            dbquery("INSERT INTO ".DB_CXM_RADIOSTATUS
                ." (rs_name, rs_source, rs_station, rs_panel, rs_order, rs_status, rs_cache, rs_theme,"
                ."  rs_access, rs_show_image, rs_show_next, rs_stream_url,"
                ."  rs_status_wb, rs_wb_max, rs_wb_max_user, rs_wb_popup, rs_access_wb)"
                ." VALUES ('".$rs_name."', '".$rs_source."', '".$rs_station."', '".$rs_panel."', '".$rs_order."',"
                ." '".$rs_status."', '".$rs_cache."', '".$rs_theme."',"
                ." '".$rs_access."', '".$rs_show_image."', '".$rs_show_next."', '".$rs_stream_url."',"
                ." '".$rs_status_wb."', '".$rs_wb_max."', '".$rs_wb_max_user."', '".$rs_wb_popup."', '".$rs_access_wb."')");
        }
        redirect($admin_url.'&tab=streams');
    }
}

// ── Tab: Einstellungen – Speichern ────────────────────────────────────────
if ($tab === 'settings' && isset($_POST['save_settings'])) {
    $new_settings = array(
        'reload_cache' => intval($_POST['reload_cache']),
        'reload_side'  => intval($_POST['reload_side']),
        'reload_main'  => intval($_POST['reload_main']),
        'api_base'     => stripinput($_POST['api_base']),
        'wb_time'      => stripinput($_POST['wb_time']),
    );
    foreach ($new_settings as $key => $val) {
        $exists = dbquery("SELECT settings_name FROM ".DB_SETTINGS_INF
            ." WHERE settings_name='".$key."' AND settings_inf='cxm_radiostatus_panel'");
        if (dbrows($exists)) {
            dbquery("UPDATE ".DB_SETTINGS_INF." SET settings_value='".$val."'"
                ." WHERE settings_name='".$key."' AND settings_inf='cxm_radiostatus_panel'");
        } else {
            dbquery("INSERT INTO ".DB_SETTINGS_INF." (settings_name, settings_value, settings_inf)"
                ." VALUES ('".$key."', '".$val."', 'cxm_radiostatus_panel')");
        }
    }
    $cxm_radiostatus_settings = array_merge($cxm_radiostatus_settings, $new_settings);
}

if ($tab === 'wunschbox') {
    redirect(CXM_RADIOSTATUS.'cxm_radiostatus_info.php');
}

// ── Ausgabe ────────────────────────────────────────────────────────────────
require_once THEMES.'templates/admin_header.php';

$stream_total = dbcount("(rs_id)", DB_CXM_RADIOSTATUS);
$stream_active = dbcount("(rs_id)", DB_CXM_RADIOSTATUS, "rs_status='1'");
$wb_unread = dbcount("(wb_id)", DB_CXM_RADIOSTATUS_WUNSCHBOX, "wb_status='0'");

echo '<div class="cxm-rs-admin">';
echo '<div class="cxm-rs-admin-hero">';
echo '<div class="cxm-rs-admin-hero-copy">';
echo '<span class="cxm-rs-admin-kicker">laut.fm · LexyCast</span>';
echo '<h1>'.$locale['cxmrs_title'].'</h1>';
echo '<p>Streams, Player, Panel-Ausgabe und Wunschbox zentral verwalten.</p>';
echo '</div>';
echo '<div class="cxm-rs-admin-stats">';
echo '<div class="cxm-rs-stat"><span>'.$stream_total.'</span><small>Streams</small></div>';
echo '<div class="cxm-rs-stat"><span>'.$stream_active.'</span><small>Aktiv</small></div>';
echo '<div class="cxm-rs-stat"><span>'.$wb_unread.'</span><small>Neue Wünsche</small></div>';
echo '</div>';
echo '</div>';

// Tab-Navigation
echo '<div class="cxm-rs-admin-toolbar">';
echo '<ul class="admin-tabs cxm-rs-admin-tabs">';
foreach (array('streams' => $locale['cxmrsa_streams'], 'stream' => $locale['cxmrsa_add'], 'settings' => $locale['cxmrsa_settings']) as $t => $label) {
    echo '<li'.($tab === $t ? ' class="active"' : '').'>'
        .'<a href="'.$admin_url.'&amp;tab='.$t.'">'.$label.'</a></li>';
}
echo '</ul>';
echo '<div class="cxm-rs-admin-actions"><a href="'.CXM_RADIOSTATUS.'cxm_radiostatus_info.php" class="button cxm-rs-button-secondary cxm-rs-tab-link">'.$locale['cxmrsa_wb_page_open'].'</a></div>';
echo '</div>';

// ══ TAB: Stream hinzufügen / bearbeiten ═══════════════════════════════════
if ($tab === 'stream') {
    $edit_data = null;
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) && isnum($_GET['id'])) {
        $edit_data = dbarray(dbquery("SELECT * FROM ".DB_CXM_RADIOSTATUS." WHERE rs_id='".intval($_GET['id'])."'"));
    }

    // Stream-Formular
    $form_action = $admin_form_action;
    echo '<div class="cxm-rs-admin-card cxm-rs-admin-card-form">';
    echo '<div class="cxm-rs-card-head"><div><span>'.$locale['cxmrsa_streams'].'</span><h2>'.($edit_data ? $locale['cxmrsa_edit'] : $locale['cxmrsa_add']).'</h2></div></div>';
    echo '<form method="post" action="'.$form_action.'" class="cxm-rs-admin-form">';
    echo '<input type="hidden" name="aid" value="'.iAUTH.'" />';
    echo '<input type="hidden" name="tab" value="stream" />';
    echo '<table cellpadding="4" cellspacing="1" width="100%" class="tbl-border cxm-rs-admin-table cxm-rs-admin-form-table">';

    if ($edit_data) {
        echo '<input type="hidden" name="rs_id" value="'.$edit_data['rs_id'].'" />';
    }

    $f = $edit_data ? $edit_data : array();
    $v = function($key, $default='') use ($f) { return htmlspecialchars(isset($f[$key]) ? $f[$key] : $default); };

    echo '<tr><td class="tbl2" width="30%">'.$locale['cxmrsa_name'].'</td>'
        .'<td class="tbl2"><input type="text" name="rs_name" value="'.$v('rs_name').'" class="textbox cxm-rs-input-medium" /></td></tr>';

    $source = $v('rs_source', 'lautfm') === 'lexycast' ? 'lexycast' : 'lautfm';
    echo '<tr><td class="tbl2">'.$locale['cxmrsa_source'].'</td><td class="tbl2"><select name="rs_source" id="rs_source" onchange="cxmRsSourceChange()">';
    foreach (array('lautfm' => $locale['cxmrsa_source_lautfm'], 'lexycast' => $locale['cxmrsa_source_lexycast']) as $sv => $sl) {
        echo '<option value="'.$sv.'"'.($source === $sv ? ' selected="selected"' : '').'>'.$sl.'</option>';
    }
    echo '</select></td></tr>';

    echo '<tr><td class="tbl2" id="rs_station_label">'.($source === 'lexycast' ? $locale['cxmrsa_station_lexycast'] : $locale['cxmrsa_station']).'</td>'
        .'<td class="tbl2"><input type="text" id="rs_station" name="rs_station" value="'.$v('rs_station').'" class="textbox cxm-rs-input-medium"'
        .' placeholder="'.($source === 'lexycast' ? 'https://c02.lexycast.de:4020' : '').'" />'
        .' <button type="button" class="button cxm-rs-button-ghost" onclick="cxmRsPreviewStation()">'.$locale['cxmrsa_preview'].'</button>'
        .'<br /><span class="small" id="rs_station_hint">'.($source === 'lexycast' ? $locale['cxmrsa_station_lexycast_hint'] : '').'</span></td></tr>';

    echo '<script type="text/javascript">
    var cxmRsSourceText = '.json_encode(array(
        'lautfm'   => array('label' => $locale['cxmrsa_station'], 'hint' => '', 'placeholder' => ''),
        'lexycast' => array('label' => $locale['cxmrsa_station_lexycast'], 'hint' => $locale['cxmrsa_station_lexycast_hint'], 'placeholder' => 'https://c02.lexycast.de:4020'),
    )).';
    function cxmRsSourceChange() {
        var t = cxmRsSourceText[document.getElementById("rs_source").value];
        document.getElementById("rs_station_label").textContent = t.label;
        document.getElementById("rs_station_hint").textContent = t.hint;
        document.getElementById("rs_station").setAttribute("placeholder", t.placeholder);
    }
    function cxmRsPreviewStation() {
        var value = document.getElementById("rs_station").value.replace(/^\s+|\s+$/g, "");
        if (document.getElementById("rs_source").value === "lexycast") {
            var server = value.match(/#(\d+)$/);
            value = value.replace(/#\d+$/, "").replace(/\/+$/, "").replace(/\/api(\/v\d+)?$/i, "");
            if (!/^https?:\/\//i.test(value)) { value = "https://" + value; }
            window.open(value + "/api/v2/" + (server ? "channels/?server=" + server[1] : "servers/"), "_blank");
        } else {
            window.open("https://api.laut.fm/station/" + encodeURIComponent(value), "_blank");
        }
    }
    </script>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_panel'].'</td><td class="tbl2"><select name="rs_panel">';
    foreach (array(1 => $locale['cxmrsa_panel_1'], 2 => $locale['cxmrsa_panel_2'], 3 => $locale['cxmrsa_panel_3'], 4 => $locale['cxmrsa_panel_4']) as $pv => $pl) {
        echo '<option value="'.$pv.'"'.($v('rs_panel','1') == $pv ? ' selected="selected"' : '').'>'.$pl.'</option>';
    }
    echo '</select></td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_show_image'].'</td>'
        .'<td class="tbl2"><input type="checkbox" name="rs_show_image" value="1"'
        .($v('rs_show_image','1') ? ' checked="checked"' : '').' /></td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_show_next'].'</td>'
        .'<td class="tbl2"><input type="checkbox" name="rs_show_next" value="1"'
        .($v('rs_show_next','1') ? ' checked="checked"' : '').' /></td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_stream_url'].'</td>'
        .'<td class="tbl2"><input type="text" name="rs_stream_url" value="'.$v('rs_stream_url').'" class="textbox cxm-rs-input-medium" /></td></tr>';

    if ($edit_data) {
        $embed_url = $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS).'cxm_radiostatus_player.php?id='.(int)$edit_data['rs_id'].'&typ=embed';
        $iframe_code = '<iframe src="'.$embed_url.'" width="280" height="280" frameborder="0" loading="lazy" scrolling="no" allow="autoplay"></iframe>';
        echo '<tr><td class="tbl2">'.$locale['cxmrsa_embed_code'].'</td>'
            .'<td class="tbl2"><textarea readonly="readonly" class="textbox cxm-rs-iframe-code" onclick="this.select()">'.htmlspecialchars($iframe_code).'</textarea><br /><span class="small">'.$locale['cxmrsa_embed_hint'].'</span></td></tr>';
    }

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_wb_active'].'</td>'
        .'<td class="tbl2"><input type="checkbox" name="rs_status_wb" value="1"'
        .($v('rs_status_wb','1') ? ' checked="checked"' : '').' /></td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_wb_max'].'</td>'
        .'<td class="tbl2"><input type="number" name="rs_wb_max" value="'.$v('rs_wb_max','100').'" class="textbox cxm-rs-input-small" /></td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_wb_max_user'].'</td>'
        .'<td class="tbl2"><input type="number" name="rs_wb_max_user" value="'.$v('rs_wb_max_user','3').'" class="textbox cxm-rs-input-small" /></td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_wb_popup'].'</td>'
        .'<td class="tbl2"><input type="checkbox" name="rs_wb_popup" value="1"'
        .($v('rs_wb_popup') ? ' checked="checked"' : '').' /></td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_access'].'</td>'
        .'<td class="tbl2">'.cxm_rs_access_select('rs_access', $v('rs_access', '0')).'</td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_access_wb'].'</td>'
        .'<td class="tbl2">'.cxm_rs_access_select('rs_access_wb', $v('rs_access_wb', '0')).'</td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_status'].'</td>'
        .'<td class="tbl2"><input type="checkbox" name="rs_status" value="1"'
        .($v('rs_status','0') ? ' checked="checked"' : '').' /></td></tr>';

    // Theme-Auswahl (einfach numerisch)
    echo '<tr><td class="tbl2">Theme</td>'
        .'<td class="tbl2"><input type="number" name="rs_theme" value="'.$v('rs_theme','1').'" class="textbox cxm-rs-input-tiny" min="1" /></td></tr>';

    echo '<tr><td class="tbl2 cxm-rs-form-submit" colspan="2" align="right">'
        .'<input type="submit" name="save_stream" value="'.$locale['cxmrsa_save'].'" class="button cxm-rs-button-primary" /></td></tr>';
    echo '</table></form></div>';

// ══ TAB: Streams ══════════════════════════════════════════════════════════
} elseif ($tab === 'streams') {

    // Stream-Liste
    echo '<div class="cxm-rs-admin-card cxm-rs-admin-card-list">';
    echo '<div class="cxm-rs-card-head"><div><span>'.$locale['cxmrsa_streams'].'</span><h2>Übersicht</h2></div></div>';
    $result = dbquery("SELECT * FROM ".DB_CXM_RADIOSTATUS." ORDER BY rs_panel, rs_order");
    if (dbrows($result)) {
        echo '<table cellpadding="3" cellspacing="1" width="100%" class="tbl-border cxm-rs-admin-table cxm-rs-stream-table">';
        echo '<tr>'
            .'<td class="tbl2"><strong>'.$locale['cxmrsa_name'].'</strong></td>'
            .'<td class="tbl2"><strong>'.$locale['cxmrsa_station'].'</strong></td>'
            .'<td class="tbl2"><strong>'.$locale['cxmrsa_panel'].'</strong></td>'
            .'<td class="tbl2"><strong>'.$locale['cxmrsa_status'].'</strong></td>'
            .'<td class="tbl2"></td>'
            .'</tr>';
        $panel_labels = array(1 => $locale['cxmrsa_panel_1'], 2 => $locale['cxmrsa_panel_2'], 3 => $locale['cxmrsa_panel_3'], 4 => $locale['cxmrsa_panel_4']);
        while ($row = dbarray($result)) {
            $status_class = $row['rs_status'] ? 'cxm-rs-badge-on' : 'cxm-rs-badge-off';
            $edit_url = $admin_url.'&amp;tab=stream&amp;id='.$row['rs_id'].'&amp;action=edit';
            $embed_url_raw = $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS).'cxm_radiostatus_player.php?id='.(int)$row['rs_id'].'&typ=embed';
            $embed_url_html = $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS).'cxm_radiostatus_player.php?id='.(int)$row['rs_id'].'&amp;typ=embed';
            $embed_code = '<iframe src="'.$embed_url_html.'" width="280" height="280" frameborder="0" scrolling="no" loading="lazy" allow="autoplay"></iframe>';
            $toggle_icon = $row['rs_status'] ? '&#x23FB;' : '&#x2714;';
            $toggle_title = $row['rs_status'] ? $locale['cxmrsa_disable'] : $locale['cxmrsa_enable'];

            echo '<tr>'
                .'<td class="tbl1"><strong>'.htmlspecialchars($row['rs_name']).'</strong></td>'
                .'<td class="tbl1"><span class="cxm-rs-badge cxm-rs-badge-source">'
                .(isset($row['rs_source']) && $row['rs_source'] === 'lexycast' ? $locale['cxmrsa_source_lexycast'] : $locale['cxmrsa_source_lautfm'])
                .'</span> <code>'.htmlspecialchars($row['rs_station']).'</code></td>'
                .'<td class="tbl1">'.(isset($panel_labels[$row['rs_panel']]) ? $panel_labels[$row['rs_panel']] : $row['rs_panel']).'</td>'
                .'<td class="tbl1"><span class="cxm-rs-badge '.$status_class.'">'.($row['rs_status'] ? $locale['cxmrsa_active'] : $locale['cxmrsa_inactive']).'</span></td>'
                .'<td class="tbl1">'
                .'<div class="cxm-rs-row-actions">'
                ."<a href=\"#\" class=\"cxm-rs-preview-btn\" title=\"".$locale['cxmrsa_preview']."\" aria-label=\"".$locale['cxmrsa_preview']."\" onclick='return cxmRsOpenPreviewModal(".(int)$row['rs_id'].", ".json_encode($row['rs_name']).", ".json_encode($embed_url_raw).", ".json_encode($embed_code).");'>"
                .'<span class="cxm-rs-icon" aria-hidden="true">&lt;/&gt;</span>'
                .'</a>'
                .cxm_rs_stream_action_button($row['rs_id'], 'up', '&#9650;', 'cxm-rs-move-btn', $locale['cxmrsa_order_up'])
                .cxm_rs_stream_action_button($row['rs_id'], 'down', '&#9660;', 'cxm-rs-move-btn', $locale['cxmrsa_order_down'])
                .'<a href="'.$edit_url.'" class="cxm-rs-action-btn" title="'.$locale['cxmrsa_edit'].'" aria-label="'.$locale['cxmrsa_edit'].'">&#9998;</a>'
                .cxm_rs_stream_action_button($row['rs_id'], 'toggle', $toggle_icon, 'cxm-rs-action-btn', $toggle_title)
                .cxm_rs_stream_action_button($row['rs_id'], 'delete', '&#128465;', 'cxm-rs-action-btn cxm-rs-danger-btn', $locale['cxmrsa_delete'], $locale['cxmrsa_confirm_del'])
                .'</div>'
                .'</td></tr>';
        }
        echo '</table>';
    } else {
        echo '<div class="cxm-rs-empty-state">'.$locale['cxmrsa_no_streams'].'</div>';
    }
    echo '</div>';

    echo '<div id="cxm-rs-preview-modal" class="cxm-rs-modal cxm-rs-hidden">';
    echo '<div class="cxm-rs-modal-backdrop" onclick="return cxmRsClosePreviewModal();"></div>';
    echo '<div class="cxm-rs-modal-dialog">';
    echo '<div class="cxm-rs-modal-head">';
    echo '<div><span>'.$locale['cxmrsa_preview'].'</span><h2 data-role="title">'.$locale['cxmrsa_preview'].'</h2></div>';
    echo '<button type="button" class="cxm-rs-modal-close" onclick="return cxmRsClosePreviewModal();" title="'.$locale['cxmrsa_close'].'">&#215;</button>';
    echo '</div>';
    echo '<div class="cxm-rs-modal-grid">';
    echo '<div class="cxm-rs-modal-preview"><iframe data-role="frame" src="about:blank" loading="lazy" scrolling="no" allow="autoplay"></iframe></div>';
    echo '<div class="cxm-rs-modal-code">';
    echo '<label>'.$locale['cxmrsa_embed_code'].'</label>';
    echo '<textarea data-role="code" readonly="readonly" class="textbox cxm-rs-modal-textarea" onclick="this.select();"></textarea>';
    echo '<p class="cxm-rs-modal-hint">'.$locale['cxmrsa_embed_hint'].'</p>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

// ══ TAB: Einstellungen ════════════════════════════════════════════════════
} elseif ($tab === 'settings') {
    echo '<div class="cxm-rs-admin-card">';
    echo '<div class="cxm-rs-card-head"><div><span>'.$locale['cxmrsa_settings'].'</span><h2>'.$locale['cxmrsa_settings'].'</h2></div></div>';
    echo '<form method="post" action="'.$admin_form_action.'" class="cxm-rs-admin-form">';
    echo '<input type="hidden" name="aid" value="'.iAUTH.'" />';
    echo '<input type="hidden" name="tab" value="settings" />';
    echo '<table cellpadding="4" cellspacing="1" width="100%" class="tbl-border cxm-rs-admin-table cxm-rs-admin-form-table">';

    $sf = $cxm_radiostatus_settings;
    echo '<tr><td class="tbl2" width="40%">'.$locale['cxmrsa_reload_cache'].'</td>'
        .'<td class="tbl2"><input type="number" name="reload_cache" value="'.htmlspecialchars($sf['reload_cache']).'" class="textbox cxm-rs-input-number" /></td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_reload_side'].'</td>'
        .'<td class="tbl2"><input type="number" name="reload_side" value="'.htmlspecialchars($sf['reload_side']).'" class="textbox cxm-rs-input-number" /></td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_reload_main'].'</td>'
        .'<td class="tbl2"><input type="number" name="reload_main" value="'.htmlspecialchars($sf['reload_main']).'" class="textbox cxm-rs-input-number" /></td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_api_base'].'</td>'
        .'<td class="tbl2"><input type="text" name="api_base" value="'.htmlspecialchars($sf['api_base']).'" class="textbox cxm-rs-input-medium" /></td></tr>';

    echo '<tr><td class="tbl2">'.$locale['cxmrsa_wb_time'].'</td>'
        .'<td class="tbl2"><input type="text" name="wb_time" value="'.htmlspecialchars($sf['wb_time']).'" class="textbox cxm-rs-input-date" /></td></tr>';

    echo '<tr><td class="tbl2" colspan="2" align="right">'
        .'<input type="submit" name="save_settings" value="'.$locale['cxmrsa_save'].'" class="button cxm-rs-button-primary" /></td></tr>';
    echo '</table></form></div>';
}

echo '</div>';

require_once THEMES.'templates/footer.php';
?>
