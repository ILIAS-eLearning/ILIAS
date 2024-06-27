# Vorschlag zur Überarbeitung von MathJax in ILIAS 10

## Einbindung

MathJax 3 wird als Dependency direkt in die [package.json](../../../package.json) aufgenommen:

````
    "mathjax": "^3.2.2"
````

Auf diese Weise kann es bei ILIAS-Updates aktuell und sicher gehalten werden. Die verwendete MathJax-Version ist eindeutig und kann für Updates getestet werden.

Die Mathjax-Skripte sind bereits kompiliert, laden aber Komponenten nach. Daher müssen die MathJax-Assets per Composer-Init in einen eigenen Komponenten-Ordner kopiert werden.

Es gibt Startskripte für verschiedene Standard-Konfigurationen, z.B. ob SVG oder HTML erzeugt werden soll. Die Auswahl könnte über das ILIAS-Setup oder für Plugins konfigurierbar gemacht werden.

MathJax 2  wird nicht mehr unterstützt. Damit kann der JavaScript-Code in ILIAS für ein nachträgliches Rendering dynamischer Inhalte (z.b. bei Akkorrdions oder Testfragen im Seiteneditor) auf MathJax 3 beschränkt werden.

## Konfiguration

Die bisherigen Konfigurations-Optionen für MathJax im Setup und in der ILIAS-Administration werden entfernt.

Einzig die generelle Aktivierung von MathJax blebt erhalten, so dass in Plattformen, die kein Latex benötigen auf die Einbindung des JavaScripts verzichtet werden kann.

Für MathJax 3 wird eine Konfigurationsdatei [config.js](config.js) eingebunden. Sie wird zusammen mit den Skripten für MathJax als Asset kopiert. In ihr ist der Safe-Mode aktiviert, und es sind die CSS-Klassen definiert mit denen ein Latex-Rendering in Teilen der Seite aktiviert oder deaktiviert werden kann. Außerdem werden in ihr die Begranzer definiert, die MathJax für Latex-Code erkennt. Durch Hinzufügen der Begrenzer von ILIAS, z.B. [tex] und [/tex] muss seitens ILIAS keine Code-Umwandlung mehr erfolgen.

Diese Datei sollte durch eine in der Installation hinzu kopierte `config.local.js` ersetzt werden können (siehe Policy unten).

## MathJax-Server

Im ILIAS-Kern wid kein MathJax-Server mehr unterstützt. Der bisher beschriebene MathJax 2-Server verwendet veraltete Komponenten und ist nicht mehr zu empfehlen. 

Für ILIAS 10 wird als Ersatz ein Plugin entwickelt, das einen MathJax 3-Server verwendet. Es kann die Methoden `exchangeUIRendererAfterInitialization` und `exchangeUIFactoryAfterInitialization` nutzen, um den Seiteninhalt kurz vor der Auslieferung durch den Server so zu verarbeiten wie das MathJax-Skript im Browser. 

## Verwendung

Alle Aufrufe von `ilMathJax` sind veraltet und werden ersetzt. Die verschiedenen `PURPOSE` und `RENDERING`-Parameter von `ilMathJax` sind obsolet. 

Die Latex-Verarbeitung wird nur noch über Komponenten des UI-Frameworks aktiviert. 
Die ILIAS-Komponenten verarbeiten bisher nur Legacy-Content mit Latex  (z.B. die `ilPageObjectGUI`), der teils komplexes HTML enthalten kann. Um den Migrationsaufwand gering zu halten, wird Latex daher zunächst von der Legacy-Komponente unterstützt. 

Bisher:

````
$output = ilMathJax::getInstance()->insertLatexImages($output);
````

Neu: 
````
$output = $this->ui->renderer()->render(
    $this->ui->factory()->legacy($output)->withLatexEnabled());
````



## UI Framework

Für Komponenten im UI-Framerwork, die Latex unterstützten, gibt es zwei neue Interfaces:

* [LatexAwareComponent](../UI/src/Component/LatexAwareComponent.php)
* [LatexAwareRenderer](../UI/src/Implementation/Render/LatexAwareRenderer.php)

Ihre Funktionen können durch Traits hinzugefügt werden:

* [LatexAwareComponentTrait](../UI/src/Implementation/Component/LatexAwareComponentTrait.php)
* [LatexAwareRendererTrait](../UI/src/Implementation/Render/LatexAwareRendererTrait.php)

Die Funktionen der Komponenten beschränken sich auf die Aktivierung und Deaktivierung der Latex-Verarbeitung in der Komponente.

Im Renderer kann der Content entsprechend mit `addLatexEnabling` und `addLatexDisabling` in eine `<div>` mit der dazugehöigen CSS-Klasse gepackt werden. Mit `registerMathJaxResources` werden die für MathJax benötigten Assets in die Seite eingebinden.

Der Renderer bekommt die Information über die MathJax-Aktivierung, die CSS-Klassen und Assets über die Funktion `withMathJaxConfig` des Traits mitgeteilt, die eine Datenklasse [MathJaxUIConfig](src/MathJaxUIConfig.php) übergibt. Das geschieht für Renderer, die das Interface implementieren, in der [DefaultRendererFactory](../UI/src/Implementation/Render/DefaultRendererFactory.php). 

Die DefaultRendererFactory hat dazu eine [MathJaxFactory](src/MathJaxFactory.php) als neue Abhängigkeit, die in der ILIAS-Initialisierung übergeben wird.

## Rendering Policy

Neben der Legacy-Komponente wird das [LatexAwareRenderer](../UI/src/Implementation/Render/LatexAwareRenderer.php)-Interface auch vom Standard-Renderer der Seite implementiert,. Er setzt im `<body>` der Seite die globale CSS-Klasse, mit der eine Latex-Verarbeitung auf der Seite generell unterdrückt und nur in Komponenten freigegeben wird, die es aktiviert haben.

Diese Unterdrückung beschränkt die Darstellung von MathJax auf die von ILIAS vorgesehenen Stellen. Bei einer Umfrage in der SIG Mathe-Digital wurde für knapp die Hälfte aller Institutionen angegeben, dass Latex mit den Standard-Begrenzern von MathJax häufig auch an anderen Stellen verwendet wird.

Es muss daher für lokale Administratoren möglich sein, die  Policy ohne Patchen der Plattform oder Verwendung eines Skins zu ändern. Zwei einfache Mechanismen sind dafür denkbar:

* eine Option im Setup, die die CSS-Klasse zur globalen Deaktivierung steuert,
* eine lokale Konfigurationsdatei für MathJax, die bei der Composer-Installation statt der Standarddatei als Asset kopiert wird.

