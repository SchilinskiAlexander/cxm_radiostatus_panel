<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: cxm_radiostatus_panel.php
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

// Das Panel kann mehrfach pro Seite eingebunden werden (z.B. links und rechts)
if (!function_exists('cxm_rs_panel_where')) {
    function cxm_rs_panel_where($panel) {
        return "rs_panel='".(int)$panel."'";
    }
}

// Panel-Position bestimmen
if (isset($current_side)) {
    $panel = $current_side;
} elseif (isset($p_data) && array_key_exists('panel_side', $p_data)) {
    $panel = $p_data['panel_side'];
} else {
    $panel = 5;
}

if ($panel == 1 || $panel == 4) {
    openside($locale['cxmrs_title']);
} elseif ($panel != 5) {
    opentable($locale['cxmrs_title']);
}

// Streams für dieses Panel laden
$result = dbquery("SELECT rs_id, rs_cache, rs_panel, rs_status_wb, rs_access_wb, rs_wb_popup FROM "
    .DB_CXM_RADIOSTATUS." WHERE ".groupaccess("rs_access")
    ." AND ".cxm_rs_panel_where($panel)." AND rs_status='1' ORDER BY rs_order");

if (dbrows($result)) {
    add_to_head('<link rel="stylesheet" type="text/css" href="'.CXM_RADIOSTATUS.'css/cxm_radiostatus.css" />');

    // Container für AJAX-Inhalte
    echo '<div id="cxm_radiostatus_panel_'.(int)$panel.'"></div>';

    // Wunschbox-Formulare (statisch im DOM)
    while ($data = dbarray($result)) {
        $rs_id = (int)$data['rs_id'];
        if ($data['rs_status_wb'] && checkgroup($data['rs_access_wb']) && empty($data['rs_wb_popup'])) {
            echo '<div id="cxm_rs_wb_'.$rs_id.'" class="cxm-rs-hidden">';
            echo '<form id="cxm_wb_form_'.$rs_id.'" onsubmit="return check_wb_'.$rs_id.'();">';
            echo '<table cellspacing="0" cellpadding="2" width="100%" class="tbl-border">';
            echo '<tr><td class="tbl2" colspan="2"><strong>'.$locale['cxmrswb_title'].'</strong></td></tr>';

            // Name
            echo '<tr><td class="tbl2">'.$locale['cxmrswb_name'].' <span class="cxm-rs-required">*</span></td>'
                .'<td class="tbl2"><input type="text" id="cxm_wb_name_'.$rs_id.'" name="name"'
                .(iMEMBER ? ' value="'.htmlspecialchars($userdata['user_name']).'"' : '')
                .' class="textbox cxm-rs-form-wide" /></td></tr>';

            // Wunsch
            echo '<tr><td class="tbl2">'.$locale['cxmrswb_wunsch'].'</td>'
                .'<td class="tbl2"><input type="text" id="cxm_wb_wunsch_'.$rs_id.'" name="wunsch"'
                .' class="textbox cxm-rs-form-wide" /></td></tr>';

            // Nachricht
            echo '<tr><td class="tbl2">'.$locale['cxmrswb_nachricht'].'</td>'
                .'<td class="tbl2"><textarea id="cxm_wb_nachricht_'.$rs_id.'" name="nachricht"'
                .' class="textbox cxm-rs-form-wide" rows="3"></textarea></td></tr>';

            // CAPTCHA für Gäste
            if (!iMEMBER) {
                echo '<tr><td class="tbl2">'.$locale['cxmrswb_captcha'].' <span class="cxm-rs-required">*</span></td>'
                    .'<td class="tbl2">'.cxm_rs_captcha_html('cxm_wb_code_'.$rs_id).'</td></tr>';
            }

            // Absenden
            echo '<tr><td class="tbl2" colspan="2" align="right">'
                .'<input type="submit" name="wb_save" value="'.$locale['cxmrswb_send'].'" class="button" />'
                .'</td></tr>';
            echo '</table>';
            echo '</form>';
            echo '<div id="cxm_rs_wb_check_'.$rs_id.'" class="cxm-rs-hidden"></div>';
            echo '</div>';
        }
    }

    add_to_head('<script type="text/javascript" src="'.CXM_RADIOSTATUS.'cxm_radiostatus_inc.php?js='.(int)$panel.'"></script>');
} else {
    echo '<div>'.$locale['cxmrs_no_stream'].'</div>';
}

echo '<noscript><div align="center">'.$locale['cxmrs_no_js'].'</div></noscript>';

if ($panel == 1 || $panel == 4) {
    closeside();
} elseif ($panel != 5) {
    closetable();
}
?>
