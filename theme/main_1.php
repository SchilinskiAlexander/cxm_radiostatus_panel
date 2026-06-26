<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: main_1.php
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
<div class="cxm-rs-main">

    <?php if (!empty($cache['show_image'])): ?>
    <div class="cxm-rs-image">
        <img src="###station_img###" alt="###onair_name###" width="120" height="120"
             onerror="this.onerror=null;this.src='<?php echo CXM_RADIOSTATUS; ?>images/autodj.gif';" />
    </div>
    <?php endif; ?>

    <div class="cxm-rs-content">
        <h3>###rs_name### ###live_badge###</h3>

        <div class="cxm-rs-now">
            <strong><?php echo $locale['cxmrs_now']; ?></strong> ###song###
            <?php if (!empty($cache['album'])): ?>
            <span class="cxm-rs-album">(###album###)</span>
            <?php endif; ?>
        </div>

        <?php if (!empty($cache['artist_thumb'])): ?>
        <div class="cxm-rs-artist">
            <img src="###artist_thumb###" alt="###artist###" width="80" height="80" />
            <span>###artist###</span>
        </div>
        <?php endif; ?>

        <div class="cxm-rs-progress-wrap">
            <div class="cxm-rs-progress-bar" id="cxm_rs_prog_###rs_id###"
                 data-ends="###ends_at###"></div>
        </div>

        <div class="cxm-rs-stats">
            ###listner### <?php echo $locale['cxmrs_listener']; ?> &bull; ###playlist### &bull; ###genre###
        </div>

        ###next_artists_block###

        <audio controls preload="none" class="cxm-rs-audio">
            <source src="###stream_url###" type="audio/mpeg" />
        </audio>

        ###history_block###

        ###wb_button###
    </div>
</div>
