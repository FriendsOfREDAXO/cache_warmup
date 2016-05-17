# Cache-Warmup

Generiert den Cache vorab, so dass die Website bereits beim Erstaufruf performant läuft.

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/cache-warmup/assets/cache-warmup.jpg)

## Fragen?

### Wofür wird das Addon benötigt?

Manchmal hinterlegt man eine Website zur Ansicht auf einem Testserver. Häufig wird davor oder danach der REDAXO-Cache gelöscht, um veraltete Inhalte zu entfernen, die vielleicht noch aus der Entwicklungszeit enthalten sind. Danach allerdings müssen alle Inhalte neu generiert werden. REDAXO übernimmt dies eigenständig beim Aufruf jeder Seite.

Diese initialen Seitenaufrufe können leider recht langsam sein, vor allem, wenn der Cache für viele Bilder generiert werden muss. Nutzer*innen, denen die technischen Hintergründe nicht bekannt sind, und die erstmalig die Website anschauen, könnten nun (fälschlicherweise) annehmen, REDAXO sei nicht sonderlich schnell. Verständlich, denn sie erhalten im ersten Moment keine performante Website.

Das Cache-Warmup-Addon kann alle verwendeten Inhalte der Website vorab generieren, so dass danach niemand mehr unnötig lange warten muss.

### Fehler `RAM exceeded (internal)`, was hat das zu bedeuten?

Der Arbeitsspeicher des Webservers reicht nicht aus, um alle Bilder zu verarbeiten. Das wird übrigens auch die Website selbst betreffen, nicht nur das Cache-Warmup-Addon. Deshalb sollte nun unbedingt der Medienpool geprüft und alle übergroßen (betrifft Pixel, nicht Dateigröße) Bilder manuell verkleinert werden — oder alternativ der Arbeitsspeicher des Webservers vergrößert werden.

Noch ein Hinweis zu Bildgrößen: Die Pixelwerte sind entscheidend dafür, wieviel RAM benötigt wird, damit REDAXOs Media Manager es verarbeiten kann. Ein Bild mit 4000 × 3000 px und 24 Bit Farbtiefe benötigt bereits 34 MB RAM. Soll daraus vom Media Manager ein Thumbnail in 1920 × 1440 px generiert werden, sind weitere 8 MB notwendig. Der Prozess selbst benötigt zudem (geschätzt) ein zusätzliches 1,5- bis 1,8-faches an Speicher, so dass nun insgesamt schon bis zu 75 MB erforderlich sind. Und natürlich benötigt auch REDAXO noch Speicher.  
— Für dieses Beispiel benötigt der Webserver also mindestens 96 MB RAM, damit die Website fehlerfrei ausgeliefert werden kann.

Das Cache-Warmup-Addon ist also auch nützlich, um zu prüfen, ob die Ressourcen des Zielservers für die Generierung aller verwendeten Bilder ausreichen.

### Ein anderer Fehler. Was hat der nun zu bedeuten?

Es gibt viele weitere Fehler, die bei der Verwendung des Cache-Warmup-Addons auftreten können. Ein paar typische sind diese:

* `Not Found (404)`  
Die Seite zum Generieren des Caches konnte nicht gefunden werden. Vielleicht hilft an dieser Stelle am ehesten, das Addon neu zu installieren.
* `Request Timeout (408)`  
Das Generieren des Caches — vermutlich eines Bildcaches — hat zuviel Zeit benötigt, so dass der Vorgang vom Server abgebrochen wurde. Dies darf normalerweise nicht vorkommen, weil das Addon den Cache in kleinen Schritten generiert. Bitte einfach nochmal versuchen und/oder die Scriptlaufzeit (max\_execution\_time) des Servers erhöhen.
* `Internal Server Error (500)`  
Allgemeiner Fehler. Irgendwas ist schief gegangen. Die Fehlerseite zeigt hoffentlich weitere Details.
* `Service Unavailable (503)`  
Die Seite zum Generieren des Caches ist nicht erreichbar. Und vermutlich die gesamte Website nicht. Bitte später nochmal versuchen oder prüfen, ob der Server und REDAXO okay sind!

Wir freuen uns über jede Mithilfe, die Qualität des Addons zu verbessern, indem Fehler bei [Github](https://github.com/FriendsOfREDAXO/cache-warmup/issues) gemeldet werden. Vielen Dank!