# CxM Radiostatus Panel

PHP-Fusion infusion for displaying laut.fm and LexyCast radio stream status, player links, current metadata, history, upcoming artists, and a moderator-controlled wishbox.

German documentation: [README.de.md](README.de.md)

## Features

- Displays one or more configured stations in PHP-Fusion panels, source selectable per stream: **laut.fm** or **LexyCast**.
- LexyCast: just enter the panel address with port (e.g. `https://c02.lexycast.de:4020`). Station, channel, stream URL, current track with cover and album, history, listener count and live moderator are read from the public LexyCast API v2. For a station other than the default, append `#number` (e.g. `…:4020#2`).
- Supports side and main panel layouts with selectable themes.
- Fetches station data, current song, listener count, playlist, history, and upcoming artists from the laut.fm API.
- Uses a local cache to reduce API requests.
- Provides HTML5 popup player, iframe embed, M3U download, and PLS download links.
- Optional stream URL override per station.
- Optional wishbox for live moderated shows.
- Permission controls for stream visibility and wishbox access.
- Admin area for streams, ordering, status, embed preview, and global settings.

## Requirements

- PHP-Fusion installation with the classic infusion system.
- PHP with outbound HTTP/HTTPS access to `https://api.laut.fm` or your LexyCast server (`allow_url_fopen`).
- Writable `infusions/cxm_radiostatus_panel/cache/` directory for generated cache files.
- JavaScript enabled in the visitor browser for automatic panel updates.
- Securimage/captcha support from the PHP-Fusion installation for guest wishbox submissions.

## Installation

1. Upload the folder `cxm_radiostatus_panel` to your PHP-Fusion `infusions/` directory.
2. Make sure the cache directory is writable by the web server:
   ```text
   infusions/cxm_radiostatus_panel/cache/
   ```
3. Log in as administrator.
4. Open the PHP-Fusion infusion administration area.
5. Infuse/install **Radio Status**.
6. Open the **Radio Status** admin panel.
7. Add at least one stream:
   - Display name
   - Source (laut.fm or LexyCast)
   - laut.fm station slug or LexyCast address with port
   - Panel position
   - Access level
   - Optional stream URL override
   - Optional wishbox settings
8. Check the generated panel position in PHP-Fusion panel administration and enable or move it if needed.

## Configuration

The admin settings include:

- Cache lifetime in seconds.
- Side panel reload interval in milliseconds.
- Main panel reload interval in milliseconds.
- laut.fm API base URL, default: `https://api.laut.fm`.
- Wishbox date format.

## Designs

Each stream gets a layout and a style under **Design** in the admin area, picked from cards with a live preview (sample data, switchable between side and main panel). All new layouts work left/right and in the centre and adapt to the panel width.

- **Layouts:** Large cover, Compact, Studio (host and on-air sign up front), Host (large host picture with name), Show (on-air bar with host), Host & cover (both pictures side by side), With history (recently played, plus upcoming artists on laut.fm). The previous banner design remains available as **Classic**; the old design with audio player was removed and streams using it switch to "Large cover".
- **Styles:** Modern, Hit parade (ultramarine with lit title plates), Jukebox (oxblood, chrome, typed title strips), Mixtape (cassette label with ballpoint title), Vinyl (the cover spins like a record), Car radio (amber display), Beach (titles on a postcard), Parlour (wood and brass plate). The style also applies to the popup player, iframe embed and request box; **Player style** can give the player its own style.
- **Request box admin:** its own light surface with a tab per stream, new requests highlighted.
- **In the panel:** play/pause right in the panel (keeps playing across automatic refreshes), progress bar with the real remaining time, scrolling text only for long titles, animation on track change.
- **Fonts:** bundled locally in `fonts/` (SIL Open Font License); no external services are loaded.

## Updates

1. Back up your database and existing infusion files.
2. Replace the files in `infusions/cxm_radiostatus_panel/`.
3. Keep generated cache files out of release packages.
4. Recheck cache directory write permissions.
5. Open the admin area and verify stream settings. The first visit adds missing columns (`rs_source`, `rs_skin`, `rs_player_skin`). Existing streams stay on laut.fm.

## Uninstall

Use the PHP-Fusion infusion administration area to defuse/uninstall the infusion. This removes the infusion database tables, settings, and registered panel entry.

## License

Released under the Affero GPL license. See `agpl.txt` for the full license text.
