<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: layout_compact.php
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
$root = 'cxm-x cxm-layout-compact cxm-skin-'.$v['skin'].' cxm-pos-'.$v['pos'].($v['live'] ? ' is-live' : '');
?>
<section class="<?php echo $root; ?>" data-cxm-id="<?php echo $v['id']; ?>" data-song="<?php echo $v['song']; ?>">
    <div class="cxm-x-row">
        <?php if ($v['show_image']) { echo cxm_rs_part_cover($v); } ?>
        <div class="cxm-x-plate">
            <div class="cxm-x-meta"><?php echo cxm_rs_part_onair($v); ?></div>
            <?php echo cxm_rs_part_titles($v, true); ?>
        </div>
        <?php echo cxm_rs_part_play($v); ?>
    </div>
    <?php echo cxm_rs_part_progress($v); ?>
    <?php if ($v['wb_button'] !== ''): ?>
    <div class="cxm-x-foot"><?php echo $v['wb_button']; ?></div>
    <?php endif; ?>
</section>
