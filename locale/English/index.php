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
$locale['cxmrs_player_play']  = 'Play';
$locale['cxmrs_player_pause'] = 'Pause';
$locale['cxmrs_player_mute']  = 'Mute';
$locale['cxmrs_player_volume']= 'Volume';
$locale['cxmrs_player_error'] = 'The stream could not be loaded.';

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
$locale['cxmrsa_source']      = 'Source';
$locale['cxmrsa_source_lautfm'] = 'laut.fm';
$locale['cxmrsa_source_lexycast'] = 'LexyCast';
$locale['cxmrsa_station_lexycast'] = 'LexyCast address with port';
$locale['cxmrsa_station_lexycast_hint'] = 'e.g. https://c02.lexycast.de:4020 - station, channel and stream URL are detected automatically. For a station other than the default, append #number (e.g. ...:4020#2).';
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
$locale['cxmrsa_api_base']    = 'laut.fm API base URL';
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

// Designs
$locale['cxmrs_offline_hint']          = 'Nothing on air right now. Check back later.';
$locale['cxmrsd_design']               = 'Design';
$locale['cxmrsd_layout']               = 'Layout';
$locale['cxmrsd_skin']                 = 'Style';
$locale['cxmrsd_preview']              = 'Preview with sample data';
$locale['cxmrsd_preview_side']         = 'Side panel';
$locale['cxmrsd_preview_main']         = 'Main panel';
$locale['cxmrsd_skin_classic_note']    = 'Classic layouts do not use a style.';
$locale['cxmrsd_layout_cover']         = 'Large cover';
$locale['cxmrsd_layout_cover_desc']    = 'Large cover, title, progress and play.';
$locale['cxmrsd_layout_compact']       = 'Compact';
$locale['cxmrsd_layout_compact_desc']  = 'One slim row with scrolling title. For busy sidebars.';
$locale['cxmrsd_layout_studio']        = 'Studio';
$locale['cxmrsd_layout_studio_desc']   = 'Host and on-air sign up front, request box prominent.';
$locale['cxmrsd_layout_host']          = 'Host';
$locale['cxmrsd_layout_host_desc']     = 'Large host picture with name, current track below.';
$locale['cxmrsd_layout_show']          = 'Show';
$locale['cxmrsd_layout_show_desc']     = 'On-air bar with host, track and cover below.';
$locale['cxmrsd_layout_duo']           = 'Host & cover';
$locale['cxmrsd_layout_duo_desc']      = 'Host picture and cover side by side.';
$locale['cxmrsd_layout_history']       = 'With history';
$locale['cxmrsd_layout_history_desc']  = 'Current track plus recently played tracks.';
$locale['cxmrsd_layout_classic3']      = 'Classic';
$locale['cxmrsd_layout_classic3_desc'] = 'The previous banner design (side panels use the old compact view).';
$locale['cxmrsd_skin_modern']          = 'Modern';
$locale['cxmrsd_skin_modern_desc']     = 'Calm and dark with a coral accent.';
$locale['cxmrsd_skin_hitparade']       = 'Hit parade';
$locale['cxmrsd_skin_hitparade_desc']  = 'Ultramarine with lit title plates and big numerals.';
$locale['cxmrsd_skin_jukebox']         = 'Jukebox';
$locale['cxmrsd_skin_jukebox_desc']    = 'Oxblood and chrome, titles on typed title strips.';
$locale['cxmrsd_skin_mixtape']         = 'Mixtape';
$locale['cxmrsd_skin_mixtape_desc']    = 'Cassette case with label and ballpoint titles.';
$locale['cxmrsa_lead']           = 'Streams, designs and request box in one place.';
$locale['cxmrsa_stat_streams']   = 'Streams';
$locale['cxmrsa_stat_active']    = 'Active';
$locale['cxmrsa_stat_wishes']    = 'New requests';
$locale['cxmrsa_sec_stream']     = 'Stream';
$locale['cxmrsa_sec_display']    = 'Display';
$locale['cxmrsa_options']        = 'Options';
$locale['cxmrsa_status_on']      = 'Stream is active (visible)';
$locale['cxmrsa_test']           = 'Check API';
$locale['cxmrsa_cancel']         = 'Cancel';
$locale['cxmrsa_zero_unlimited'] = '0 = unlimited';
$locale['cxmrsd_skin_vinyl']           = 'Vinyl';
$locale['cxmrsd_skin_vinyl_desc']      = 'Record black with a gold label; the cover spins while playing.';
$locale['cxmrsd_skin_autoradio']       = 'Car radio';
$locale['cxmrsd_skin_autoradio_desc']  = 'Amber display like the RDS field of a car radio.';
$locale['cxmrsd_skin_strand']          = 'Beach';
$locale['cxmrsd_skin_strand_desc']     = 'Petrol, sand and sunset, titles on a postcard.';
$locale['cxmrsd_skin_stuebchen']       = 'Parlour';
$locale['cxmrsd_skin_stuebchen_desc']  = 'Warm wood and a brass plate, cosy like a parlour.';
$locale['cxmrsd_player_skin']          = 'Player style';
$locale['cxmrsd_player_skin_same']     = 'Same as panel';
$locale['cxmrsd_player_skin_hint']     = 'Applies to the popup player, iframe embed and request box popup.';
$locale['cxmrsa_wb_lead']              = 'Song requests from live shows. The list refreshes every minute.';
$locale['cxmrsa_back_admin']           = 'Back to settings';
$locale['cxmrsa_wb_new']               = 'New';
?>
