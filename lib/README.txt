Immocaster PHP SDK v1.1.42
==========================
Author:     Norman Braun (http://www.medienopfer98.de)
Copyright:  Immocaster UG (haftungsbeschränkt)
Link:       http://www.immocaster.com

Das PHP SDK von Immocaster steht unter der FreeBSD Lizenz zur Verfügung und kann für private sowie kommerzielle Projekte eingesetzt werden. Lediglich die Verweise wie Copyright, Autor, etc. müssen in den Dateien erhalten bleiben. Weitere Infos zur Lizenz befinden sich unter Immocaster/LICENSE.txt.

History
=======

SDK Version 1.1.42
- Korrektur der XML-Dateien

SDK Version 1.1.41
- Methode zum löschen von Attachments
- cURL als Standard für den Datenaustausch (file_get_contents() wird nicht mehr unterstützt)
- Umstellung auf die neue ImmobilienScout24-URL für Sandbox-Anfragen

SDK Version 1.1.38
- Auslesen von Attachements von selbst exportieren Objekten (*BETA)

SDK Version 1.1.36
- Aktivieren von Objekten

SDK Version 1.1.35
- Aktualisieren von Objektdaten
- Deaktivierung von Objekten

SDK Version 1.1.33
- Composer.json für Packagist. (https://packagist.org/packages/immocaster/php-sdk)

SDK Version 1.1.32
- Eigene Exposes via Offer-API auslesen.

SDK Version 1.1.31
- Eigenen XML für den Objektexport durchreichen.

SDK Version 1.1.30
- Bugfix: Exportproblem bei HouseRent und HouseBuy behoben.

SDK Version 1.1.29
- Funktion zum Auslesen aller Objekte eines Maklers.

SDK Version 1.1.28
- Exportfunktion für Objekt-Bilder (JPG,GIF,PNG).

SDK Version 1.1.26
- Exportfunktion für Wohnungen und Häuser zu ImmobilienScout24 (ohne Dateianhänge).

SDK Version 1.1.25
- Möglichkeit für ein Listing von Channels in die ein zertifizierter Nutzer exportieren darf

SDK Version 1.1.24
- Neue Funktion zum auslesen eines Anbieterlogos anhand des Benutzernamen

SDK Version 1.1.23
- Neue Funktion zum auslesen eines Impressums für ein Objekt

SDK Version 1.1.22
- Probleme bei der Registrierung mit cURL behoben
- History in Readme Datei

SDK Version 1.1.20
- Bugs von Version 1.1.19 behoben
- POST Support
- Neue Funktion zum versenden von Kontaktanfragen
- Neue Funktion zum empfehlen von Objekten

SDK Version 1.1.19 - Nicht mehr nutzen!
- Bug: Beim Aufruf von Exposes
- JSON Support

SDK Version 1.1.18
- Support von cURL
- Verbesserte Fehlerausgabe

SDK Version 1.1.15
- Bug von Version 1.1.14 behoben

SDK Version 1.1.14 - Nicht mehr nutzen!
- Bug: Beim Aufruf von Exposes per 3-legged-oauth
- Problem mit SDK bei Hosting-Paketen gelöst (mit "php.ini")
- Objektaufrufe nun über 2 und 3-legged-oauth möglich

SDK Version 1.1.13
- Kleine Updates von Funktionen und Kommentaren

SDK Version 1.1.12
- Integriertes 3-Legged-Oauth zum Zertifizieren von Applikationen
- Neue Möglichkeiten innerhalb der Funktionen (z.B. nur innerhalb von Maklerobjekten suchen)

SDK Version 1.0.6
- Call-Funktionen von private auf public gesetzt um Warnmeldungen zu verhindern

SDK Version 1.0.5
- Erste Version des SDK
