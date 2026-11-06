# Cache-Warmup

Bereitet REDAXO-Artikel und erfasste Media-Manager-Varianten für die ersten Frontend-Aufrufe vor.

## Artikel, Bilder und Ausgabeformate

Der Seitengenerator setzt für jeden Artikel die aktuelle Artikel-ID und Sprache, damit auch Templates und Module mit `rex_article::getCurrent()` den richtigen Inhalt rendern. Der vorherige Kontext wird nach dem Durchlauf wiederhergestellt, auch bei einem Fehler.

Die Bilderfassung berücksichtigt zusätzlich verschachtelte JSON-Medienfelder `REX_MEDIA_…` und `REX_MEDIALIST_…` in `value1` bis `value10`, etwa aus MForm/MBlock. SVG/SVGZ werden nicht automatisch mit sämtlichen Raster-Medientypen kombiniert, da die GD-Effekte des Media Managers sie nicht dekodieren können. Gezielte SVG-/Medientyp-Paare können weiterhin über `CACHE_WARMUP_IMAGES_WITH_MEDIATYPES` ergänzt werden.

**Media Negotiator ist optional.** Ohne verfügbaren Media Negotiator verwendet der Warmup die bisherigen Artikel-/Sprach- und Bild-/Medientyp-Einträge, ohne zusätzliche Formatdurchläufe oder Änderungen an den Request-Headern. Es wird keine weitere Paketabhängigkeit benötigt. Bei verfügbarem Media Negotiator mit den unterstützten Format-APIs werden Originalformat, WebP und gegebenenfalls AVIF getrennt vorbereitet, entsprechend den Serverfähigkeiten und den Einstellungen `disable_avif` und `preferred_format`. Die bestehenden Extension Points erhalten weiterhin die bisherigen zweispaltigen Listen; die Formatdurchläufe werden erst danach ergänzt.

Die Fortschrittsanzeige zählt bestätigte Seitendurchläufe und Bildvarianten. Eine Bildvariante entspricht einem Bild mit einem Medientyp und gegebenenfalls einem Formatprofil. Bei mehreren Profilen steigen deshalb die angezeigten Zahlen. Standardmäßig wird ein Eintrag pro Request verarbeitet, da bereits ein einzelnes Template zahlreiche Bildgrößen synchron erzeugen kann. Der Warmup kann dadurch länger dauern, während die einzelnen Requests kürzer bleiben.

Fehlgeschlagene Cache-Schreibvorgänge und ungültige Generatorantworten stoppen den Lauf. Die Fehlermeldung enthält die Generator-URL; serverseitige Laufzeitfehler werden im REDAXO-Systemlog protokolliert. Ein abgebrochener Lauf ist kein vollständiger Warmup. Bereits erzeugte Caches bleiben erhalten und werden beim nächsten Lauf wiederverwendet.

Der Warmup führt interne Template- und Media-Manager-Aufrufe aus. Er ist kein vollständiger öffentlicher HTTP-/Browser-Crawl: Domain-, URI-, Session- und benutzerabhängige Sonderfälle sowie beliebige Bildquellen aus HTML oder eigenen Tabellen können zusätzliche Extension-Point-Einträge erfordern.

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/cache-warmup/assets/cache-warmup.png)

## Wofür wird das Addon benötigt?

Manchmal hinterlegt man eine Website zur Ansicht auf einem Testserver. Häufig wird davor oder danach der REDAXO-Cache gelöscht, um veraltete Inhalte zu entfernen, die vielleicht noch aus der Entwicklungszeit enthalten sind. Danach allerdings müssen alle Inhalte neu generiert werden. REDAXO übernimmt dies eigenständig beim Aufruf jeder Seite.

Diese initialen Seitenaufrufe können leider recht langsam sein, vor allem, wenn der Cache für viele Bilder generiert werden muss. Nutzer*innen, denen die technischen Hintergründe nicht bekannt sind, und die erstmalig die Website anschauen, könnten nun (fälschlicherweise) annehmen, REDAXO sei nicht sonderlich schnell. Verständlich, denn sie erhalten im ersten Moment keine performante Website.

Das Cache-Warmup-Addon kann alle verwendeten Inhalte der Website vorab generieren, so dass danach niemand mehr unnötig lange warten muss.

🐿 __Protip:__ Weil Cache-Warmup alle Bilder und Seiten einmal durchläuft, ist es nebenher sehr nützlich, um zu prüfen, ob die Website fehlerfrei ausgeliefert wird. Ob also keine Bilder zu groß sind, um vom Webserver verarbeitet zu werden, und ob alle Templates und Module richtig funktionieren.

---

## 🤕 Fehler beim Warmup-Prozess

