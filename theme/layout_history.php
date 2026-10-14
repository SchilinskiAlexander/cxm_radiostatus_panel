<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: layout_history.php
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
$root = 'cxm-x cxm-layout-history cxm-skin-'.$v['skin'].' cxm-pos-'.$v['pos'].($v['live'] ? ' is-live' : '');
?>
<section class="<?php echo $root; ?>" data-cxm-id="<?php echo $v['id']; ?>" data-song="<?php echo $v['song']; ?>">
    <div class="cxm-x-head">
        <div class="cxm-x-station"><?php echo $v['name']; ?></div>
        <?php echo cxm_rs_part_onair($v); ?>
    </div>
    <div class="cxm-x-now">
        <?php if ($v['show_image']) { echo cxm_rs_part_cover($v); } ?>
        <div class="cxm-x-plate">
            <span class="cxm-x-label"><?php echo $v['t']['now']; ?></span>
            <?php echo cxm_rs_part_titles($v); ?>
        </div>
        <?php echo cxm_rs_part_play($v); ?>
    </div>
    <?php echo cxm_rs_part_progress($v); ?>
    <?php if (!empty($v['history'])): ?>
    <div class="cxm-x-list">
        <div class="cxm-x-label"><?php echo $v['t']['history']; ?></div>
        <ol>
            <?php foreach ($v['history'] as $h): ?>
            <li>
                <span class="cxm-x-list-text"><b><?php echo $h['title']; ?></b><?php if ($h['artist'] !== ''): ?> <span><?php echo $h['artist']; ?></span><?php endif; ?></span>
                <?php if ($h['time'] !== ''): ?><time><?php echo $h['time']; ?></time><?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
    <?php endif; ?>
    <?php if (!empty($v['next'])): ?>
    <div class="cxm-x-next">
        <span class="cxm-x-label"><?php echo $v['t']['next']; ?></span>
        <?php foreach ($v['next'] as $n): ?><span class="cxm-x-chip"><?php echo $n; ?></span><?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="cxm-x-controls">
        <?php echo cxm_rs_part_listeners($v); ?>
        <?php echo cxm_rs_part_links($v); ?>
    </div>
    <?php echo $v['wb_button']; ?>
</section>
