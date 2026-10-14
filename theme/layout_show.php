<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: layout_show.php
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
$root = 'cxm-x cxm-layout-show cxm-skin-'.$v['skin'].' cxm-pos-'.$v['pos'].($v['live'] ? ' is-live' : '');
?>
<section class="<?php echo $root; ?>" data-cxm-id="<?php echo $v['id']; ?>" data-song="<?php echo $v['song']; ?>">
    <div class="cxm-x-show">
        <?php if ($v['show_image']): ?>
        <div class="cxm-x-avatar"><img src="<?php echo $v['station_img']; ?>" alt="" loading="lazy" onerror="this.onerror=null;this.src='<?php echo $v['default_img']; ?>';" /></div>
        <?php endif; ?>
        <div class="cxm-x-show-text">
            <span class="cxm-x-sign"><span class="cxm-x-sign-light" aria-hidden="true"></span><?php echo $v['live'] ? $v['t']['live'] : $v['t']['onair']; ?></span>
            <div class="cxm-x-host-name"><?php echo $v['onair']; ?></div>
            <div class="cxm-x-station"><?php echo $v['name']; ?></div>
        </div>
    </div>
    <div class="cxm-x-body">
        <div class="cxm-x-now">
            <?php if ($v['show_image']) { echo cxm_rs_part_cover($v); } ?>
            <div class="cxm-x-plate">
                <span class="cxm-x-label"><?php echo $v['t']['now']; ?></span>
                <?php echo cxm_rs_part_titles($v, true); ?>
            </div>
            <?php echo cxm_rs_part_play($v); ?>
        </div>
        <?php echo cxm_rs_part_progress($v); ?>
        <?php echo $v['wb_button']; ?>
    </div>
</section>