Es kommt immer wieder vor, dass Cache-Warmup nicht vollständig durchläuft, sondern vorher mit einem Fehler abbricht. An dieser Stelle ein paar Infos dazu, welche Fehler vorkommen können, und wie du damit umgehen kannst:

### `RAM exceeded (internal)`

__Ursache:__  
Der Arbeitsspeicher des Webservers reicht nicht aus, um alle Bilder zu verarbeiten. Dies ist ein Problem für deine Website, denn es bedeutet, dass die betroffenen Bilder nicht von REDAXOs Media Manager ausgegeben werden können, sondern stattdessen ein Fehlerbild angezeigt wird.

__Maßnahmen:__  
Du solltest nun unbedingt deinen Medienpool prüfen und alle übergroßen  Bilder — das betrifft nicht die Dateigröße, sondern die Pixel! — manuell verkleinern und neu hochladen. Wenn beispielsweise Bilder in deinem Medienpool liegen, die mit einer Digitalkamera aufgenommen worden sind und unbearbeitet hochgeladen wurden, dann sind sie womöglich ~6000 Pixel breit, und REDAXO würde mehr RAM benötigen, um davon Thumbnails zu erstellen, als auf typischen Webspaces verfügbar ist. Wenn du sie auf ~3000 Pixel verkleinerst und neu in den Medienpool lädst, sollte es hoffentlich klappen!  
Wenn möglich, kannst du alternativ auch einfach den Arbeitsspeicher des Webservers vergrößern. 256 MB sollten gut funktionieren, wenn du mit großen Bildern arbeitest.

### `Request Timeout (408)`

__Ursache:__  
Das Generieren des Caches dauert zu lange, so dass die maximale Skriptlaufzeit (max\_execution\_time) des Webservers überschritten wird. Cache-Warmup versucht zwar, abhängig von der Skriptlaufzeit den Cache in kleinen Schritten zu generieren, aber manchmal — etwa bei sehr großen Bildern im Medienpool oder aufwendigen Templates/Modulen — sind selbst kleine Schritte noch zu groß.

__Maßnahmen:__  
Standardmäßig wird pro Request nur ein Artikel-/Sprach-/Formatdurchlauf bzw. eine Bild-/Medientyp-/Formatkombination verarbeitet. Wenn bereits ein einzelner Eintrag die Servergrenze überschreitet, kann eine kleinere Paketgröße das Problem nicht weiter lösen. Prüfe dann das betroffene Template oder Bild anhand der angezeigten Generator-URL und des Systemlogs.

Bei bekannt kurzen Laufzeiten können die Paketgrößen weiterhin über `PACKAGES_INCLUDED` angepasst werden:

```php
rex_extension::register('PACKAGES_INCLUDED', static function () {
    $addon = rex_addon::get('cache_warmup');
    $addon->setConfig('chunkSizePages', 1);
    $addon->setConfig('chunkSizeImages', 5);
});
```

### `Internal Server Error (500)`

__Ursachen:__  
Dieser Fehler kann verschiedene Ursachen haben. Manchmal entsteht er aufgrund von Fehlern im PHP-Code, z. B. innerhalb von Templates oder Modulen. In solchen Fällen müsstest du das Problem eigentlich auch auf der Website sehen, wenn du die betroffenen Seiten aufrufst.

__Maßnahmen:__  
Cache-Warmup bearbeitet normalerweise mehrere Seiten in einem Rutsch (etwa 100 bis 1000) und kann leider nicht unterscheiden, auf welchen davon Fehler auftreten. Um das herauszufinden, kannst du den Warmup für jede Seite einzeln durchlaufen lassen, indem du diesen Code-Schnipsel in die `boot.php` des project-AddOns mit aufnimmst:

```php
// run Cache Warmup addOn in single page mode
rex_extension::register('PACKAGES_INCLUDED', function () {   
    rex_addon::get('cache_warmup')->setConfig('chunkSizePages', 1);
});
```

Womöglich dauert der Warmup-Vorgang nun sehr lange, das ist leider nicht zu ändern. Aber die URL der Fehlerseite liefert dir nun zumindest die ID samt ctype der Seite, auf der das Problem aufgetreten ist.

---

## Extension Points (EP)

Das AddOn stellt verschiedene Extension Points bereit, um in die Auswahl der Artikel und Bilder, deren Cachefiles generiert werden sollen, manuell einzugreifen. Dies kann nützlich sein, um etwa Bilder zu ergänzen, die aus verschiedenen Gründen nicht vom AddOn erfasst worden sind, oder um bestimmte Kategorien oder Medientypen vom Generieren des Caches auszuschließen.

