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
require_once CXM_RADIOSTATUS.'cxm_radiostatus_themes.php';
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
        $rs_theme      = cxm_rs_layout_id(isset($_POST['rs_theme']) ? $_POST['rs_theme'] : 10);
        $rs_skin       = cxm_rs_skin_key(isset($_POST['rs_skin']) ? $_POST['rs_skin'] : 'modern');
        $rs_player_skin = (isset($_POST['rs_player_skin']) && $_POST['rs_player_skin'] !== '') ? cxm_rs_skin_key($_POST['rs_player_skin']) : '';

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
                ." rs_theme='".$rs_theme."',"
                ." rs_skin='".$rs_skin."',"
                ." rs_player_skin='".$rs_player_skin."'"
                ." WHERE rs_id='".$rs_id."'");
        } else {
            $rs_cache = md5(uniqid('', true));
            $rs_order_r = dbarray(dbquery("SELECT MAX(rs_order) AS mo FROM ".DB_CXM_RADIOSTATUS));
            $rs_order = $rs_order_r ? (int)$rs_order_r['mo'] + 1 : 1;
            dbquery("INSERT INTO ".DB_CXM_RADIOSTATUS
                ." (rs_name, rs_source, rs_station, rs_panel, rs_order, rs_status, rs_cache, rs_theme, rs_skin, rs_player_skin,"
                ."  rs_access, rs_show_image, rs_show_next, rs_stream_url,"
                ."  rs_status_wb, rs_wb_max, rs_wb_max_user, rs_wb_popup, rs_access_wb)"
                ." VALUES ('".$rs_name."', '".$rs_source."', '".$rs_station."', '".$rs_panel."', '".$rs_order."',"
                ." '".$rs_status."', '".$rs_cache."', '".$rs_theme."', '".$rs_skin."', '".$rs_player_skin."',"
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
add_to_head('<script type="text/javascript" src="'.CXM_RADIOSTATUS.'js/cxm_radiostatus.js"></script>');
require_once THEMES.'templates/admin_header.php';

$stream_total = dbcount("(rs_id)", DB_CXM_RADIOSTATUS);
$stream_active = dbcount("(rs_id)", DB_CXM_RADIOSTATUS, "rs_status='1'");
$wb_unread = dbcount("(wb_id)", DB_CXM_RADIOSTATUS_WUNSCHBOX, "wb_status='0'");

echo '<div class="cxm-a">';
echo '<header class="cxm-a-top">';
echo '<div class="cxm-a-title"><h1>'.$locale['cxmrs_title'].'</h1><p>'.$locale['cxmrsa_lead'].'</p></div>';
echo '<dl class="cxm-a-stats">';
echo '<div><dt>'.$locale['cxmrsa_stat_streams'].'</dt><dd>'.$stream_total.'</dd></div>';
echo '<div><dt>'.$locale['cxmrsa_stat_active'].'</dt><dd>'.$stream_active.'</dd></div>';
echo '<div'.($wb_unread > 0 ? ' class="is-hot"' : '').'><dt>'.$locale['cxmrsa_stat_wishes'].'</dt><dd>'.$wb_unread.'</dd></div>';
echo '</dl>';
echo '</header>';

// Tab-Navigation
echo '<nav class="cxm-a-nav">';
echo '<ul class="cxm-a-tabs">';
foreach (array('streams' => $locale['cxmrsa_streams'], 'stream' => $locale['cxmrsa_add'], 'settings' => $locale['cxmrsa_settings']) as $t => $label) {
    echo '<li><a href="'.$admin_url.'&amp;tab='.$t.'"'.($tab === $t ? ' class="is-active" aria-current="page"' : '').'>'.$label.'</a></li>';
}
echo '</ul>';
echo '<a href="'.CXM_RADIOSTATUS.'cxm_radiostatus_info.php" class="cxm-a-btn cxm-a-btn-quiet">'.cxm_rs_icon('wish').'<span>'.$locale['cxmrsa_wb_page_open'].'</span></a>';
echo '</nav>';

// ══ TAB: Stream hinzufügen / bearbeiten ═══════════════════════════════════
if ($tab === 'stream') {
    $edit_data = null;
    if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) && isnum($_GET['id'])) {
        $edit_data = dbarray(dbquery("SELECT * FROM ".DB_CXM_RADIOSTATUS." WHERE rs_id='".intval($_GET['id'])."'"));
    }

    $f = $edit_data ? $edit_data : array();
    $v = function($key, $default='') use ($f) { return htmlspecialchars(isset($f[$key]) ? $f[$key] : $default); };
    $checked = function($key, $default) use ($v) { return $v($key, $default) ? ' checked="checked"' : ''; };
    $row = function($label, $control, $hint = '', $for = '') {
        return '<div class="cxm-a-field">'
            .'<label class="cxm-a-label"'.($for !== '' ? ' for="'.$for.'"' : '').'>'.$label.'</label>'
            .'<div class="cxm-a-control">'.$control.($hint !== '' ? '<p class="cxm-a-hint">'.$hint.'</p>' : '').'</div>'
            .'</div>';
    };
    $switch = function($name, $label, $is_checked) {
        return '<label class="cxm-a-switch"><input type="checkbox" name="'.$name.'" value="1"'.$is_checked.' /><span class="cxm-a-switch-ui" aria-hidden="true"></span><span>'.$label.'</span></label>';
    };

    echo '<form method="post" action="'.$admin_form_action.'" class="cxm-a-form">';
    echo '<input type="hidden" name="aid" value="'.iAUTH.'" />';
    echo '<input type="hidden" name="tab" value="stream" />';
    if ($edit_data) {
        echo '<input type="hidden" name="rs_id" value="'.(int)$edit_data['rs_id'].'" />';
    }

    echo '<div class="cxm-a-formhead"><h2>'.($edit_data ? $locale['cxmrsa_edit'].': '.$v('rs_name') : $locale['cxmrsa_add']).'</h2>'
        .'<button type="submit" name="save_stream" value="1" class="cxm-a-btn cxm-a-btn-primary">'.$locale['cxmrsa_save'].'</button></div>';

    // Stream
    $source = $v('rs_source', 'lautfm') === 'lexycast' ? 'lexycast' : 'lautfm';
    echo '<section class="cxm-a-card"><h3>'.$locale['cxmrsa_sec_stream'].'</h3>';
    echo $row($locale['cxmrsa_name'], '<input type="text" id="rs_name" name="rs_name" value="'.$v('rs_name').'" class="textbox cxm-a-input" required="required" />', '', 'rs_name');

    $source_ctl = '<div class="cxm-a-segment" role="radiogroup">';
    foreach (array('lautfm' => $locale['cxmrsa_source_lautfm'], 'lexycast' => $locale['cxmrsa_source_lexycast']) as $sv => $sl) {
        $source_ctl .= '<label><input type="radio" name="rs_source" value="'.$sv.'"'.($source === $sv ? ' checked="checked"' : '').' onchange="cxmRsSourceChange()" /><span>'.$sl.'</span></label>';
    }
    $source_ctl .= '</div>';
    echo $row($locale['cxmrsa_source'], $source_ctl);

    echo '<div class="cxm-a-field">'
        .'<label class="cxm-a-label" id="rs_station_label" for="rs_station">'.($source === 'lexycast' ? $locale['cxmrsa_station_lexycast'] : $locale['cxmrsa_station']).'</label>'
        .'<div class="cxm-a-control"><div class="cxm-a-inline">'
        .'<input type="text" id="rs_station" name="rs_station" value="'.$v('rs_station').'" class="textbox cxm-a-input" placeholder="'.($source === 'lexycast' ? 'https://c02.lexycast.de:4020' : '').'" />'
        .'<button type="button" class="cxm-a-btn" onclick="cxmRsPreviewStation()">'.cxm_rs_icon('popup').'<span>'.$locale['cxmrsa_test'].'</span></button>'
        .'</div><p class="cxm-a-hint" id="rs_station_hint">'.($source === 'lexycast' ? $locale['cxmrsa_station_lexycast_hint'] : '').'</p></div>'
        .'</div>';
    echo $row($locale['cxmrsa_stream_url'], '<input type="text" id="rs_stream_url" name="rs_stream_url" value="'.$v('rs_stream_url').'" class="textbox cxm-a-input" />', '', 'rs_stream_url');
    echo '</section>';

    // Anzeige
    $panel_ctl = '<select name="rs_panel" id="rs_panel" class="textbox cxm-a-input cxm-a-input-auto" onchange="cxmRsDesignPos()">';
    foreach (array(1 => $locale['cxmrsa_panel_1'], 2 => $locale['cxmrsa_panel_2'], 3 => $locale['cxmrsa_panel_3'], 4 => $locale['cxmrsa_panel_4']) as $pv => $pl) {
        $panel_ctl .= '<option value="'.$pv.'"'.($v('rs_panel', '1') == $pv ? ' selected="selected"' : '').'>'.$pl.'</option>';
    }
    $panel_ctl .= '</select>';
    echo '<section class="cxm-a-card"><h3>'.$locale['cxmrsa_sec_display'].'</h3>';
    echo $row($locale['cxmrsa_panel'], $panel_ctl, '', 'rs_panel');
    echo $row($locale['cxmrsa_access'], cxm_rs_access_select('rs_access', $v('rs_access', '0')));
    echo '<div class="cxm-a-field"><span class="cxm-a-label">'.$locale['cxmrsa_options'].'</span><div class="cxm-a-control cxm-a-switches">'
        .$switch('rs_status', $locale['cxmrsa_status_on'], $checked('rs_status', '0'))
        .$switch('rs_show_image', $locale['cxmrsa_show_image'], $checked('rs_show_image', '1'))
        .$switch('rs_show_next', $locale['cxmrsa_show_next'], $checked('rs_show_next', '1'))
        .'</div></div>';
    echo '</section>';

    // Design
    $cur_layout = cxm_rs_layout_id($v('rs_theme', '10'));
    $cur_skin = cxm_rs_skin_key($v('rs_skin', 'modern'));
    $cur_pos = in_array((int)$v('rs_panel', '1'), array(1, 4), true) ? 'side' : 'main';
    echo '<section class="cxm-a-card cxm-a-design" id="cxm-a-design" data-layout="'.$cur_layout.'" data-skin="'.$cur_skin.'" data-pos="'.$cur_pos.'">';
    echo '<h3>'.$locale['cxmrsd_design'].'</h3>';
    echo '<div class="cxm-a-design-grid">';
    echo '<div class="cxm-a-design-pick">';
    echo '<div class="cxm-a-group" role="radiogroup" aria-labelledby="cxm-a-layout-label"><div class="cxm-a-group-label" id="cxm-a-layout-label">'.$locale['cxmrsd_layout'].'</div><div class="cxm-a-layouts">';
    foreach (cxm_rs_layouts() as $lid => $layout) {
        echo '<label class="cxm-a-option cxm-a-layout'.(!empty($layout['classic']) ? ' is-classic' : '').'">'
            .'<input type="radio" name="rs_theme" value="'.$lid.'"'.($cur_layout === $lid ? ' checked="checked"' : '').' />'
            .'<span class="cxm-a-thumb" aria-hidden="true"><span class="cxm-a-thumb-scale">'.cxm_rs_render(cxm_rs_demo_cache($cur_skin, $lid), $lid === 3 ? 2 : 1).'</span></span>'
            .'<span class="cxm-a-option-name">'.$layout['label'].'</span>'
            .'<span class="cxm-a-option-desc">'.$layout['desc'].'</span>'
            .'</label>';
    }
    echo '</div></div>';
    echo '<div class="cxm-a-group" role="radiogroup" aria-labelledby="cxm-a-skin-label"><div class="cxm-a-group-label" id="cxm-a-skin-label">'.$locale['cxmrsd_skin'].'</div><div class="cxm-a-skins">';
    foreach (cxm_rs_skins() as $sk => $skin) {
        echo '<label class="cxm-a-option cxm-a-skin">'
            .'<input type="radio" name="rs_skin" value="'.$sk.'"'.($cur_skin === $sk ? ' checked="checked"' : '').' />'
            .'<span class="cxm-a-swatch" aria-hidden="true">';
        foreach ($skin['swatch'] as $color) {
            echo '<i style="background:'.$color.'"></i>';
        }
        echo '</span><span class="cxm-a-option-name">'.$skin['label'].'</span>'
            .'<span class="cxm-a-option-desc">'.$skin['desc'].'</span>'
            .'</label>';
    }
    echo '</div><p class="cxm-a-hint cxm-a-classic-note">'.$locale['cxmrsd_skin_classic_note'].'</p></div>';
    $cur_player_skin = $v('rs_player_skin', '');
    echo '<div class="cxm-a-group"><label class="cxm-a-group-label" for="rs_player_skin">'.$locale['cxmrsd_player_skin'].'</label>'
        .'<select name="rs_player_skin" id="rs_player_skin" class="textbox cxm-a-input cxm-a-input-auto">'
        .'<option value="">'.$locale['cxmrsd_player_skin_same'].'</option>';
    foreach (cxm_rs_skins() as $sk => $skin) {
        echo '<option value="'.$sk.'"'.($cur_player_skin === $sk ? ' selected="selected"' : '').'>'.$skin['label'].'</option>';
    }
    echo '</select><p class="cxm-a-hint">'.$locale['cxmrsd_player_skin_hint'].'</p></div>';
    echo '</div>';

    echo '<div class="cxm-a-preview">';
    echo '<div class="cxm-a-preview-head"><span>'.$locale['cxmrsd_preview'].'</span>'
        .'<div class="cxm-a-segment cxm-a-segment-sm" role="group">'
        .'<button type="button" data-pos="side" onclick="cxmRsDesignPos(\'side\')">'.$locale['cxmrsd_preview_side'].'</button>'
        .'<button type="button" data-pos="main" onclick="cxmRsDesignPos(\'main\')">'.$locale['cxmrsd_preview_main'].'</button>'
        .'</div></div>';
    echo '<div class="cxm-a-preview-stage">';
    foreach (cxm_rs_layouts() as $lid => $layout) {
        foreach (array('side' => 1, 'main' => 2) as $pos => $ptyp) {
            echo '<div class="cxm-a-preview-item" data-layout="'.$lid.'" data-pos="'.$pos.'">'.cxm_rs_render(cxm_rs_demo_cache($cur_skin, $lid), $ptyp).'</div>';
        }
    }
    echo '</div></div>';
    echo '</div>';
    echo '</section>';

    // Wunschbox
    echo '<section class="cxm-a-card"><h3>'.$locale['cxmrswb_title'].'</h3>';
    echo '<div class="cxm-a-field"><span class="cxm-a-label">'.$locale['cxmrsa_options'].'</span><div class="cxm-a-control cxm-a-switches">'
        .$switch('rs_status_wb', $locale['cxmrsa_wb_active'], $checked('rs_status_wb', '1'))
        .$switch('rs_wb_popup', $locale['cxmrsa_wb_popup'], $checked('rs_wb_popup', ''))
        .'</div></div>';
    echo $row($locale['cxmrsa_wb_max'], '<input type="number" id="rs_wb_max" name="rs_wb_max" min="0" value="'.$v('rs_wb_max', '100').'" class="textbox cxm-a-input cxm-a-input-num" />', $locale['cxmrsa_zero_unlimited'], 'rs_wb_max');
    echo $row($locale['cxmrsa_wb_max_user'], '<input type="number" id="rs_wb_max_user" name="rs_wb_max_user" min="0" value="'.$v('rs_wb_max_user', '3').'" class="textbox cxm-a-input cxm-a-input-num" />', $locale['cxmrsa_zero_unlimited'], 'rs_wb_max_user');
    echo $row($locale['cxmrsa_access_wb'], cxm_rs_access_select('rs_access_wb', $v('rs_access_wb', '0')));
    echo '</section>';

    if ($edit_data) {
        $embed_url = $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS).'cxm_radiostatus_player.php?id='.(int)$edit_data['rs_id'].'&typ=embed';
        $iframe_code = '<iframe src="'.$embed_url.'" width="280" height="280" frameborder="0" loading="lazy" scrolling="no" allow="autoplay"></iframe>';
        echo '<section class="cxm-a-card"><h3>'.$locale['cxmrsa_embed_code'].'</h3>';
        echo '<textarea readonly="readonly" class="textbox cxm-a-code" onclick="this.select()">'.htmlspecialchars($iframe_code).'</textarea>';
        echo '<p class="cxm-a-hint">'.$locale['cxmrsa_embed_hint'].'</p>';
        echo '</section>';
    }

    echo '<div class="cxm-a-savebar"><a href="'.$admin_url.'&amp;tab=streams" class="cxm-a-btn cxm-a-btn-quiet">'.$locale['cxmrsa_cancel'].'</a>'
        .'<button type="submit" name="save_stream" value="1" class="cxm-a-btn cxm-a-btn-primary">'.$locale['cxmrsa_save'].'</button></div>';
    echo '</form>';

    echo '<script type="text/javascript">
    var cxmRsSourceText = '.json_encode(array(
        'lautfm'   => array('label' => $locale['cxmrsa_station'], 'hint' => '', 'placeholder' => ''),
        'lexycast' => array('label' => $locale['cxmrsa_station_lexycast'], 'hint' => $locale['cxmrsa_station_lexycast_hint'], 'placeholder' => 'https://c02.lexycast.de:4020'),
    )).';
    function cxmRsSource() {
        var r = document.querySelector("input[name=rs_source]:checked");
        return r ? r.value : "lautfm";
    }
    function cxmRsSourceChange() {
        var t = cxmRsSourceText[cxmRsSource()];
        document.getElementById("rs_station_label").textContent = t.label;
        document.getElementById("rs_station_hint").textContent = t.hint;
        document.getElementById("rs_station").setAttribute("placeholder", t.placeholder);
    }
    function cxmRsPreviewStation() {
        var value = document.getElementById("rs_station").value.replace(/^\s+|\s+$/g, "");
        if (cxmRsSource() === "lexycast") {
            var server = value.match(/#(\d+)$/);
            value = value.replace(/#\d+$/, "").replace(/\/+$/, "").replace(/\/api(\/v\d+)?$/i, "");
            if (!/^https?:\/\//i.test(value)) { value = "https://" + value; }
            window.open(value + "/api/v2/" + (server ? "channels/?server=" + server[1] : "servers/"), "_blank");
        } else {
            window.open("https://api.laut.fm/station/" + encodeURIComponent(value), "_blank");
        }
    }
    (function () {
        var box = document.getElementById("cxm-a-design");
        function setSkin(skin) {
            box.setAttribute("data-skin", skin);
            var els = box.querySelectorAll(".cxm-x");
            for (var i = 0; i < els.length; i++) {
                els[i].className = els[i].className.replace(/\bcxm-skin-[a-z]+\b/, "cxm-skin-" + skin);
            }
            if (window.cxmRs) { cxmRs.refresh(box); }
        }
        function showPreview() {
            var items = box.querySelectorAll(".cxm-a-preview-item");
            for (var i = 0; i < items.length; i++) {
                var on = items[i].getAttribute("data-layout") === box.getAttribute("data-layout")
                    && items[i].getAttribute("data-pos") === box.getAttribute("data-pos");
                items[i].className = items[i].className.replace(/\s*is-shown/g, "") + (on ? " is-shown" : "");
            }
            if (window.cxmRs) { window.setTimeout(function () { cxmRs.refresh(box); }, 0); }
        }
        window.cxmRsDesignPos = function (pos) {
            if (!pos) {
                var p = document.getElementById("rs_panel").value;
                pos = (p === "1" || p === "4") ? "side" : "main";
            }
            box.setAttribute("data-pos", pos);
            var btns = box.querySelectorAll(".cxm-a-preview-head button");
            for (var i = 0; i < btns.length; i++) {
                btns[i].setAttribute("aria-pressed", btns[i].getAttribute("data-pos") === pos ? "true" : "false");
            }
            showPreview();
        };
        box.addEventListener("change", function (e) {
            if (e.target.name === "rs_theme") {
                box.setAttribute("data-layout", e.target.value);
                showPreview();
            } else if (e.target.name === "rs_skin") {
                setSkin(e.target.value);
            }
        });
        cxmRsDesignPos(box.getAttribute("data-pos"));
    })();
    </script>';

// ══ TAB: Streams ══════════════════════════════════════════════════════════
} elseif ($tab === 'streams') {

    $layouts = cxm_rs_layouts();
    $skins = cxm_rs_skins();
    $result = dbquery("SELECT * FROM ".DB_CXM_RADIOSTATUS." ORDER BY rs_panel, rs_order");
    echo '<section class="cxm-a-card cxm-a-card-flush">';
    echo '<div class="cxm-a-cardhead"><h2>'.$locale['cxmrsa_streams'].'</h2><a href="'.$admin_url.'&amp;tab=stream" class="cxm-a-btn cxm-a-btn-primary">'.$locale['cxmrsa_add'].'</a></div>';
    if (dbrows($result)) {
        echo '<div class="cxm-a-tablewrap"><table class="cxm-a-table">';
        echo '<thead><tr>'
            .'<th scope="col">'.$locale['cxmrsa_name'].'</th>'
            .'<th scope="col">'.$locale['cxmrsa_source'].'</th>'
            .'<th scope="col">'.$locale['cxmrsa_panel'].'</th>'
            .'<th scope="col">'.$locale['cxmrsd_design'].'</th>'
            .'<th scope="col">'.$locale['cxmrsa_status'].'</th>'
            .'<th scope="col"><span class="cxm-x-sr">'.$locale['cxmrsa_actions'].'</span></th>'
            .'</tr></thead><tbody>';
        $panel_labels = array(1 => $locale['cxmrsa_panel_1'], 2 => $locale['cxmrsa_panel_2'], 3 => $locale['cxmrsa_panel_3'], 4 => $locale['cxmrsa_panel_4']);
        while ($row = dbarray($result)) {
            $edit_url = $admin_url.'&amp;tab=stream&amp;id='.$row['rs_id'].'&amp;action=edit';
            $embed_url_raw = $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS).'cxm_radiostatus_player.php?id='.(int)$row['rs_id'].'&typ=embed';
            $embed_url_html = $settings['siteurl'].str_replace('../', '', CXM_RADIOSTATUS).'cxm_radiostatus_player.php?id='.(int)$row['rs_id'].'&amp;typ=embed';
            $embed_code = '<iframe src="'.$embed_url_html.'" width="280" height="280" frameborder="0" scrolling="no" loading="lazy" allow="autoplay"></iframe>';
            $toggle_title = $row['rs_status'] ? $locale['cxmrsa_disable'] : $locale['cxmrsa_enable'];
            $lid = cxm_rs_layout_id($row['rs_theme']);
            $design = $layouts[$lid]['label'];
            if (empty($layouts[$lid]['classic'])) {
                $sk = cxm_rs_skin_key(isset($row['rs_skin']) ? $row['rs_skin'] : 'modern');
                $design = '<i class="cxm-a-dot" style="background:'.$skins[$sk]['swatch'][0].';box-shadow:inset 0 0 0 3px '.$skins[$sk]['swatch'][2].'"></i>'.$design.' · '.$skins[$sk]['label'];
            }
            $is_lc = isset($row['rs_source']) && $row['rs_source'] === 'lexycast';

            echo '<tr>'
                .'<td><a class="cxm-a-rowlink" href="'.$edit_url.'">'.htmlspecialchars($row['rs_name']).'</a></td>'
                .'<td><span class="cxm-a-tag'.($is_lc ? ' is-lc' : '').'">'.($is_lc ? $locale['cxmrsa_source_lexycast'] : $locale['cxmrsa_source_lautfm']).'</span><code class="cxm-a-mono">'.htmlspecialchars($row['rs_station']).'</code></td>'
                .'<td>'.(isset($panel_labels[$row['rs_panel']]) ? $panel_labels[$row['rs_panel']] : (int)$row['rs_panel']).'</td>'
                .'<td class="cxm-a-design-cell">'.$design.'</td>'
                .'<td><span class="cxm-a-state'.($row['rs_status'] ? ' is-on' : '').'">'.($row['rs_status'] ? $locale['cxmrsa_active'] : $locale['cxmrsa_inactive']).'</span></td>'
                .'<td><div class="cxm-a-actions">'
                ."<button type=\"button\" class=\"cxm-a-icon-btn\" title=\"".$locale['cxmrsa_embed_code']."\" aria-label=\"".$locale['cxmrsa_embed_code']."\" onclick='return cxmRsOpenPreviewModal(".(int)$row['rs_id'].", ".json_encode($row['rs_name'], JSON_HEX_APOS).", ".json_encode($embed_url_raw, JSON_HEX_APOS).", ".json_encode($embed_code, JSON_HEX_APOS).");'>".cxm_rs_icon('code').'</button>'
                .cxm_rs_stream_action_button($row['rs_id'], 'up', cxm_rs_icon('up'), 'cxm-a-icon-btn', $locale['cxmrsa_order_up'])
                .cxm_rs_stream_action_button($row['rs_id'], 'down', cxm_rs_icon('down'), 'cxm-a-icon-btn', $locale['cxmrsa_order_down'])
                .'<a href="'.$edit_url.'" class="cxm-a-icon-btn" title="'.$locale['cxmrsa_edit'].'" aria-label="'.$locale['cxmrsa_edit'].'">'.cxm_rs_icon('edit').'</a>'
                .cxm_rs_stream_action_button($row['rs_id'], 'toggle', cxm_rs_icon('power'), 'cxm-a-icon-btn'.($row['rs_status'] ? ' is-on' : ''), $toggle_title)
                .cxm_rs_stream_action_button($row['rs_id'], 'delete', cxm_rs_icon('trash'), 'cxm-a-icon-btn is-danger', $locale['cxmrsa_delete'], $locale['cxmrsa_confirm_del'])
                .'</div></td></tr>';
        }
        echo '</tbody></table></div>';
    } else {
        echo '<div class="cxm-a-empty"><p>'.$locale['cxmrsa_no_streams'].'</p><a href="'.$admin_url.'&amp;tab=stream" class="cxm-a-btn cxm-a-btn-primary">'.$locale['cxmrsa_add'].'</a></div>';
    }
    echo '</section>';

    echo '<div id="cxm-rs-preview-modal" class="cxm-a-modal cxm-rs-hidden" role="dialog" aria-modal="true" aria-labelledby="cxm-a-modal-title">';
    echo '<div class="cxm-a-modal-backdrop" onclick="return cxmRsClosePreviewModal();"></div>';
    echo '<div class="cxm-a-modal-dialog">';
    echo '<div class="cxm-a-modal-head">';
    echo '<h2 id="cxm-a-modal-title" data-role="title">'.$locale['cxmrsa_preview'].'</h2>';
    echo '<button type="button" class="cxm-a-icon-btn" onclick="return cxmRsClosePreviewModal();" title="'.$locale['cxmrsa_close'].'" aria-label="'.$locale['cxmrsa_close'].'">'.cxm_rs_icon('close').'</button>';
    echo '</div>';
    echo '<div class="cxm-a-modal-grid">';
    echo '<div class="cxm-a-modal-preview"><iframe data-role="frame" src="about:blank" title="'.$locale['cxmrsa_preview'].'" loading="lazy" scrolling="no" allow="autoplay"></iframe></div>';
    echo '<div class="cxm-a-modal-code">';
    echo '<label for="cxm-a-modal-code">'.$locale['cxmrsa_embed_code'].'</label>';
    echo '<textarea id="cxm-a-modal-code" data-role="code" readonly="readonly" class="textbox cxm-a-code" onclick="this.select();"></textarea>';
    echo '<p class="cxm-a-hint">'.$locale['cxmrsa_embed_hint'].'</p>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

// ══ TAB: Einstellungen ════════════════════════════════════════════════════
} elseif ($tab === 'settings') {
    $sf = $cxm_radiostatus_settings;
    $field = function($label, $control, $for) {
        return '<div class="cxm-a-field"><label class="cxm-a-label" for="'.$for.'">'.$label.'</label><div class="cxm-a-control">'.$control.'</div></div>';
    };
    echo '<form method="post" action="'.$admin_form_action.'" class="cxm-a-form">';
    echo '<input type="hidden" name="aid" value="'.iAUTH.'" />';
    echo '<input type="hidden" name="tab" value="settings" />';
    if (isset($_POST['save_settings'])) {
        echo '<div class="cxm-a-notice" role="status">'.$locale['cxmrsa_saved'].'</div>';
    }
    echo '<section class="cxm-a-card"><h3>'.$locale['cxmrsa_settings'].'</h3>';
    echo $field($locale['cxmrsa_reload_cache'], '<input type="number" id="reload_cache" name="reload_cache" min="5" value="'.htmlspecialchars($sf['reload_cache']).'" class="textbox cxm-a-input cxm-a-input-num" />', 'reload_cache');
    echo $field($locale['cxmrsa_reload_side'], '<input type="number" id="reload_side" name="reload_side" min="5000" step="1000" value="'.htmlspecialchars($sf['reload_side']).'" class="textbox cxm-a-input cxm-a-input-num" />', 'reload_side');
    echo $field($locale['cxmrsa_reload_main'], '<input type="number" id="reload_main" name="reload_main" min="5000" step="1000" value="'.htmlspecialchars($sf['reload_main']).'" class="textbox cxm-a-input cxm-a-input-num" />', 'reload_main');
    echo $field($locale['cxmrsa_api_base'], '<input type="text" id="api_base" name="api_base" value="'.htmlspecialchars($sf['api_base']).'" class="textbox cxm-a-input" />', 'api_base');
    echo $field($locale['cxmrsa_wb_time'], '<input type="text" id="wb_time" name="wb_time" value="'.htmlspecialchars($sf['wb_time']).'" class="textbox cxm-a-input cxm-a-input-auto" />', 'wb_time');
    echo '</section>';
    echo '<div class="cxm-a-savebar"><button type="submit" name="save_settings" value="1" class="cxm-a-btn cxm-a-btn-primary">'.$locale['cxmrsa_save'].'</button></div>';
    echo '</form>';
}

echo '</div>';

require_once THEMES.'templates/footer.php';
?>
