# Entwicklung innerhalb AssessmentQuestion

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

