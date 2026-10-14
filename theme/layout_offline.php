<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: layout_offline.php
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
global $locale;
?>
<section class="cxm-x cxm-layout-offline cxm-skin-<?php echo $v['skin']; ?> cxm-pos-<?php echo $v['pos']; ?>" data-cxm-id="<?php echo $v['id']; ?>">
    <div class="cxm-x-offline">
        <span class="cxm-x-onair is-off"><span class="cxm-x-onair-light" aria-hidden="true"></span><?php echo htmlspecialchars($locale['cxmrs_offline']); ?></span>
        <div class="cxm-x-station"><?php echo $v['name']; ?></div>
        <p><?php echo htmlspecialchars($locale['cxmrs_offline_hint']); ?></p>
        <?php if ((defined('iSUPERADMIN') && iSUPERADMIN) && !empty($cache['error'])): ?>
        <p class="cxm-x-error"><?php echo htmlspecialchars($cache['error']); ?></p>
        <?php endif; ?>
    </div>
</section>
