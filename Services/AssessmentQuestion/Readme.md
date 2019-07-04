# Entwicklung innerhalb AssessmentQuestion

## Einfluss TB auf unsere Entwicklung
Björn und ich hatten am 2. Juli ein Meeting mit den Mitgliedern des Technical Boards vom ILIAS open source e-Learning e.V. Die Mitglieder des TBs haben den Anspruch zu verstehen, was wir hier im AssessmenQuestion Service tun und wollen die Konzepte entsprechend verstehen und kommentieren können. 

Unser derzeitiger Entwicklungsfluss liess dies nicht zu und wir sind angehalten die einzelnen Konzepte als einzelne Pullrequests inkl. entsprechender Readmes zu stellen. Eine Abnahmegarantie gibt es nicht(!) Diese Pullrequests werde ich jeweils erstellen indem ich die entsprechenden Files rauskopiere.

Gerne würde ich ASAP den Common-Ordner als Pullrequest stellen.

Gleichzeitig ist ein Api Alignment-Projekt im Gange, bei welcher eine Grundlage für die Entwicklung für ILIAS gelegt werden soll. Genaue Details hierzu sind noch nicht bekannt.

Dies zwingt uns, wenn wir nun nicht das ganze KnowHow, welches wir nun aufgebaut haben, wegschmeissen wollen, zu noch mehr Effizienz in der Umsetzung und fortlaufender Kommunikation mit dem TB. Daher nun folgende Richtlinen fürs weitere Vorgehen.

## Branching
Unser derzeitiger Branch feature/6-0/MessageBus bleibt der Hauptentwicklungsbranch. Es wird nicht mehr direkt in diesen Branch committet. Es erfolgen jeweils Pullrequests. Sobald sämtliche innvolvierten (AL, TT, BH, MST) ihr OK zum Pullrequest gegeben haben wird dieser gemerged. Damit wissen wir alle immer was derzeit im Gang ist.

## DDD / CQRS / EventSourcing
Wir müssen DDD und EventSourcing ausfsplitten und beide Uses Cases unter Common beschreiben. Somit werden wir wo notwenig Abstrakte Klassen haben, welche den reinen DDD Weg aufzeigen und Klassen, welche den erweiterten Weg aufzeigen.

## Beschreibung der Common-Klassen
Die Verwendung der Common Klassen muss im Readme entsprechend verständlich erläutert werden: vgl. Services/AssessmentQuestion/src/Common/DomainModel/Aggregate/docs/documentation/Readme.md

## CHANGELOG
Bitte füllt vor dem Pullrequest kurz das Changelog mit Infos, was ihr gemacht habt. Hier geht es mehr um die Grundsätze / Stand der Arbeit.

## ToDo Folder
Auf sämtlichen Ebenen dürfen ToDo Folder angelegt werden. In diesen Folder können Legacy Klassen abgelegt werden, welche gegebenfalls noch besprochen werden müssen, bei welchen man nicht sicher ist, ob es diese noch braucht oder welche noch ins neue Konzept überführt werden müssen.

## Zeitplan_ToDos.md
Hier führen wir die ToDos auf, welche nächstens anstehen, bitte jeweils abhaken sobald erledigt.

## Mitarbeit restliche srag
Alle MA, welche hier mitlesen, dürfen gerne entsprechende Pullrequests stellen und uns bei der Sicherstellung der Qualität helfen!! Wir sind dankbar um jede Hilfe.

## Wir schaffen das :-)
Aus meiner Sicht - 14 Jahre ILIAS Erfahrung - ist's einer der grössten Meilensteine für ILIAS. Wenn wir dies hier im Team schaffen, dann dürfen wir uns echt freuen darüber! -> Weiter geht's - nun in doppelter Taktung ;-)

Wir müssen nun extrem Gas geben. Ich möchte vor allem auch für Björn das Risiko minimieren. Scheitern wir, so müsste Björn im Schnelldurchlauf das alte Konzept fahren und Test und Fragenservice aufsplitten, damit für 6.0 ein Ergebnis vorhanden ist, auf welchem er aufbauen kann und er somit nicht in eine komplette wirtschaftliche Blockade fällt.

## Literatur:
*Verständlich erläutert für Nicht-Entwickler*
Domain-driven Design erklärt
https://www.heise.de/developer/artikel/Domain-driven-Design-erklaert-3130720.html?seite=all

Podcast zu CQRS und Event Sourcing
https://www.innoq.com/de/podcast/028-event-sourcing-und-cqrs/

Umgang mit komplexen Geschäftsabläufen in einem Microservice
https://docs.microsoft.com/de-de/dotnet/standard/microservices-architecture/microservice-ddd-cqrs-patterns/

CQRS erläutert von Martin Fowler (eine kleine Berümtheit innerhalb von Softwarearchitektur-Fragen) 
https://martinfowler.com/bliki/CQRS.html


*Erläuterung für Entwickler*
CQRS im Original - Artikel von Greg Young
https://cqrs.files.wordpress.com/2010/11/cqrs_documents.pdf

Eine Zusammenfassung von DDD und CQRS für die Umsetzung in PHP
http://xeroxmobileprint.net/DiscoveryTable/test/folder1/Domain-Driven_Design_in_PHP.pdf

CodeBeispiele der Autoren vom obigen Buch
https://github.com/dddinphp


# Decisions (WIP)
Hier notieren wir Entscheide, welche wir fällen und welche entsprechend noch eingearbeitet werden müssen.
## MessageBus
* Wird eigenimplementiert -> keine Verwendung einer Library, da ein relativ einfaches Konzept

## Caching
Domain schaut, dass diese jeweils einen aktuellen Cache seines Objekts hat, damit nicht immer der gesamte Eventstore durchgearbeitet werden muss.

Hierbei müssen wir beachten, dass ILIAS ebenfalls auf mehreren Webservern betrieben werden kann. Somit sollte immer wenn aus dem Cache gelesen wird, sich der Cache beim Evenstore zurückversichern, ob der Cache noch aktuell ist. Eventstore anfragen, gib mir alles seit meinem letzten Cache update.

## ValueObjects
Constructor alles Attribute
& Getter (keine Setter).

## Zuständigkeit Fragenservice
Liefert eine gesamte Authoring-Umgebung inkl. Navigation zwischen den Fragenservice-Formularen.

## Abgrenzung
ASQ-Service
* Domainlogik / CQRS Interface
* Abläufe / Logik / Flow etc.

Authoring-Umgebung / Formular-Umgebung
* via Builder ähnlich zu Kitchen-Sink-Objekte (src/ui)

# Serialization
* wir verwenden PHP Standard-Funktionen json_decode / json_encode


#Libraries
* Ramsey/UUID - Verwendung da gemäss definiertem RFC umgesetzt.
** https://packagist.org/packages/ramsey/uuid

