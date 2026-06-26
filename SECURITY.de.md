# Sicherheitsrichtlinie

Englische Sicherheitsrichtlinie: [SECURITY.md](SECURITY.md)

## Unterstützte Versionen

Nur die jeweils neueste veröffentlichte Version des CxM Radiostatus Panel erhält Sicherheitskorrekturen.

## Sicherheitslücke melden

Bitte Sicherheitslücken nicht öffentlich als GitHub Issue melden.

Nutze stattdessen einen privaten Meldeweg:

- GitHub Private Security Advisory, falls im Repository aktiviert.
- Kontaktformular oder Kontaktinformationen auf https://schilinskialexander.com.

Bitte mitschicken:

- Klare Beschreibung der Sicherheitslücke.
- Schritte zum Reproduzieren.
- Betroffene PHP-Fusion-Version, PHP-Version und Webserver-Umgebung.
- Ob Anmeldung oder Adminrechte erforderlich sind.
- Proof of Concept, Screenshots oder Logs, falls hilfreich.

## Veröffentlichung

Sicherheitsmeldungen sollen privat bleiben, bis ein Fix verfügbar ist. Nach Prüfung wird ein Fix vorbereitet; die Release Notes sollten Auswirkung und Update-Empfehlung beschreiben, ohne unnötige Exploit-Details öffentlich zu machen.

## Geltungsbereich

Im Scope:

- Umgehung von Authentifizierung oder Berechtigungen innerhalb der Infusion.
- Cross-Site-Scripting, CSRF, SQL Injection, Dateioffenlegung oder unsicheres Cache-Verhalten innerhalb der Infusion.
- Probleme im Player, in der Wunschbox, im Adminbereich oder in AJAX-Endpunkten.

Nicht im Scope:

- Sicherheitslücken in PHP-Fusion Core, Webserver-Konfiguration, PHP selbst oder Drittanbietern wie laut.fm.
- Probleme durch lokal veränderten Code, der nicht Teil dieser Infusion ist.
- Öffentlich veröffentlichte Reports ohne vorherigen angemessenen privaten Meldeversuch.
