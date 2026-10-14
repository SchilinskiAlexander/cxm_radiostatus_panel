<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: cxm_radiostatus_info.php
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

if (!iADMIN || !checkrights('CXRS')) {
    redirect(BASEDIR.'index.php');
}

if (!defined('CXM_RADIOSTATUS')) {
    define('CXM_RADIOSTATUS', INFUSIONS.'cxm_radiostatus_panel/');
}

require_once CXM_RADIOSTATUS.'infusion_db.php';
if (file_exists(CXM_RADIOSTATUS.'locale/'.LOCALESET.'index.php')) {
    require CXM_RADIOSTATUS.'locale/'.LOCALESET.'index.php';
} else {
    require CXM_RADIOSTATUS.'locale/German/index.php';
}

require_once INCLUDES.'output_handling_include.php';
add_to_head('<link rel="stylesheet" type="text/css" href="'.CXM_RADIOSTATUS.'css/cxm_radiostatus.css" />');

$streams = array();
$stream_ids = array();
$result = dbquery("SELECT rs_id, rs_name FROM ".DB_CXM_RADIOSTATUS." ORDER BY rs_panel, rs_order");
while ($row = dbarray($result)) {
    $streams[] = $row;
    $stream_ids[] = (int)$row['rs_id'];
}

$stream_id = isset($_GET['stream']) && isnum($_GET['stream']) ? intval($_GET['stream']) : 0;
if ((!$stream_id || !in_array($stream_id, $stream_ids, true)) && !empty($streams)) {
    $stream_id = (int)$streams[0]['rs_id'];
}

// Inhalt puffern, damit CSS und Skripte vor dem Theme-Kopf registriert sind
ob_start();
echo '<div class="cxm-a">';
echo '<header class="cxm-a-top"><div class="cxm-a-title"><h1>'.$locale['cxmrswb_admin_page'].'</h1><p>'.$locale['cxmrsa_wb_lead'].'</p></div>'
    .'<a href="'.CXM_RADIOSTATUS.'cxm_radiostatus_admin.php'.$aidlink.'" class="cxm-a-btn cxm-a-btn-quiet">'.$locale['cxmrsa_back_admin'].'</a></header>';

if (empty($streams)) {
    echo '<section class="cxm-a-card"><div class="cxm-a-empty"><p>'.$locale['cxmrsa_no_streams'].'</p></div></section>';
} else {
    if (count($streams) > 1) {
        echo '<nav class="cxm-a-nav"><ul class="cxm-a-tabs">';
        foreach ($streams as $stream) {
            $active = $stream_id === (int)$stream['rs_id'];
            echo '<li><a href="'.FUSION_SELF.'?stream='.(int)$stream['rs_id'].'"'.($active ? ' class="is-active" aria-current="page"' : '').'>'
                .htmlspecialchars($stream['rs_name']).'</a></li>';
        }
        echo '</ul></nav>';
    }

    echo '<section class="cxm-a-card cxm-a-card-flush" id="cxm-rs-wb-admin" aria-live="polite"><div class="cxm-a-empty"><p>'.$locale['cxmrswb_wait'].'</p></div></section>';

    add_to_head('<script type="text/javascript">
    var cxmRsWbStream = '.$stream_id.';
    var cxmRsWbAid = "'.(defined('iAUTH') ? iAUTH : '').'";
    function cxmRsWbLoad() {
        $("#cxm-rs-wb-admin").load("'.CXM_RADIOSTATUS.'cxm_radiostatus_inc.php?wbadmin='.$stream_id.'&aid=" + cxmRsWbAid);
    }
    function cxmRsWbDelete(id) {
        $.post("'.CXM_RADIOSTATUS.'cxm_radiostatus_inc.php", { wbadmin: cxmRsWbStream, aid: cxmRsWbAid, id: id }).done(cxmRsWbLoad);
    }
    function cxmRsWbStatus(id, status) {
        $.post("'.CXM_RADIOSTATUS.'cxm_radiostatus_inc.php", { wbadmin: cxmRsWbStream, aid: cxmRsWbAid, uid: id, status: status }).done(cxmRsWbLoad);
    }
    function cxmRsWbBulk(action) {
        $.post("'.CXM_RADIOSTATUS.'cxm_radiostatus_inc.php", { wbadmin: cxmRsWbStream, aid: cxmRsWbAid, action: action }).done(cxmRsWbLoad);
    }
    $(document).ready(function() {
        cxmRsWbLoad();
        setInterval(cxmRsWbLoad, 60000);
    });
    </script>');
}

echo '</div>';
$cxm_rs_page = ob_get_clean();
require_once THEMES.'templates/header.php';
echo $cxm_rs_page;

require_once THEMES.'templates/footer.php';
?>
