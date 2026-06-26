# CxM Radiostatus Panel

PHP-Fusion infusion for displaying laut.fm radio stream status, player links, current metadata, history, upcoming artists, and a moderator-controlled wishbox.

German documentation: [README.de.md](README.de.md)

## Features

- Displays one or more configured laut.fm stations in PHP-Fusion panels.
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
- PHP with outbound HTTP/HTTPS access to `https://api.laut.fm`.
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
   - laut.fm station slug
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
- API base URL, default: `https://api.laut.fm`.
- Wishbox date format.

## Updates

1. Back up your database and existing infusion files.
2. Replace the files in `infusions/cxm_radiostatus_panel/`.
3. Keep generated cache files out of release packages.
4. Recheck cache directory write permissions.
5. Open the admin area and verify stream settings.

## Uninstall

Use the PHP-Fusion infusion administration area to defuse/uninstall the infusion. This removes the infusion database tables, settings, and registered panel entry.

## License

Released under the Affero GPL license. See `agpl.txt` for the full license text.
