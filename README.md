# Cache-Warmup

Generiert den Cache vorab, so dass die Website bereits beim Erstaufruf performant läuft.

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/cache-warmup/assets/cache-warmup.jpg)

## Wofür wird das Addon benötigt?

Manchmal hinterlegt man eine Website zur Ansicht auf einem Testserver. Häufig wird davor oder danach der REDAXO-Cache gelöscht, um veraltete Inhalte zu entfernen, die vielleicht noch aus der Entwicklungszeit enthalten sind. Danach allerdings müssen alle Inhalte neu generiert werden. REDAXO übernimmt dies eigenständig beim Aufruf jeder Seite.

Diese initialen Seitenaufrufe können leider recht langsam sein, vor allem, wenn der Cache für viele Bilder generiert werden muss. Nutzer*innen, denen die technischen Hintergründe nicht bekannt sind, und die erstmalig die Website anschauen, könnten nun (fälschlicherweise) annehmen, REDAXO sei nicht sonderlich schnell. Verständlich, denn sie erhalten im ersten Moment keine performante Website.

Das Cache-Warmup-Addon kann alle verwendeten Inhalte der Website vorab generieren, so dass danach niemand mehr unnötig lange warten muss.

## Fehler `RAM exceeded (internal)`, was hat das zu bedeuten?

Der Arbeitsspeicher des Webservers reicht nicht aus, um alle Bilder zu verarbeiten. Das wird übrigens auch die Website selbst betreffen, nicht nur das Cache-Warmup-Addon. Deshalb sollte nun unbedingt der Medienpool geprüft und alle übergroßen (betrifft Pixel, nicht Dateigröße) Bilder manuell verkleinert werden — oder alternativ der Arbeitsspeicher des Webservers vergrößert werden.

Noch ein Hinweis zu Bildgrößen: Die Pixelwerte sind entscheidend dafür, wieviel RAM benötigt wird, damit REDAXOs Media Manager es verarbeiten kann. Ein Bild mit 4000 × 3000 px und 24 Bit Farbtiefe benötigt bereits 34 MB RAM. Soll daraus vom Media Manager ein Thumbnail in 1920 × 1440 px generiert werden, sind weitere 8 MB notwendig. Der Prozess selbst benötigt zudem (geschätzt) ein zusätzliches 1,5- bis 1,8-faches an Speicher, so dass nun insgesamt schon bis zu 75 MB erforderlich sind. Und natürlich benötigt auch REDAXO selbst noch etwas Speicher.  
— Für dieses Beispiel sollte der Webserver also über mindestens 80–90 MB RAM verfügen, damit die Website fehlerfrei ausgeliefert werden kann.

🐿 __Protip:__ Das Cache-Warmup-Addon ist also auch nützlich, um zu prüfen, ob die Ressourcen des Webservers für die Auslieferung aller Bilder der Website ausreichen.

## Ein anderer Fehler als oben. Was hat der nun zu bedeuten?

Es gibt viele weitere Fehler, die bei der Verwendung des Cache-Warmup-Addons auftreten können. Ein paar typische sind diese:

* `Not Found (404)`  
Die Seite zum Generieren des Caches konnte nicht gefunden werden. Vielleicht hilft an dieser Stelle am ehesten, das Addon neu zu installieren.
* `Request Timeout (408)`  
Das Generieren des Caches — vermutlich eines Bildcaches — hat zuviel Zeit benötigt, so dass der Vorgang vom Server abgebrochen wurde. Dies darf normalerweise nicht vorkommen, weil das Addon den Cache in kleinen Schritten generiert. Bitte einfach nochmal versuchen und/oder die Scriptlaufzeit (max\_execution\_time) des Servers erhöhen.
* `Internal Server Error (500)`  
Allgemeiner Fehler. Irgendwas ist schief gegangen. Die Fehlerseite zeigt hoffentlich weitere Details.
* `Service Unavailable (503)`  
Die Seite zum Generieren des Caches ist nicht erreichbar. Und vermutlich die gesamte Website nicht. Bitte später nochmal versuchen oder prüfen, ob der Server und REDAXO okay sind!

💯 Wir freuen uns über jede Mithilfe, die Qualität des Addons zu verbessern, indem Fehler bei [Github](https://github.com/FriendsOfREDAXO/cache_warmup/issues) gemeldet werden. Vielen Dank!

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

### Anwendungsbeispiele für die Nutzung von EPs

Die Beispiele zeigen verschiedene Anwendungsfälle und können beispielsweise __in der `boot.php` des project-AddOns__ hinterlegt werden. 

#### `CACHE_WARMUP_GENERATE_PAGE`

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

#### `CACHE_WARMUP_GENERATE_IMAGE `

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

#### `CACHE_WARMUP_IMAGES `

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

#### `CACHE_WARMUP_MEDIATYPES `

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
4. Das große Array wird danach in viele Häppchen zerhackt, deren Größe von der __Skriptlaufzeit des Servers__ abhängt. Damit kann später gesteuert werden, wie viele Cachefiles bei jedem Request erstellt werden. Bilder benötigen dabei natürlich massiv mehr Serverressourcen als Seiten.
5. Das Array wird __als JSON im HTML des Popups__ ausgegeben, das das Generieren des Caches triggert, den Fortschritt zeigt und Infos ausgibt. Das Popup __parst das JSON__ und sendet __häppchenweise AJAX-Requests an einen Generator__.
6. Der Generator erstellt die Cachefiles für Bilder und Seiten. Die Angaben dazu, welche Bilder mit welchen Mediatypen und welche Seiten in welchen Sprachen erstellt werden sollen, befinden sich im __Query string__ der URL jedes AJAX-Request.
