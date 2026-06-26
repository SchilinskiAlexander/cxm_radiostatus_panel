<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2008 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Title: CxM Radiostatus Panel for PHP-Fusion
| Filename: index.php
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

// General
$locale['cxmrs_title']    = 'Radio Status';
$locale['cxmrs_desc']     = 'Radio Status Panel';

// Panel
$locale['cxmrs_offline']  = 'Offline';
$locale['cxmrs_listener'] = 'Listeners';
$locale['cxmrs_live']     = 'LIVE';
$locale['cxmrs_now']      = 'Now playing:';
$locale['cxmrs_history']  = 'Recent tracks:';
$locale['cxmrs_next']     = 'Coming up:';
$locale['cxmrs_no_stream']= 'No stream configured.';
$locale['cxmrs_no_js']    = 'Please enable JavaScript.';
$locale['cxmrs_onair']    = 'On Air';
$locale['cxmrs_player_popup'] = 'HTML5 player popup';
$locale['cxmrs_player_m3u']   = 'Download M3U';
$locale['cxmrs_player_pls']   = 'Download PLS';

// Wishbox
$locale['cxmrswb_title']      = 'Wishbox';
$locale['cxmrswb_name']       = 'Your name';
$locale['cxmrswb_wunsch']     = 'Your requested song / artist';
$locale['cxmrswb_nachricht']  = 'Your message / greetings';
$locale['cxmrswb_send']       = 'Send request';
$locale['cxmrswb_ok']         = 'Your request has been submitted!';
$locale['cxmrswb_err_fields'] = 'Please enter your name and either a song request or a message.';
$locale['cxmrswb_err_limit']  = 'You have reached the maximum number of requests.';
$locale['cxmrswb_err_cap']    = 'Invalid security code.';
$locale['cxmrswb_err_stream'] = 'Stream not found or wishbox disabled.';
$locale['cxmrswb_err_closed'] = 'The wishbox is currently closed.';
$locale['cxmrswb_open']       = 'Send request';
$locale['cxmrswb_close']      = 'Close';
$locale['cxmrswb_captcha']    = 'Security code';
$locale['cxmrswb_wait']       = 'Please wait...';
$locale['cxmrswb_admin_page'] = 'Wishbox Management';

// Admin
$locale['cxmrsa_streams']     = 'Streams';
$locale['cxmrsa_wunschbox']   = 'Wishbox';
$locale['cxmrsa_settings']    = 'Settings';
$locale['cxmrsa_add']         = 'Add stream';
$locale['cxmrsa_edit']        = 'Edit stream';
$locale['cxmrsa_name']        = 'Display name';
$locale['cxmrsa_station']     = 'Station slug';
$locale['cxmrsa_preview']     = 'Preview';
$locale['cxmrsa_actions']     = 'Actions';
$locale['cxmrsa_embed_code']  = 'iframe code';
$locale['cxmrsa_embed_hint']  = 'For external websites. Maximum size: 280x280 pixels.';
$locale['cxmrsa_close']       = 'Close';
$locale['cxmrsa_panel']       = 'Panel position';
$locale['cxmrsa_panel_1']     = 'Left';
$locale['cxmrsa_panel_2']     = 'Upper center';
$locale['cxmrsa_panel_3']     = 'Lower center';
$locale['cxmrsa_panel_4']     = 'Right';
$locale['cxmrsa_show_image']  = 'Show station image';
$locale['cxmrsa_show_next']   = 'Show upcoming artists';
$locale['cxmrsa_stream_url']  = 'Override stream URL (optional)';
$locale['cxmrsa_status']      = 'Status';
$locale['cxmrsa_active']      = 'Active';
$locale['cxmrsa_inactive']    = 'Inactive';
$locale['cxmrsa_wb_active']   = 'Wishbox active';
$locale['cxmrsa_wb_max']      = 'Max. requests total';
$locale['cxmrsa_wb_max_user'] = 'Max. requests per user/IP';
$locale['cxmrsa_wb_popup']    = 'Open wishbox in popup';
$locale['cxmrsa_access']      = 'Display access level';
$locale['cxmrsa_access_wb']   = 'Wishbox access level';
$locale['cxmrsa_reload_cache']= 'Cache age (seconds)';
$locale['cxmrsa_reload_side'] = 'Side panel reload (ms)';
$locale['cxmrsa_reload_main'] = 'Main panel reload (ms)';
$locale['cxmrsa_api_base']    = 'API base URL';
$locale['cxmrsa_wb_time']     = 'Wishbox date format';
$locale['cxmrsa_save']        = 'Save';
$locale['cxmrsa_delete']      = 'Delete';
$locale['cxmrsa_confirm_del'] = 'Really delete?';
$locale['cxmrsa_new_entries'] = 'new entries';
$locale['cxmrsa_order_up']    = 'Up';
$locale['cxmrsa_order_down']  = 'Down';
$locale['cxmrsa_enable']      = 'Enable';
$locale['cxmrsa_disable']     = 'Disable';
$locale['cxmrsa_no_streams']  = 'No streams have been added yet.';
$locale['cxmrsa_select_stream'] = 'Select stream';
$locale['cxmrsa_wb_entries']  = 'Wishbox entries';
$locale['cxmrsa_wb_time_col'] = 'Time';
$locale['cxmrsa_wb_name_col'] = 'Name';
$locale['cxmrsa_wb_wish_col'] = 'Request / message';
$locale['cxmrsa_wb_status_col'] = 'Status';
$locale['cxmrsa_wb_action_col'] = 'Actions';
$locale['cxmrsa_wb_read']     = 'Mark as read';
$locale['cxmrsa_wb_unread']   = 'Mark as unread';
$locale['cxmrsa_wb_del']      = 'Delete';
$locale['cxmrsa_wb_del_all']  = 'Delete all';
$locale['cxmrsa_wb_read_all'] = 'Mark all as read';
$locale['cxmrsa_wb_empty']    = 'No entries available.';
$locale['cxmrsa_wb_page_open']= 'Open wishbox page';
$locale['cxmrsa_saved']       = 'Settings saved.';
$locale['cxmrsa_stream_saved']= 'Stream saved.';
$locale['cxmrsa_stream_del']  = 'Stream deleted.';
?>
