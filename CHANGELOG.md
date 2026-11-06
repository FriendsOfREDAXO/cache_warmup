# Changelog: Cache Warmup

## Unreleased

* Globalen Artikel- und Sprachkontext pro Artikel setzen und auch nach Fehlern wiederherstellen.
* Original-/WebP-/AVIF-Caches bei verfügbarem Media Negotiator getrennt vorbereiten; keine zusätzliche Paketabhängigkeit.
* Verschachtelte JSON-Medienfelder in `value1`–`value10` berücksichtigen.
* SVG/SVGZ nicht automatisch mit sämtlichen Raster-Medientypen kombinieren; gezielte Ergänzungen per Extension Point bleiben möglich.
* Konservative Paketgrößen für aufwendige Template- und Bildgenerierung verwenden.
* Artikel-/Bildcache-Ergebnisse und Generatorantworten prüfen; Fortschritt erst nach bestätigtem Erfolg erhöhen und bei Fehlern abbrechen.
* Medieninstanzen anhand des Dateinamens freigeben und leere Metainfo-/Medien-ID-Abfragen abfangen.


## [4.0.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/4.0.0) – 02.07.2022

### Breaking changes

* Erfordert REDAXO 5.4 und PHP 7.4 als Mindestversionen  
  Alter Code wurde entfernt, um die Komplexität zu verringern! 🦊


## [3.7.1](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.7.1) – 12.06.2022

### Bugfixes

* Externe Pakete aktualisiert
* Code aufgeräumt


## [3.7.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.7.0) – 20.11.2021

### Features

* Dokumentation für den Dark Mode angepasst (REDAXO 5.13)
* Konflikte mit YForm 4 entfernt
* PHP-Mindestversion auf 7 erhöht


## [3.6.1](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.6.1) – 10.10.2019

### Bugfixes

