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

require_once THEMES.'templates/header.php';
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

opentable($locale['cxmrswb_admin_page']);

if (empty($streams)) {
    echo '<div class="tbl2">'.$locale['cxmrsa_no_streams'].'</div>';
} else {
    echo '<form method="get" action="'.FUSION_SELF.'" class="cxm-rs-stream-select-form">';
    echo '<select name="stream" class="textbox" onchange="this.form.submit()">';
    foreach ($streams as $stream) {
        echo '<option value="'.$stream['rs_id'].'"'.($stream_id === (int)$stream['rs_id'] ? ' selected="selected"' : '').'>'
            .htmlspecialchars($stream['rs_name']).'</option>';
    }
    echo '</select>';
    echo '</form>';

    echo '<div id="cxm-rs-wb-admin"><div class="tbl2">'.$locale['cxmrswb_wait'].'</div></div>';

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

closetable();

require_once THEMES.'templates/footer.php';
?>
