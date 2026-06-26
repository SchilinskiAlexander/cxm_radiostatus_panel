<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: main_3.php
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
<div class="cxm-rs-theme3">
    <div class="cxm-rs-theme3-head">
        <span>###rs_name###</span>
    </div>

    <div class="cxm-rs-theme3-grid">
        <div class="cxm-rs-theme3-image">
            <img src="###station_img###" alt="###onair_name###"
                 onerror="this.onerror=null;this.src='<?php echo CXM_RADIOSTATUS; ?>images/autodj.gif';" />
        </div>

        <div class="cxm-rs-theme3-topbar">
            <div class="cxm-rs-theme3-status">
                <span class="cxm-rs-theme3-status-label">OnAir:</span>
                <span class="cxm-rs-theme3-status-value">###onair_name###</span>
            </div>

            <div class="cxm-rs-theme3-player">
                <div>
                    ###player###
                </div>
                <div>###wb_button###</div>
            </div>
        </div>

        <div class="cxm-rs-theme3-now">
            <strong><?php echo $locale['cxmrs_now']; ?></strong>
            <div class="cxm-rs-theme3-song cxm-rs-theme3-marquee">
                <span class="cxm-rs-theme3-marquee-inner">###song###</span>
            </div>
        </div>
    </div>
</div>
