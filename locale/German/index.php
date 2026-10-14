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

// Allgemein
$locale['cxmrs_title']    = 'Radio Status';
$locale['cxmrs_desc']     = 'Radio Status Panel';

// Panel
$locale['cxmrs_offline']  = 'Offline';
$locale['cxmrs_listener'] = 'Hörer';
$locale['cxmrs_live']     = 'LIVE';
$locale['cxmrs_now']      = 'Aktuell:';
$locale['cxmrs_history']  = 'Letzte Titel:';
$locale['cxmrs_next']     = 'Demnächst:';
$locale['cxmrs_no_stream']= 'Kein Stream konfiguriert.';
$locale['cxmrs_no_js']    = 'Bitte JavaScript aktivieren.';
$locale['cxmrs_onair']    = 'OnAir';
$locale['cxmrs_player_popup'] = 'HTML5-Player im Popup';
$locale['cxmrs_player_m3u']   = 'M3U herunterladen';
$locale['cxmrs_player_pls']   = 'PLS herunterladen';
$locale['cxmrs_player_play']  = 'Abspielen';
$locale['cxmrs_player_pause'] = 'Pause';
$locale['cxmrs_player_mute']  = 'Stumm schalten';
$locale['cxmrs_player_volume']= 'Lautstärke';
$locale['cxmrs_player_error'] = 'Stream konnte nicht geladen werden.';

// Wunschbox
$locale['cxmrswb_title']      = 'Wunschbox';
$locale['cxmrswb_name']       = 'Dein Name';
$locale['cxmrswb_wunsch']     = 'Dein Wunsch-Song / Künstler';
$locale['cxmrswb_nachricht']  = 'Deine Nachricht / Grüße';
$locale['cxmrswb_send']       = 'Wunsch absenden';
$locale['cxmrswb_ok']         = 'Dein Wunsch wurde übermittelt!';
$locale['cxmrswb_err_fields'] = 'Bitte Name und Wunsch oder Nachricht ausfüllen.';
$locale['cxmrswb_err_limit']  = 'Du hast das maximale Limit an Wünschen erreicht.';
$locale['cxmrswb_err_cap']    = 'Ungültiger Sicherheitscode.';
$locale['cxmrswb_err_stream'] = 'Stream nicht gefunden oder Wunschbox deaktiviert.';
$locale['cxmrswb_err_closed'] = 'Die Wunschbox ist derzeit geschlossen.';
$locale['cxmrswb_open']       = 'Wunsch schicken';
$locale['cxmrswb_close']      = 'Schließen';
$locale['cxmrswb_captcha']    = 'Sicherheitscode';
$locale['cxmrswb_wait']       = 'Bitte warten...';
$locale['cxmrswb_admin_page'] = 'Wunschbox-Verwaltung';