| Extension Point                         | Beschreibung |
| --------------------------------------- | ------------ |
| `CACHE_WARMUP_GENERATE_PAGE`            | Enthält den zu generierenden Artikel und die Sprache. Kann verwendet werden, um Artikel anhand verschiedener Kriterien auszulassen, wenn der Cache generiert wird. |
| `CACHE_WARMUP_GENERATE_IMAGE`           | Enthält das zu generierende Bild und den Medientyp. Kann verwendet werden, um Bilder anhand verschiedener Kriterien auszulassen, wenn der Cache generiert wird. |
| `CACHE_WARMUP_IMAGES`                   | Ermöglicht, die Liste der vom AddOn ausgewählten Bilder zu bearbeiten. |
| `CACHE_WARMUP_MEDIATYPES`               | Ermöglicht, die Liste der vom AddOn ausgewählten Medientypen zu bearbeiten. |
| `CACHE_WARMUP_PAGES_WITH_CLANGS`        | Liefert alle zu generierenden Artikel in ihren Sprachen. Kann verwendet werden, um die Artikelliste zu bearbeiten, vor allem, um weitere Artikel mit Angabe der Sprache zu ergänzen. |
| `CACHE_WARMUP_IMAGES_WITH_MEDIATYPES` | Liefert alle zu generierenden Bilder mit ihren Medientypen. Kann verwendet werden, um die Bilderliste zu bearbeiten, vor allem, um weitere Bilder mit Angabe des Medientyps zu ergänzen. |

## Anwendungsbeispiele für die Nutzung von EPs

Die Beispiele zeigen verschiedene Anwendungsfälle und können beispielsweise __in der `boot.php` des project-AddOns__ hinterlegt werden. 

### `CACHE_WARMUP_GENERATE_PAGE`

Dieser EP wird unmittelbar vorm Generieren der Cachefiles jedes einzelnen Artikels angesprochen und ermöglicht, anhand verschiedener Kriterien den Artikel zu überspringen. Das Codebeispiel zeigt verschiedene Anwendungsfälle:

```php
rex_extension::register('CACHE_WARMUP_GENERATE_PAGE', function (rex_extension_point $ep) {
    list($article_id, $clang) = $ep->getParams();

    $article = rex_article::get($article_id);

    // Artikel mit ID 42 auslassen
    if ($article_id == 42) {
        return false;
    }

    // Artikel der Kategorie 23 und deren Kindkategorien auslassen
    if (in_array(23, $article->getPathAsArray())) {
        return false;
    }

    // Sprache mit clang 2 komplett auslassen
    if ($clang == 2) {
        return false;
    }

    return true;
});
```

### `CACHE_WARMUP_GENERATE_IMAGE `

Dieser EP wird unmittelbar vorm Generieren der Cachefiles jedes einzelnen Bilders angesprochen und ermöglicht, anhand verschiedener Kriterien das Bild zu überspringen. Das Codebeispiel zeigt verschiedene Anwendungsfälle:

```php
rex_extension::register('CACHE_WARMUP_GENERATE_IMAGE', function (rex_extension_point $ep) {
    list($image, $mediaType) = $ep->getParams();

    $media = rex_media::get($image);
    if ($media) {
        if ($media->isImage()) {

            // Bilder vom Typ SVG auslassen
            if ($media->getExtension() == 'svg') {
                return false;
            }
           
            // Bilder der Kategorie 2 auslassen
            if ($media->getCategoryId() == 2) {
                return false;
            }

            // MediaType 'photos' ausschließlich für Bilder der Kategorie 3 verwenden
            if ($mediaType == 'photos' && $media->getCategoryId() != 3) {
                return false;
            }

            // MediaType 'fullscreen' auslassen
            if ($mediaType == 'fullscreen') {
                return false;
            }

            // Interne REDAXO-MediaTypes (beginnen mit 'rex_') auslassen
            if (strpos($mediaType, 'rex_') !== false) {
                return false;
            }
        }
        rex_media::clearInstance($image);
    }
    return true;
});
```

### `CACHE_WARMUP_IMAGES `

Über diesen EP kann die Liste der vom AddOn erfassten Bilder modifiziert werden, um z. B. Bilder aus der Liste zu entfernen, deren Cachefiles nicht generiert werden sollen, oder um Bilder zu ergänzen, die aus verschiedenen Gründen nicht vom AddOn erfasst worden sind.

```php
rex_extension::register('CACHE_WARMUP_IMAGES', function (rex_extension_point $ep) {
    $images = $ep->getSubject();

    // Bilder hinzufügen
    $images[] = 'dave-grohl.jpg';
    $images[] = 'pat-smear.jpg';
    $images[] = 'nate-mendel.jpg';
    $images[] = 'taylor-hawkins.jpg';
    $images[] = 'chris-shiflett.jpg';

    return $images;
});
```

### `CACHE_WARMUP_MEDIATYPES `

Über diesen EP können die im System hinterlegten Mediatypen modifiziert werden, um z. B. Mediatypen aus der Liste zu entfernen, die nicht zum Generieren von Cachefiles verwendet werden sollen, oder um eigene Mediatypen zu ergänzen.

