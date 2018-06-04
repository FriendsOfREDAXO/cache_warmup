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

## Ich bin Entwickler*in. Was genau macht das Addon?

1. Es werden alle [Bilder](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/lib/selector.php#L31) erfasst, die in __Modulen, Metainfos und yforms__ verwendet werden, sowie alle definierten [MediaTypes](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/lib/selector.php#L201) des Media Managers. Über den Extension Point `CACHE_WARMUP_IMAGES` können zusätzliche Bilder hinzugefügt werden:
```php
rex_extension::register('CACHE_WARMUP_IMAGES', function ($ep)
{
    $images = $ep->getSubject();
    $db = rex_sql::factory()->setQuery('SELECT filename FROM rex_media');
    while ($db->hasNext()) {
        $images[] = $db->getValue('filename');
        $db->next();
    }
    return $images;
});
```
2. Es werden alle [Seiten](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/lib/selector.php#L224) erfasst, die online sind, sowie alle Sprachen.
3. Aus den erfassten Daten wird [ein großes Array erstellt](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/lib/selector.php#L15) mit Einträgen für jedes Bild mit jedem MediaType und jeder Seite in jeder Sprache. Beispiel: 10 Bilder mit 5 MediaTypes = 50 Bilder. 100 Seiten in 3 Sprachen = 300 Seiten.
4. Das große Array wird danach in viele Häppchen zerhackt, deren Größe von der [Skriptlaufzeit des Servers](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/boot.php#L19-L21) abhängt. Damit kann später gesteuert werden, wie viele Cachefiles bei jedem Request erstellt werden. Bilder benötigen dabei natürlich massiv mehr Serverressourcen als Seiten.
5. Das Array wird [als JSON im HTML des Popups](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/pages/warmup.php#L22) ausgegeben, das das Generieren des Caches triggert, den Fortschritt zeigt und Infos ausgibt. Das Popup [parst das JSON](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/assets/js/cache-warmup.js#L454) und sendet [häppchenweise Ajax requests](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/assets/js/cache-warmup.js#L380) an einen [Generator](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/pages/generator.php).
6. Der Generator erstellt die Cachefiles für [Bilder](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/lib/generator_images.php) und [Seiten](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/lib/generator_pages.php). Die Angaben dazu, welche Bilder mit welchen Mediatypen und welche Seiten in welchen Sprachen erstellt werden sollen, befinden sich im [Query string](https://github.com/FriendsOfREDAXO/cache_warmup/blob/master/pages/generator.php#L6) der URL.

## Extension Points (EP)

| Extension Point               | Beschreibung |
| ----------------------------- | ------------ |
| `CACHE_WARMUP_PAGES`          | … |
| `CACHE_WARMUP_IMAGES`         | … |
| `CACHE_WARMUP_MEDIATYPES`     | … |
| `CACHE_WARMUP_GENERATE_PAGE`  | … |
| `CACHE_WARMUP_GENERATE_IMAGE` | … |

### Anwendungsbeispiele

#### `CACHE_WARMUP_PAGES`

```php
rex_extension::register('CACHE_WARMUP_PAGES', function (rex_extension_point $ep) {
    $pages = $ep->getSubject();

    // Seite hinzufügen (article_id, clang)
    $pages[] = array("12", "1");
    $pages[] = array("12", "2");

    return $pages;
});
```

#### `CACHE_WARMUP_IMAGES `

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

```php
rex_extension::register('CACHE_WARMUP_MEDIATYPES', function (rex_extension_point $ep) {
    $mediaTypes = $ep->getSubject();
    foreach ($mediaTypes as $k => $mediaType) {

        // MediaType 'content' entfernen
        if ($mediaType === 'content') {
            unset($mediaTypes[$k]);
        }

        // REDAXO-MediaTypes entfernen
        if (strpos($mediaType, 'rex_') !== false) {
            unset($mediaTypes[$k]);
        }
    }
    return $mediaTypes;
});
```

#### `CACHE_WARMUP_GENERATE_IMAGE `

```php
rex_extension::register('CACHE_WARMUP_GENERATE_IMAGE', function (rex_extension_point $ep) {
    list($imageId, $mediaType) = $ep->getParams();

    $sql = rex_sql::factory();
    $sql->setQuery('SELECT filename FROM ' . rex::getTablePrefix() . 'media WHERE id=?', [$imageId]);
    $image = $sql->getValue('filename');

    $media = rex_media::get($image);
    if ($media instanceof rex_media && $media->isImage()) {

        // MediaType 'photos' ausschließlich für Bilder der Kategorie 3 verwenden
        if ($mediaType == 'photos' && $media->getCategoryId() != 3) {
            return false;
        }

        // MediaType 'fullscreen' auslassen
        if ($mediaType == 'fullscreen') {
            return false;
        }

        // Bilder der Kategorie 2 auslassen
        if ($media->getCategoryId() == 2) {
            return false;
        }

        // Interne REDAXO-MediaTypes (beginnen mit 'rex_') auslassen
        if (strpos($mediaType, 'rex_') !== false) {
            return false;
        }
    }
    rex_media::clearInstance($item);
    return true;
});
```

#### `CACHE_WARMUP_GENERATE_PAGE `

```php
rex_extension::register('CACHE_WARMUP_GENERATE_PAGE', function (rex_extension_point $ep) {
    list($article_id, $clang) = $ep->getParams();

    $article = rex_article::get($article_id);

    // Artikel mit ID 42 auslassen
    if ($article_id == 42) {
        return false;
    }

    // Artikel der Kategorie 23 und darunter auslassen
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



