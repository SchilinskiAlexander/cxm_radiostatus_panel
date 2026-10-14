<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: layout_studio.php
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
$root = 'cxm-x cxm-layout-studio cxm-skin-'.$v['skin'].' cxm-pos-'.$v['pos'].($v['live'] ? ' is-live' : '');
?>
<section class="<?php echo $root; ?>" data-cxm-id="<?php echo $v['id']; ?>" data-song="<?php echo $v['song']; ?>">
    <div class="cxm-x-booth">
        <div class="cxm-x-sign"><span class="cxm-x-sign-light" aria-hidden="true"></span><?php echo $v['live'] ? $v['t']['live'] : $v['t']['onair']; ?></div>
        <?php if ($v['show_image']): ?>
        <div class="cxm-x-host"><img src="<?php echo $v['station_img']; ?>" alt="" loading="lazy" onerror="this.onerror=null;this.src='<?php echo $v['default_img']; ?>';" /></div>
        <?php endif; ?>
        <div class="cxm-x-host-name"><?php echo $v['onair']; ?></div>
        <div class="cxm-x-station"><?php echo $v['name']; ?></div>
    </div>
    <div class="cxm-x-body">
        <div class="cxm-x-plate">
            <span class="cxm-x-label"><?php echo $v['t']['now']; ?></span>
            <?php echo cxm_rs_part_titles($v, true); ?>
        </div>
        <?php echo cxm_rs_part_progress($v); ?>
        <div class="cxm-x-controls">
            <?php echo cxm_rs_part_play($v); ?>
            <?php echo cxm_rs_part_listeners($v); ?>
            <?php echo cxm_rs_part_links($v); ?>
        </div>
        <?php echo $v['wb_button']; ?>
    </div>
</section>
