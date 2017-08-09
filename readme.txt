=== Immocaster Wordpress Plugin ===
Contributors: hinnerk
Version: 1.3.6
Tags: Immobilien, Immobilie, Estate, Estates, Real Estates, Real Estate, Scort, Scout24, ImmoScout, ImmobilienScout, ImmoScout24, ImmobilienScout24, Immobilienliste, Ergebnisliste, API, Immocaster, SDK, Imme, Makler, Maklerplugin, Makler Website, Maklerobjekte, Makler Objekte, Markier, Wohnungen, Wohnung, Wohnungsvermittlung, Haus, Häuser, Vermieten, Vermietung, Verkaufen, Verkauf, IS24, Import, ImmobilienScout24.de, IS24.de, Objektlisten
Requires at least: 3.7
Tested up to: 3.9.0
Stable tag: 1.3.6
License: GPLv2

Das Wordpress Plugin von Immocaster ermöglicht die Anzeige von Immobilien von ImmobilienScout24 im eingehen Blog.

== Description ==

Dieses Plugin nutzt das Immocaster-SDK (https://github.com/ImmobilienScout24/restapi-php-sdk) um sich mir der API von ImmobilienScout24 zu verbinden und wurde speziell für Makler und Regionale Blog entwickelt. Es ermöglicht das Auslesen von Daten von ImmobilienScout24 um diese auf dem eigenen Blog anzeigen zu lassen.

<strong>ACHTUNG: Der Aufruf von Maklerobjekten bzw. eigenen Objekten ist nicht mit einem Basis-Account von ImmobilienScout24 möglich.</strong>

== Installation ==

1. Zugangsdaten (Key und Secret) unter `http://developer.immobilienscout24.de/rest-api/rest-api-zugang/` anfordern. Achten Sie darauf, den richtigen Zugang zu beantragen. ImmobilienScout24 hat Keys mit verschiedenen Berechtigungen. Entscheiden Sie sich für den Zugriff auf alle Objekte der Immobilienplatform (ohne Exposés) oder als Makler für den Zugriff auf eigene Objekte (mit Exposés).
2. Laden Sie das Plugin herunter und speichern Sie es im Pluginverzeichnis von Wordpress unter `/wp-content/plugins/`.
3. Aktivieren Sie das Plugin wie gewohnt im Administrationsgereicht von Wordpress.
4. Im Bereich Immocaster → ImmobilienScout24 hinterlegen Sie den Key und Secret.
5. Navigieren Sie zu einer beliebigen Seite und editieren Sie diese. Unterhalb des WYSIWYG-Editor finden Sie die Möglichkeit für die Anzeige einer Ergebnisliste.
6. Navigieren Sie zu Design → Widgets um einen Teaser mit Immobilien in der Sidebar anzuzeigen.

== Screenshots ==

1. [Installation: Hinterlegen Sie Key und Secret.](http://medienopfer98.de/immocaster/screen1.jpg)
2. [Ergebnislisten erstellen Sie innerhalb von Seiten.](http://medienopfer98.de/immocaster/screen2.jpg)
3. [Teaser mit Immobilien können im Bereich Widgets angelegt werden.](http://medienopfer98.de/immocaster/screen3.jpg)

== Changelog ==

= 1.3.6 =
* Änderung des Plugin-Authors auf hinnerk

= 1.3.5 =
* Fix für dynamische Seiten in WP 3.9.

= 1.3.4 =
* Anpassung auf die API Änderung für Radiussuchen. "Alle Regionen/All Regions" funktioniert wieder für Listen.
* Fix der Preisanzeige von Büros in Listenansicht.
* Fix der Kartenanzeigen in der Exposeansicht für Büros.

= 1.3.2 =
* Alte Immocaster-Infobox aus Dashboard entfernt.

= 1.3.1 =
* Sicherer Redirect zu IS24 (wp_safe_redirect).

= 1.3.0 =
* Komplette Überarbeitung des Plugins.

= 1.2.27 =
* Bugfix: Probleme bei Zertifizierung.
* Erweiterung der Sprachdatei.
* Feature: Kontaktbox auf Exposeseiten.

= 1.2.26 =
* Bugfix: Weitere jQuery-Probleme mit WP behoben.
* Möglichkeit für das Hinterlegen von IS24-Partnerlinks.

= 1.2.24 =
* Bugfix: Syntax-Problem nach Anpassen des Immocaster-Menüs.

= 1.2.23 =
* Bugfix: jQuery UI Probleme mit neuem WP behoben.

= 1.2.21 =
* Bugfix: Gekürzte Posts mit "weiterlesen"-Link funktioniert wieder (Excerpts).
* Bugfix: Fehlerhafte Ausgabe in Einstellungen behoben.

= 1.2.20 =
* Neuer Objekt-Typ (Laden/Geschäft) für Seiten auswählbar.

= 1.2.19 =
* Neuer Objekt-Typ (Büro/Praxis) für Seiten auswählbar.
* Bugfix: Probleme mit Autocompletion.
* Anpassung in deutschen Übersetzungen.

= 1.2.18 =
* Feature zum ausgeben aller Objekte eines Maklers auf regionaler Ebene.
* Manuelle Angabe für Exposeberechtigung.
* Verschiedene Optimierungen des Plugins für bestimmte Themes.

= 1.2.16 =
* Anpassung an die zukünftigen Expose-Berechtigungen von ImmobilienScout24.
* Syncronisierte Funktionen mit Basic und Pro-Version.

= 1.2.13 =
* Fehler beim Laden von Datei behoben.

= 1.2.11 =
* Erste Beta-Version des Plugins 1.2.x

= 1.1 =
* Das erste WP-Plugin steht nichtmehr zum Download zur Verfügung. Es wird empfohlen die neuere Version des Plugins kostenlos herunterzuladen.