* Generator responds with HTTP 200 to provide YRewrite 2.6+ compat (#106) 


## [3.6.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.6.0) – 10.02.2019

### Features

* Funktioniert mit YForm 3 (#103)


## [3.5.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.5.0) – 24.12.2018

### Features

* Debug-Modus wird nicht mehr innerhalb des JS aktiviert und deaktiviert, sondern hängt nun fest an REDAXOs Debug-Modus. (#92)
* Dokumentation: Umgang mit Fehlern beim Warmup-Prozess


## [3.4.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.4.0) – 28.09.2018

### Features

* Use `includeCurrentPageSubPath` (#94 @christophboecker)
   Requires at least REDAXO 5.1

### Bugfixes

* fix wrong `rex_media::clearInstance` values (#97 @staabm)


## [3.3.1](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.3.1) – 14.07.2018

### Features

* Performance: JS/CSS nur auf Warmup-Seiten laden (#83 @staabm)
* Performance: Generierung von Medien optimiert (#84 @staabm)
* Extension Points (EPs) zum Filtern der zu generierenden Objekte (#90 @schuer)
* Spanische Übersetzung, Traducción en castellano. ¡Gracias! (#91 @nandes2062)

Hilfe zur Benutzung der neuen Extension Points findet ihr in der README! 🚀


## [3.3.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.3.0) – 14.07.2018

-> [3.3.1](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.3.1)


## [3.2.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.2.0) – 13.10.2017

### Features

* Schwedisch. Tack så mycket @interweave-media! (#78)

### Bugfixes

* Cache-Buster mittels AddOn-Version entfernt, weil REDAXO seit 5.3 einen eigenen mitbringt (#79)


## [3.1.1](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.1.1) – 29.09.2017

### Bugfixes

* Konfiguration nur noch bei Aufruf der Warmup-Seite bearbeiten (#76 @IngoWinter)
Verbessert die Performance und vermeidet Session-Lock-Probleme.


## [3.1.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.1.0) – 20.08.2017

### Features

* YForm-Kompatibilität: Der Feldtyp `mediafile` (Uploads in den Medienpool) wird nun ebenfalls beachtet. (#74)


## [3.0.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/3.0.0) – 14.06.2017

### Features

* __Template beim Generieren der Artikelcaches mit einbeziehen__  (#71)
Bisher wurden nur die reinen Artikelinhalte generiert. Das war völlig okay, sorgte aber für Fehler, wenn innerhalb der Templates wichtige Dinge definiert wurden, so wie z. B. die Tabs der beliebten [Basisdemo](https://github.com/FriendsOfREDAXO/demo_base). Mit diesem Update lädt Cache-Warmup nicht mehr nur die Artikel, sondern auch die Templates drumrum.
* __Generierung der Bildercaches über neue Funktionen des Media-Managers__ (#72)
Der Media-Manager 2.3.0 enthält eine separate Methode (`rex_media_manager::create()`), um Cachefiles von Bildern mit allen Bildeffekten zu generieren. Diese benutzen wir nun auch für Cache-Warmup und haben unseren alten Code, der dafür notwendig war, als _deprecated_ markiert.

### Breaking changes

* Artikelinhalte werden nun anders generiert als vorher, nämlich inklusive ihrer Templates drumrum. Weil das potentiell zu anderen Ergebnissen führen kann, als mit der vorherigen Version von Cache-Warmup, ist diese aktuelle Version ein _Major Release_.


## [2.3.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/2.3.0) – 10.06.2017

### Features

* Extension Point (EP) `CACHE_WARMUP_IMAGES` hinzugefügt, um Entwickler\_innen die Möglichkeit zu geben, Bilder zu ergänzen, für die Cachefiles generiert werden. (@IngoWinter: #69, #70)


## [2.2.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/2.2.0) – 07.06.2017

### Features

* Wechsel in den Frontend-Modus vorm Generieren der Cache-Files (#68)
Dadurch werden auch Inhaltsanpassungen fremder AddOns beachtet, die mittels Extension Points die Ausgabe beeinflussen, z. B. der Slice-Status on/off durch [blÖcks](https://github.com/FriendsOfREDAXO/bloecks).
* Portugiesisch, vielen Dank an Taina Soares! (#67)


## [2.1.1](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/2.1.1) – 29.03.2017

Workaround für Nutzer:innen des [blÖcks](https://github.com/FriendsOfREDAXO/bloecks)-Addons: Cache-Warmup generiert vorerst keine Artikelinhalte mehr, solange der Slice-Status (Online/Offline) nicht beachtet wird. Mit diesem Update wird verhindert, dass Offline-Inhalte publiziert werden.

Siehe Diskussion: https://github.com/FriendsOfREDAXO/cache_warmup/issues/65


## [2.1.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/2.1.0) – 13.03.2017

### Features

* 💥 Cachefiles pro Request deutlich erhöht (#58) — Danke an alle fürs Abstimmen und Testen!
* Konflikte mit anderen Addons/Plugins definiert (#51)

### Bugfixes

* Unnötigen Parameter entfernt (#57, @staabm)
* Darstellungsfehler mit dem be_style-Customizer behoben (#56)
* Fehler beim Speicherüberlauf abfangen (#62)

### Security

* ⚠️ Bilder-IDs prüfen und absichern (#63) — Danke @gharlan!


## [2.0.1](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/2.0.1) – 25.01.2017

### Bugfixes

* Kompatibilität für REX <5.1 wiederhergestellt (#48)


## [2.0.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/2.0.0) – 20.01.2017

Für dieses Release wurden einige Fehler behoben und Funktionen verbessert, so dass Cache-Warmup nun merklich weniger Speicher benötigt und deutlich schneller läuft als vorher. Hooray!

### Bugfixes & Improvements:

* 💥 Speichernutzung beim Filtern von Bildern korrigiert (#36, @isospin @staabm @gharlan)
* 💥 Speichernutzung beim Prüfen von Bildern massiv reduziert (#44, @gharlan)
* 💥 Filterung der Artikel korrigiert (#43, @tbaddade @staabm)
* Code vereinfacht und verbessert (#30 #34 #35, jeweils @staabm)
* Addon-Beschreibung verbessert (#38)
* PHP-Mindestversion definiert (#32)
* `help.php` entfernt, so dass die Hilfefunktion nun den Inhalt der README anzeigt (#47)

### Breaking changes:

* Sichtbarkeit einiger Methoden verringert (#33, @staabm)
* `cache_warmup_writer::replaceOutputWith()` entfernt (#39)
* `cache_warmup_selector::getLanguages()` entfernt (#43)


## [1.0.3](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/1.0.3) – 10.10.2016

### Bugfixes

* Warnings im Systemlog unterbunden (#24)


## [1.0.2](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/1.0.2) – 18.08.2016

### Bugfixes

* Popup nun mit Hinweis, falls keine Artikel oder Bilder zum Generieren vorhanden sind. #23
* Bilder konnten nicht in Sprachen (clang) oder Medien (media) hinterlegt werden. #25


## [1.0.1](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/1.0.1) – 29.07.2016

### Bugfixes

* Kleine PHP-Korrekturen und Aufräumarbeiten #19
* Kleine JavaScript-Korrekturen und Aufräumarbeiten #20


## [1.0.0](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/1.0.0) – 02.06.2016

### Breaking changes

* Unterstriche statt Bindestrichen (REDAXO-Standard, erforderlich für myREDAXO) #17


## [1.0.0-RC1](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/1.0.0-RC1) – 25.05.2016

### Bugfixes

* Ajax Request mit Timestamp, um Caching zu vermeiden #15

### Breaking changes

* Unterstriche statt Bindestrichen (REDAXO-Standard, erforderlich für myREDAXO) #16


## [1.0.0-beta4](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/1.0.0-beta4) – 20.05.2016

### Bugfixes

* Assets-Caching vermeiden + kleine JS-Fixes #12
* Fehler vermeiden, wenn kein Metainfo-Feld existiert #13
* package.yml Verbesserungen #14


## [1.0.0-beta3](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/1.0.0-beta3) – 18.05.2016

### Bugfixes

* JS-Fehler & Popup-Größe #10


## [1.0.0-beta2](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/1.0.0-beta2) – 18.05.2016

### Bugfixes

* Kontext für Subpage system.cache-warmup (REX-5.0-Kompatibilität) #6
* Bilder werden in manchen Umgebungen zu groß generiert #7


## [1.0.0-beta1](https://github.com/FriendsOfREDAXO/cache_warmup/releases/tag/1.0.0-beta1) – 18.05.2016

Erstes Beta-Release
