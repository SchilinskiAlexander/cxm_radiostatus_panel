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

// Pfad der Infusion ab Domain-Root (z.B. /infusions/cxm_radiostatus_panel/), ohne Host.
// Für AJAX/Popups, damit www/ohne www bzw. http/https keine CORS-Fehler auslösen.
if (!function_exists('cxm_rs_web_path')) {
    function cxm_rs_web_path() {
        global $settings;

        $base = !empty($settings['siteurl']) ? parse_url($settings['siteurl'], PHP_URL_PATH) : '/';
        $base = '/'.trim((string)$base, '/');
        return rtrim($base, '/').'/'.ltrim(str_replace('../', '', CXM_RADIOSTATUS), '/');
    }
}

// Bestehende Installationen (vor 1.2) um die Spalte rs_source ergänzen
if (!function_exists('cxm_rs_ensure_schema')) {
    function cxm_rs_ensure_schema() {
        $result = dbquery("SHOW COLUMNS FROM ".DB_CXM_RADIOSTATUS." LIKE 'rs_source'");
        if ($result && !dbrows($result)) {
            dbquery("ALTER TABLE ".DB_CXM_RADIOSTATUS." ADD rs_source VARCHAR(10) NOT NULL DEFAULT 'lautfm' AFTER rs_name");
        }
        // Stil der Designs (bestehende Streams behalten ihr klassisches Layout)
        $result = dbquery("SHOW COLUMNS FROM ".DB_CXM_RADIOSTATUS." LIKE 'rs_skin'");
        if ($result && !dbrows($result)) {
            dbquery("ALTER TABLE ".DB_CXM_RADIOSTATUS." ADD rs_skin VARCHAR(20) NOT NULL DEFAULT 'modern' AFTER rs_theme");
        }
        // Eigener Stil für Popup-Player, Embed und Wunschbox-Popup ('' = wie im Panel)
        $result = dbquery("SHOW COLUMNS FROM ".DB_CXM_RADIOSTATUS." LIKE 'rs_player_skin'");
        if ($result && !dbrows($result)) {
            dbquery("ALTER TABLE ".DB_CXM_RADIOSTATUS." ADD rs_player_skin VARCHAR(20) NOT NULL DEFAULT '' AFTER rs_skin");
        }
    }
}

if (defined('DB_SETTINGS_INF')) {
    $set_result = dbquery("SELECT settings_name, settings_value FROM ".DB_SETTINGS_INF." WHERE settings_inf='cxm_radiostatus_panel'");
    if (dbrows($set_result)) {
        while ($set_data = dbarray($set_result)) {
            $cxm_radiostatus_settings[$set_data['settings_name']] = $set_data['settings_value'];
        }
    }
}
?>