```php
rex_extension::register('CACHE_WARMUP_MEDIATYPES', function (rex_extension_point $ep) {
    $mediaTypes = $ep->getSubject();
    foreach ($mediaTypes as $k => $mediaType) {

        // MediaType 'content' auslassen
        if ($mediaType === 'content') {
            unset($mediaTypes[$k]);
        }

        // REDAXO-MediaTypes auslassen
        if (strpos($mediaType, 'rex_') !== false) {
            unset($mediaTypes[$k]);
        }
    }
    return $mediaTypes;
});
```

### `CACHE_WARMUP_PAGES_WITH_CLANGS`

Liefert alle zu generierenden Artikel in ihren Sprachen. Kann verwendet werden, um die Artikelliste zu bearbeiten, vor allem, um weitere Artikel mit Angabe der Sprache zu ergänzen, z. B. solche Artikel, die aufgrund ihres Offline-Status’ nicht vom AddOn erfasst worden sind.

```php
rex_extension::register('CACHE_WARMUP_PAGES_WITH_CLANGS', function (rex_extension_point $ep) {
    $pages = $ep->getSubject();

    // Seite hinzufügen (article_id, clang)
    $pages[] = array(12, 1);
    $pages[] = array(12, 2);

    return $pages;
});
```

### `CACHE_WARMUP_IMAGES_WITH_MEDIATYPES `

Liefert alle zu generierenden Bilder mit ihren Medientypen. Kann verwendet werden, um die Bilderliste zu bearbeiten, vor allem, um weitere Bilder mit Angabe des Medientyps zu ergänzen.

🐿 __Protip:__ Dieser EP ist sehr nützlich im Umgang mit responsive Images und virtuellen Medientypen!

```php
rex_extension::register('CACHE_WARMUP_IMAGES_WITH_MEDIATYPES', function (rex_extension_point $ep) {
    $images = $ep->getSubject();

    // Bild mit MediaType hinzufügen
    $images[] = array('dave-grohl.jpg', 'portrait');

    // Liste von Bildern mit Liste von MediaTypes hinzufügen
    $imagesToAdd = array(
        'pat-smear.jpg',
        'nate-mendel.jpg',
        'taylor-hawkins.jpg',
        'chris-shiflett.jpg'
    );
    $mediaTypesToAdd = array(
        'type1',
        'type2',
        'type3'
    );
    foreach ($imagesToAdd as $image) {

        // Prüfen, Bilder vorhanden ist
        $media = rex_media::get($image);
        if ($media) {
            if ($media->isImage()) {

                // Bild mit Medientyp hinfügen
                foreach ($mediaTypesToAdd as $mediaType) {
                    $images[] = array($image, $mediaType);
                }
            }
            rex_media::clearInstance($image);
        }
    }

    return $images;
});
```

---

## Ich bin Entwickler*in. Was genau macht das Addon?

1. Es werden alle __Bilder__ erfasst, die in __Modulen, Metainfos und yforms__ verwendet werden, sowie alle definierten __MediaTypes__ des Media Managers. Verschiedene Extension Points (EPs) ermöglichen, die Liste der ausgewählten Bilder und MediaTypes zu bearbeiten, um zu kontrollieren, welche Cachefiles erstellt werden.
2. Es werden alle __Seiten__ erfasst, die online sind, sowie alle __Sprachen__. Verschiedene Extension Points (EPs) ermöglichen, die Liste zu bearbeiten.
3. Aus den erfassten Daten wird __ein großes Array erstellt__ mit Einträgen für jedes Bild mit jedem MediaType und jeder Seite in jeder Sprache. Beispiel: 10 Bilder mit 5 MediaTypes = 50 Bilder. 100 Seiten in 3 Sprachen = 300 Seiten.
4. Artikel-/Sprach- und Bild-/Medientyp-Einträge werden bei verfügbarer Formatverhandlung um die benötigten Formatprofile erweitert und standardmäßig einzeln pro Request verarbeitet. Damit kann später gesteuert werden, wie viele Cachefiles bei jedem Request erstellt werden. Bilder benötigen dabei natürlich massiv mehr Serverressourcen als Seiten.
5. Das Array wird __als JSON im HTML des Popups__ ausgegeben, das das Generieren des Caches triggert, den Fortschritt zeigt und Infos ausgibt. Das Popup __parst das JSON__ und sendet __häppchenweise AJAX-Requests an einen Generator__.
6. Der Generator erstellt die Cachefiles für Bilder und Seiten. Die Angaben dazu, welche Bilder mit welchen Mediatypen und welche Seiten in welchen Sprachen erstellt werden sollen, befinden sich im __Query string__ der URL jedes AJAX-Request.
