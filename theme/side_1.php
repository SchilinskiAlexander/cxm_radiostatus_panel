<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: side_1.php
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
?>
<div class="cxm-rs-side">
    <div class="cxm-rs-side-image">
        <img src="###station_img###" alt="###onair_name###" width="96" height="96"
             onerror="this.onerror=null;this.src='<?php echo CXM_RADIOSTATUS; ?>images/autodj.gif';" />
    </div>
    <div class="cxm-rs-header">
        <strong>###rs_name###</strong>
        ###live_badge###
    </div>
    <div class="cxm-rs-body">
        <div class="cxm-rs-song">###song###</div>
        <div class="cxm-rs-meta">###listner### <?php echo $locale['cxmrs_listener']; ?> &bull; ###genre###</div>
        <a href="###stream_url###" target="_blank" class="cxm-rs-play button">&#9654; <?php echo $locale['cxmrs_now']; ?></a>
    </div>
    ###wb_button###
</div>
