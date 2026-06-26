<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: infusion.php
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

include_once INFUSIONS.'cxm_radiostatus_panel/infusion_db.php';
if (file_exists(INFUSIONS.'cxm_radiostatus_panel/locale/'.LOCALESET.'index.php')) {
    include INFUSIONS.'cxm_radiostatus_panel/locale/'.LOCALESET.'index.php';
} else {
    include INFUSIONS.'cxm_radiostatus_panel/locale/German/index.php';
}

$inf_title       = $locale['cxmrs_title'];
$inf_description = $locale['cxmrs_desc'];
$inf_version     = '1.0';
$inf_developer   = 'Alexaxander Schilinski';
$inf_email       = '';
$inf_weburl      = 'https://schilinskialexander.com';
$inf_folder      = 'cxm_radiostatus_panel';

// Tabelle cxm_radiostatus
$inf_newtable[1] = DB_CXM_RADIOSTATUS." (
rs_id          SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
rs_name        VARCHAR(50) NOT NULL DEFAULT 'Stream',
rs_station     VARCHAR(100) NOT NULL DEFAULT '',
rs_panel       TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
rs_order       SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
rs_status      TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
rs_cache       VARCHAR(32) NOT NULL DEFAULT '',
rs_theme       TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
rs_access      TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
rs_show_image  TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
rs_show_next   TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
rs_stream_url  VARCHAR(255) NOT NULL DEFAULT '',
rs_status_wb   TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
rs_wb_max      SMALLINT(5) UNSIGNED NOT NULL DEFAULT '100',
rs_wb_max_user SMALLINT(5) UNSIGNED NOT NULL DEFAULT '3',
rs_wb_popup    TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
rs_access_wb   TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (rs_id),
INDEX rs_panel (rs_panel, rs_access, rs_status)
)";

// Tabelle cxm_radiostatus_wunschbox
$inf_newtable[2] = DB_CXM_RADIOSTATUS_WUNSCHBOX." (
wb_id        BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
wb_stream    SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
wb_user_id   MEDIUMINT(8) UNSIGNED NOT NULL DEFAULT '0',
wb_user_ip   VARCHAR(45) NOT NULL DEFAULT '',
wb_user_name VARCHAR(100) NOT NULL DEFAULT '',
wb_wunsch    VARCHAR(255) NOT NULL DEFAULT '',
wb_nachricht TEXT NOT NULL,
wb_time      INT(10) UNSIGNED NOT NULL DEFAULT '0',
wb_status    TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY (wb_id),
INDEX wb_stream (wb_stream, wb_status)
)";

$inf_insertdbrow[1] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('reload_cache', '30', '".$inf_folder."')";
$inf_insertdbrow[2] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('reload_side', '15000', '".$inf_folder."')";
$inf_insertdbrow[3] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('reload_main', '30000', '".$inf_folder."')";
$inf_insertdbrow[4] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('api_base', 'https://api.laut.fm', '".$inf_folder."')";
$inf_insertdbrow[5] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES('wb_time', '%d.%m. %H:%M', '".$inf_folder."')";
$inf_insertdbrow[6] = DB_PANELS." (panel_name, panel_filename, panel_content, panel_side, panel_order, panel_type, panel_access, panel_display, panel_status) VALUES('".$locale['cxmrs_title']."', '".$inf_folder."', '', '4', '1', 'file', '0', '0', '1')";

$inf_droptable[1] = DB_CXM_RADIOSTATUS;
$inf_droptable[2] = DB_CXM_RADIOSTATUS_WUNSCHBOX;

$inf_deldbrow[1] = DB_PANELS." WHERE panel_filename='".$inf_folder."'";
$inf_deldbrow[2] = DB_SETTINGS_INF." WHERE settings_inf='".$inf_folder."'";

// Admin-Panel registrieren
$inf_adminpanel[1] = array(
    'title'  => $locale['cxmrs_title'],
    'image'  => '',
    'panel'  => 'cxm_radiostatus_admin.php',
    'rights' => 'CXRS'
);
?>