// Admin
$locale['cxmrsa_streams']     = 'Streams';
$locale['cxmrsa_wunschbox']   = 'Wunschbox';
$locale['cxmrsa_settings']    = 'Einstellungen';
$locale['cxmrsa_add']         = 'Stream hinzufügen';
$locale['cxmrsa_edit']        = 'Stream bearbeiten';
$locale['cxmrsa_name']        = 'Anzeigename';
$locale['cxmrsa_station']     = 'Stations-Slug';
$locale['cxmrsa_source']      = 'Quelle';
$locale['cxmrsa_source_lautfm'] = 'laut.fm';
$locale['cxmrsa_source_lexycast'] = 'LexyCast';
$locale['cxmrsa_station_lexycast'] = 'LexyCast-Adresse mit Port';
$locale['cxmrsa_station_lexycast_hint'] = 'z. B. https://c02.lexycast.de:4020 - Sender, Kanal und Stream-URL werden automatisch ermittelt. Anderer Sender als der Standard: #Nummer anhängen (z. B. ...:4020#2).';
$locale['cxmrsa_preview']     = 'Vorschau';
$locale['cxmrsa_actions']     = 'Aktionen';
$locale['cxmrsa_embed_code']  = 'iframe-Code';
$locale['cxmrsa_embed_hint']  = 'Für externe Webseiten. Maximale Größe: 280x280 Pixel.';
$locale['cxmrsa_close']       = 'Schließen';
$locale['cxmrsa_panel']       = 'Panel-Position';
$locale['cxmrsa_panel_1']     = 'Links';
$locale['cxmrsa_panel_2']     = 'Mitte oben';
$locale['cxmrsa_panel_3']     = 'Mitte unten';
$locale['cxmrsa_panel_4']     = 'Rechts';
$locale['cxmrsa_show_image']  = 'Stationsbild anzeigen';
$locale['cxmrsa_show_next']   = 'Nächste Künstler anzeigen';
$locale['cxmrsa_stream_url']  = 'Stream-URL überschreiben (optional)';
$locale['cxmrsa_status']      = 'Status';
$locale['cxmrsa_active']      = 'Aktiv';
$locale['cxmrsa_inactive']    = 'Inaktiv';
$locale['cxmrsa_wb_active']   = 'Wunschbox aktiv';
$locale['cxmrsa_wb_max']      = 'Max. Wünsche gesamt';
$locale['cxmrsa_wb_max_user'] = 'Max. Wünsche pro User/IP';
$locale['cxmrsa_wb_popup']    = 'Wunschbox im Popup öffnen';
$locale['cxmrsa_access']      = 'Zugangslevel Anzeige';
$locale['cxmrsa_access_wb']   = 'Zugangslevel Wunschbox';
$locale['cxmrsa_reload_cache']= 'Cache-Alter (Sekunden)';
$locale['cxmrsa_reload_side'] = 'Seitenpanel Reload (ms)';
$locale['cxmrsa_reload_main'] = 'Hauptpanel Reload (ms)';
$locale['cxmrsa_api_base']    = 'laut.fm API-Basis-URL';
$locale['cxmrsa_wb_time']     = 'Datumsformat Wunschbox';
$locale['cxmrsa_save']        = 'Speichern';
$locale['cxmrsa_delete']      = 'Löschen';
$locale['cxmrsa_confirm_del'] = 'Wirklich löschen?';
$locale['cxmrsa_new_entries'] = 'neue Einträge';
$locale['cxmrsa_order_up']    = 'Hoch';
$locale['cxmrsa_order_down']  = 'Runter';
$locale['cxmrsa_enable']      = 'Aktivieren';
$locale['cxmrsa_disable']     = 'Deaktivieren';
$locale['cxmrsa_no_streams']  = 'Noch keine Streams vorhanden.';
$locale['cxmrsa_select_stream'] = 'Stream auswählen';
$locale['cxmrsa_wb_entries']  = 'Wunschbox-Einträge';
$locale['cxmrsa_wb_time_col'] = 'Zeit';
$locale['cxmrsa_wb_name_col'] = 'Name';
$locale['cxmrsa_wb_wish_col'] = 'Wunsch / Nachricht';
$locale['cxmrsa_wb_status_col'] = 'Status';
$locale['cxmrsa_wb_action_col'] = 'Aktionen';
$locale['cxmrsa_wb_read']     = 'Als gelesen';
$locale['cxmrsa_wb_unread']   = 'Als ungelesen';
$locale['cxmrsa_wb_del']      = 'Löschen';
$locale['cxmrsa_wb_del_all']  = 'Alle löschen';
$locale['cxmrsa_wb_read_all'] = 'Alle als gelesen';
$locale['cxmrsa_wb_empty']    = 'Keine Einträge vorhanden.';
$locale['cxmrsa_wb_page_open']= 'Wunschbox-Seite öffnen';
$locale['cxmrsa_saved']       = 'Einstellungen gespeichert.';
$locale['cxmrsa_stream_saved']= 'Stream gespeichert.';
$locale['cxmrsa_stream_del']  = 'Stream gelöscht.';

