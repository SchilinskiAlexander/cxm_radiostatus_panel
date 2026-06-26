<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: infusion_db.php
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

if (!defined('DB_CXM_RADIOSTATUS')) {
    define('DB_CXM_RADIOSTATUS', DB_PREFIX.'cxm_radiostatus');
}

if (!defined('DB_CXM_RADIOSTATUS_WUNSCHBOX')) {
    define('DB_CXM_RADIOSTATUS_WUNSCHBOX', DB_PREFIX.'cxm_radiostatus_wunschbox');
}

if (!defined('CXM_RADIOSTATUS')) {
    define('CXM_RADIOSTATUS', INFUSIONS.'cxm_radiostatus_panel/');
}

$cxm_radiostatus_settings = array(
    'reload_cache' => '30',
    'reload_side'  => '15000',
    'reload_main'  => '30000',
    'api_base'     => 'https://api.laut.fm',
    'wb_time'      => '%d.%m. %H:%M',
);

if (defined('DB_SETTINGS_INF')) {
    $set_result = dbquery("SELECT settings_name, settings_value FROM ".DB_SETTINGS_INF." WHERE settings_inf='cxm_radiostatus_panel'");
    if (dbrows($set_result)) {
        while ($set_data = dbarray($set_result)) {
            $cxm_radiostatus_settings[$set_data['settings_name']] = $set_data['settings_value'];
        }
    }
}
?>
