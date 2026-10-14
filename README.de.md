# CxM Radiostatus Panel

PHP-Fusion-Infusion zur Anzeige von laut.fm- und LexyCast-Radiostream-Status, Player-Links, aktuellen Metadaten, Verlauf, kommenden Künstlern und einer moderierten Wunschbox.

Englische Dokumentation: [README.md](README.md)

## Funktionen

- Zeigt eine oder mehrere konfigurierte Stationen in PHP-Fusion-Panels an, pro Stream wählbar: **laut.fm** oder **LexyCast**.
- LexyCast: nur die Panel-Adresse mit Port eintragen (z. B. `https://c02.lexycast.de:4020`). Sender, Kanal, Stream-URL, Titel mit Cover und Album, Verlauf, Hörerzahl und Live-Moderator kommen automatisch aus der öffentlichen LexyCast-API v2. Für einen anderen als den Standard-Sender `#Nummer` anhängen (z. B. `…:4020#2`).
- Unterstützt Seiten- und Hauptpanel-Layouts mit auswählbaren Themes.
- Lädt Stationsdaten, aktuellen Titel, Hörerzahl, Playlist, Verlauf und kommende Künstler über die laut.fm API.
- Nutzt einen lokalen Cache, um API-Anfragen zu reduzieren.
- Bietet HTML5-Popup-Player, iframe-Embed, M3U-Download und PLS-Download.
- Optionale Stream-URL-Überschreibung pro Station.
- Optionale Wunschbox für live moderierte Sendungen.
- Rechteverwaltung für Stream-Anzeige und Wunschbox-Zugriff.
- Adminbereich für Streams, Reihenfolge, Status, Embed-Vorschau und globale Einstellungen.

## Voraussetzungen

- PHP-Fusion-Installation mit klassischem Infusion-System.
- PHP mit ausgehendem HTTP/HTTPS-Zugriff auf `https://api.laut.fm` bzw. den LexyCast-Server (`allow_url_fopen`).
- Beschreibbarer Ordner `infusions/cxm_radiostatus_panel/cache/` für generierte Cachedateien.
- Aktiviertes JavaScript im Browser der Besucher für automatische Panel-Aktualisierung.
- Securimage/Captcha-Unterstützung der PHP-Fusion-Installation für Wunschbox-Einsendungen von Gästen.

## Installation

1. Den Ordner `cxm_radiostatus_panel` in das PHP-Fusion-Verzeichnis `infusions/` hochladen.
2. Sicherstellen, dass der Cache-Ordner vom Webserver beschreibbar ist:
   ```text
   infusions/cxm_radiostatus_panel/cache/
   ```
3. Als Administrator anmelden.
4. Die PHP-Fusion Infusion-Verwaltung öffnen.
5. **Radio Status** infusionieren/installieren.
6. Das Adminpanel **Radio Status** öffnen.
7. Mindestens einen Stream anlegen:
   - Anzeigename
   - Quelle (laut.fm oder LexyCast)
   - laut.fm Stations-Slug bzw. LexyCast-Adresse mit Port
   - Panel-Position
   - Zugriffslevel
   - Optionale Stream-URL-Überschreibung
   - Optionale Wunschbox-Einstellungen
8. Die erzeugte Panel-Position in der PHP-Fusion Panel-Verwaltung prüfen und bei Bedarf aktivieren oder verschieben.

## Konfiguration

Die Einstellungen im Adminbereich umfassen:

- Cache-Laufzeit in Sekunden.
- Reload-Intervall für Seitenpanels in Millisekunden.
- Reload-Intervall für Hauptpanels in Millisekunden.
- laut.fm API-Basis-URL, Standard: `https://api.laut.fm`.
- Datumsformat für die Wunschbox.

## Updates

1. Datenbank und vorhandene Infusion-Dateien sichern.
2. Dateien in `infusions/cxm_radiostatus_panel/` ersetzen.
3. Generierte Cachedateien nicht in Release-Pakete übernehmen.
4. Schreibrechte des Cache-Ordners erneut prüfen.
5. Adminbereich öffnen und Stream-Einstellungen kontrollieren. Beim ersten Aufruf ergänzt er bei Updates von 1.1 die Spalte `rs_source` (bestehende Streams bleiben laut.fm).

## Deinstallation

Die Infusion über die PHP-Fusion Infusion-Verwaltung defusionieren/deinstallieren. Dabei werden die Infusion-Tabellen, Einstellungen und der registrierte Panel-Eintrag entfernt.

## Lizenz

Veröffentlicht unter der Affero GPL License. Der vollständige Lizenztext liegt in `agpl.txt`.