// Designs
$locale['cxmrs_offline_hint']          = 'Gerade läuft keine Sendung. Schau später wieder vorbei.';
$locale['cxmrsd_design']               = 'Design';
$locale['cxmrsd_layout']               = 'Layout';
$locale['cxmrsd_skin']                 = 'Stil';
$locale['cxmrsd_preview']              = 'Vorschau mit Beispieldaten';
$locale['cxmrsd_preview_side']         = 'Seitenpanel';
$locale['cxmrsd_preview_main']         = 'Hauptpanel';
$locale['cxmrsd_skin_classic_note']    = 'Klassische Layouts nutzen keinen Stil.';
$locale['cxmrsd_layout_cover']         = 'Cover groß';
$locale['cxmrsd_layout_cover_desc']    = 'Großes Cover, Titel, Fortschritt und Play.';
$locale['cxmrsd_layout_compact']       = 'Kompakt';
$locale['cxmrsd_layout_compact_desc']  = 'Eine schmale Zeile mit Lauftext. Für volle Seitenleisten.';
$locale['cxmrsd_layout_studio']        = 'Studio';
$locale['cxmrsd_layout_studio_desc']   = 'Moderator und OnAir-Schild im Mittelpunkt, Wunschbox prominent.';
$locale['cxmrsd_layout_host']          = 'Moderator';
$locale['cxmrsd_layout_host_desc']     = 'Großes Moderatorbild mit Name, darunter der laufende Titel.';
$locale['cxmrsd_layout_show']          = 'Sendung';
$locale['cxmrsd_layout_show_desc']     = 'OnAir-Leiste mit Moderator, darunter Titel mit Cover.';
$locale['cxmrsd_layout_duo']           = 'Moderator & Cover';
$locale['cxmrsd_layout_duo_desc']      = 'Moderatorbild und Cover nebeneinander.';
$locale['cxmrsd_layout_history']       = 'Mit Verlauf';
$locale['cxmrsd_layout_history_desc']  = 'Aktueller Titel plus die zuletzt gespielten Titel.';
$locale['cxmrsd_layout_classic3']      = 'Klassisch';
$locale['cxmrsd_layout_classic3_desc'] = 'Das bisherige Banner-Design (in Seitenpanels die alte kompakte Ansicht).';
$locale['cxmrsd_skin_modern']          = 'Modern';
$locale['cxmrsd_skin_modern_desc']     = 'Ruhig und dunkel, korallroter Akzent.';
$locale['cxmrsd_skin_hitparade']       = 'Hitparade';
$locale['cxmrsd_skin_hitparade_desc']  = 'Ultramarin mit leuchtenden Titelschildern und großen Ziffern.';
$locale['cxmrsd_skin_jukebox']         = 'Jukebox';
$locale['cxmrsd_skin_jukebox_desc']    = 'Ochsenblut und Chrom, Titel auf getippten Titelstreifen.';
$locale['cxmrsd_skin_mixtape']         = 'Mixtape';
$locale['cxmrsd_skin_mixtape_desc']    = 'Kassettenhülle mit Etikett und Kugelschreiber-Titel.';
$locale['cxmrsa_lead']           = 'Streams, Designs und Wunschbox an einem Ort.';
$locale['cxmrsa_stat_streams']   = 'Streams';
$locale['cxmrsa_stat_active']    = 'Aktiv';
$locale['cxmrsa_stat_wishes']    = 'Neue Wünsche';
$locale['cxmrsa_sec_stream']     = 'Stream';
$locale['cxmrsa_sec_display']    = 'Anzeige';
$locale['cxmrsa_options']        = 'Optionen';
$locale['cxmrsa_status_on']      = 'Stream ist aktiv (sichtbar)';
$locale['cxmrsa_test']           = 'API prüfen';
$locale['cxmrsa_cancel']         = 'Abbrechen';
$locale['cxmrsa_zero_unlimited'] = '0 = unbegrenzt';
$locale['cxmrsd_skin_vinyl']           = 'Vinyl';
$locale['cxmrsd_skin_vinyl_desc']      = 'Schallplattenschwarz mit goldenem Etikett, das Cover dreht sich beim Hören.';
$locale['cxmrsd_skin_autoradio']       = 'Autoradio';
$locale['cxmrsd_skin_autoradio_desc']  = 'Bernsteinfarbenes Display wie das RDS-Feld im Autoradio.';
$locale['cxmrsd_skin_strand']          = 'Strand';
$locale['cxmrsd_skin_strand_desc']     = 'Petrol, Sand und Sonnenuntergang, Titel auf einer Postkarte.';
$locale['cxmrsd_skin_stuebchen']       = 'Stübchen';
$locale['cxmrsd_skin_stuebchen_desc']  = 'Warmes Holz und Messingschild, gemütlich wie eine Stube.';
$locale['cxmrsd_player_skin']          = 'Stil im Player';
$locale['cxmrsd_player_skin_same']     = 'Wie im Panel';
$locale['cxmrsd_player_skin_hint']     = 'Gilt für Popup-Player, iframe-Einbettung und Wunschbox-Popup.';
$locale['cxmrsa_wb_lead']              = 'Musikwünsche der Live-Sendungen. Die Liste aktualisiert sich jede Minute.';
$locale['cxmrsa_back_admin']           = 'Zurück zur Verwaltung';
$locale['cxmrsa_wb_new']               = 'Neu';
?>
