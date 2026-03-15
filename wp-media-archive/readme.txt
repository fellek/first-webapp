=== WP Media Archive ===
Contributors: developer
Tags: media, archive, images, audio, gallery
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Strukturiertes Medienarchiv für Bilder und Tonaufnahmen mit Tag-System.

== Description ==

WP Media Archive ist ein WordPress-Plugin, das ein strukturiertes Medienarchiv
für Bilder und Tonaufnahmen bereitstellt. Medieneinträge können mit Tags,
Kategorien und Medientypen organisiert werden.

= Features =

* Custom Post Type "Medienarchiv" für strukturierte Medienverwaltung
* Unterstützung für Bilder und Tonaufnahmen
* Drei Taxonomien: Medien-Tags, Medien-Kategorien, Medientyp
* Metadaten: Autor/Urheber, Aufnahmedatum, Aufnahmeort, Copyright, Dauer
* WordPress Media Library Integration für Datei-Upload
* Responsive Grid-Ansicht im Frontend
* AJAX-basierte Filterung und Suche
* Shortcodes: [medienarchiv] und [medienarchiv_suche]
* REST API Unterstützung
* Vollständig übersetzbar (i18n-ready)

= Shortcodes =

**[medienarchiv]** - Zeigt das Medienarchiv als Grid an.
Parameter:
* typ - Medientyp-Slug (z.B. "bild", "tonaufnahme")
* kategorie - Kategorie-Slug
* tag - Tag-Slug
* anzahl - Anzahl der Einträge (Standard: 12)
* spalten - Anzahl der Spalten (Standard: 3)
* sortierung - Sortierfeld (Standard: date)
* reihenfolge - ASC oder DESC (Standard: DESC)

**[medienarchiv_suche]** - Zeigt ein Suchformular mit Filtern an.

== Installation ==

1. Plugin-Ordner `wp-media-archive` in `/wp-content/plugins/` hochladen
2. Plugin im WordPress-Admin unter "Plugins" aktivieren
3. Unter "Medienarchiv" neue Einträge erstellen

== Changelog ==

= 1.0.0 =
* Erstveröffentlichung
* Custom Post Type mit Medien-Metadaten
* Tag-, Kategorie- und Typ-Taxonomien
* Frontend-Templates (Archiv, Einzelansicht, Taxonomie)
* Shortcodes für flexible Einbindung
* AJAX-Filterung
