# Security Policy

German security policy: [SECURITY.de.md](SECURITY.de.md)

## Supported Versions

Only the latest released version of CxM Radiostatus Panel is supported with security fixes.

## Reporting a Vulnerability

Please do not report security vulnerabilities in public GitHub issues.

Use one of these private reporting channels instead:

- GitHub private security advisory, if enabled for the repository.
- Contact form or contact information on https://schilinskialexander.com.

Please include:

- A clear description of the vulnerability.
- Steps to reproduce.
- Affected PHP-Fusion version, PHP version, and web server environment.
- Whether authentication or admin rights are required.
- Any proof of concept, screenshots, or logs that help verify the issue.

## Disclosure

Security reports should stay private until a fix is available. After verification, a fix will be prepared and the release notes should describe the impact and update recommendation without publishing exploit details unnecessarily.

## Scope

In scope:

- Authentication or authorization bypasses in the infusion.
- Cross-site scripting, CSRF, SQL injection, file disclosure, or unsafe cache handling in the infusion.
- Issues in the player, wishbox, admin area, or AJAX endpoints.

Out of scope:

- Vulnerabilities in PHP-Fusion core, web server configuration, PHP itself, or third-party services such as laut.fm.
- Issues caused by modified local code that is not part of this infusion.
- Publicly disclosed reports without a reasonable private reporting attempt first.
