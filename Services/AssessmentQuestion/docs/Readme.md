# Schedule 

##Workshops zu DDD / EventBus / CQRS
**Ziele**
* KnowHow Transfer
* Klärung ob die erstellten Interfaces / Klassen der ILIAS Allgemeinheit zur Verüfgung gestellt werden dürfen.
* Alternativen: 
	* Innerhalb ASQ platzieren
	* Als Packagist bereitstellen
	
**Verantwortlich / Durchführung bis**
* mstuder, 31.07.2019
	
**Tasks**
* [x] 01.07.2017: Organisation der Workshops
* [ ] 01.07.2017 - 31.07.2019: Bereitstellung dezidierter Readmes für die jeweiligen Bereiche
* [ ] 01.07.2017 - 31.07.2019: Aufräumen / kleinere übersichtleriche Pullrequests erstellen
* [ ] 01.07.2017 - 31.07.2019: Durchführen der Workshops


## Service Klassen sowohl nach Aussen als auch interne sind angelegt.
**Ziele**
* Services nach Aussen sind definiert
* Entscheid Ein Service / mehrere Services ist getroffen.
* Internal Service für die Verwendung der eigenenen ASQ-Authoring-Umgebung ist abschliessend erstellt
* Api Alignment wird - sofern kompatibel - eingearbeitet sobald dieses freigegeben wurde.

**Verantwortlich / Durchführung bis**
* mstuder & bheyser, 18.07.2019

**Tasks**
* [ ] 01.07.2019 - 18.07.2019: Entscheid eine oder mehrere Serviceklassen gefällt.
* [ ] 01.07.2019 - 18.07.2019: Erstimplementierung Service abgeschlossen.
* [ ] 01.07.2019 - 18.07.2019: Command-Klassen sind angelegt.


## Event und Commandbus
**Ziele**
* Bereitstellung von Even & Commandbus. Hierbei die derzeitige Library entfernen, damit wir unabhängig sind und an dieser Stelle keine zusätzliche Library-Diskussion führen müssen.

**Verantwortlich / Durchführung bis**
* aluethi, 05.07.2019

**Tasks**
* [ ] 01.07.2019 - 05.07.2019: Entfernung library, Ergänung unserer bestehenden Klassen
* [ ] 01.07.2019 - 05.07.2019: Readme erstellen, welche beschreibt wie der Commandbus, Eventbus sowie die Middlewares genutzt werden können. Dies auch unhabhängig von CQRS / Eventsourcing.7
* [ ] 01.07.2019 - 05.07.2019: Entscheid bezüglich Platzierung _Workshops zu DDD / EventBus / CQRS_ sobald getroffen umsetzen.


## Konzept Validierung ist geklärt
**Ziele**
* Notwendige Validierungen sind geklärt.
* Welche Art der Validierung wo vorgenommen wird, ist abschliessend geklärt
* Es existiert ein Readme


**Verantwortlich / Durchführung bis**
* aluethi, 12.07.2019

**Tasks**
* [ ] 01.07.2019 - 12.07.2019: Klärung und Beschreibung Validierung

## Interfaces und Abstrakte Klassen für DDD
**Ziele**
* Bereitstellung der notwendigen DDD Intrefaces und Klassen.

**Verantwortlich / Durchführung bis**
* aluethi, 05.07.2019

**Tasks**
* [ ] 01.07.2019 - 05.07.2019: Bestehende Klassen prüfen und eränzen (aus Konsistenzgründen Intreface ValueObject)
* [ ] 01.07.2019 - 05.07.2019: Readme erstellen, welche beschreibt wie diese Klassen genutzt werden können sowohl EventSourced als auch ohne EventSourced
* [ ] 01.07.2019 - 05.07.2019: Entscheid bezüglich Platzierung _Workshops zu DDD / EventBus / CQRS_ sobald getroffen umsetzen.


## Sämtliche Value Object / Entities und Repositories implementiert
**Ziele**
* Sämtliche Value Objects & Entities sind angelegt.
* Entscheid ob ein oder zwei Repositories AggregateQuestion und (?)AggregateSolution / AggregateTest(?) ist abschliessend gefällt.

## Sämtliche Formulare sind implementiert
**Verantwortlich / Durchführung bis**
* bheyser, 31.07.2019

**Tasks**
* [ ] 01.07.2019 - 31.07.2019: Value Objects anlegen
* [ ] 01.07.2019 - 31.07.2019: Entscheid Repositories fällen.

## FormBuilder für Authoring-Umgebung
**Ziele**
* Formbuilder inkl. Navigationskonzept ist erstellt.

**Verantwortlich / Durchführung bis**
* mstuder, 19.07.2019

**Tasks**
* [ ] 01.07.2019 - 19.07.2019: Formbuilder für ist erstellt.

## Business-Logik
**Ziele**
* Formbuilder inkl. Navigationskonzept ist erstellt.

**Verantwortlich / Durchführung bis**
* bheyser & mstuder, 15.08.2019

**Tasks**
* [ ] 15.07.2019 - 18.08.2019: Business-Logik erstellt.


# Decisions (WIP)
## MessageBus
* Wird eigenimplementiert -> keine Verwendung einer Library, da ein relativ einfaches Konzept

## Caching
Domain schaut, dass diese jeweils einen aktuellen Cache seines Objekts hat, damit nicht immer der gesamte Eventstore durchgearbeitet werden muss.

Hierbei müssen wir beachten, dass ILIAS ebenfalls auf mehreren Webservern betrieben werden kann. Somit sollte immer wenn aus dem Cache gelesen wird, sich der Cache beim Evenstore zurückversichern, ob der Cache noch aktuell ist. Eventstore anfragen, gib mir alles seit meinem letzten Cache update.

# ValueObjects
Constructor alles Attribute
& Getter (keine Setter).

# Zuständigkeit Fragenservice
Liefert eine gesamte Authoring-Umgebung inkl. Navigation zwischen den Fragenservice-Formularen.

# Abgrenzung
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




