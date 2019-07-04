## Entscheidungsprozess zu DDD / EventBus / CQRS

[ ] 03.07.2019 - @al Könntest du dir bitte die Klassen unter Common ansehen und gegebenfalls noch korrigieren / fertigstellen und unsere derzeitige Entwicklung nun auf diese Klassen zeigen lassen?

[ ] 04.07.2019 ZIEL: mst macht ein dezidierter Commit an das TB mit dem CommonFolder mit den DDD & Eventsourcing-Klassen


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
* Bereitstellung von Even & Commandbus. Hierbei die derzeitige Library entfernen, damit wir unabhängig sind und an dieser Stelle keine zusätzliche Library-Diskussion führen müssen. Bereistellung im Common-Folder

**Verantwortlich / Durchführung bis**
* aluethi, 05.07.2019

**Tasks**
* [ ] 01.07.2019 - 05.07.2019: Entfernung library, Ergänung unserer bestehenden Klassen
* [ ] 01.07.2019 - 05.07.2019: Readme erstellen, welche beschreibt wie der Commandbus, Eventbus sowie die Middlewares genutzt werden können. Dies auch unhabhängig von CQRS / Eventsourcing.
* [ ] 01.07.2019 - 05.07.2019: Entscheid bezüglich Platzierung _Workshops zu DDD / EventBus / CQRS_ sobald getroffen umsetzen.


## Konzept Objekt-Zustands-Validierung ist geklärt
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
* Bereitstellung der notwendigen DDD Interfaces und Klassen.

**Verantwortlich / Durchführung bis**
* aluethi, 05.07.2019

**Tasks**
* [ ] 01.07.2019 - 05.07.2019: Bestehende Klassen prüfen und eränzen (aus Konsistenzgründen Intreface ValueObject)
* [ ] 01.07.2019 - 05.07.2019: Readme erstellen, welche beschreibt wie diese Klassen genutzt werden können sowohl EventSourced als auch ohne EventSourced
* [ ] 01.07.2019 - 05.07.2019: Entscheid bezüglich Platzierung _Workshops zu DDD / EventBus / CQRS_ sobald getroffen umsetzen.


## Sämtliche Value Object / Entities und (Repositories) implementiert
**Ziele**
* Sämtliche Value Objects & Entities sind angelegt.
* Entscheid ob ein oder zwei Repositories AggregateQuestion und (?)AggregateSolution / AggregateTest(?) ist abschliessend gefällt.

## Sämtliche Formulare sind implementiert
**Ziele**
* Es werden die bestehenden (legacy) Formulare in FormGUIs überführt. Wir erstellen die Formulare bewusst nicht neut.

**Verantwortlich / Durchführung bis**
* bheyser, 31.07.2019

**Tasks**
* [ ] 01.07.2019 - 31.07.2019: Value Objects anlegen
* [ ] 01.07.2019 - 31.07.2019: Entscheid Repositories fällen.
* [ ] 15.08.2019: Repositories umgesetzt.

## FormBuilder für Authoring-Umgebung
**Ziele**
* Formbuilder inkl. Navigationskonzept ist erstellt.

**Verantwortlich / Durchführung bis**
* mstuder, 19.07.2019

**Tasks**
* [ ] 01.07.2019 - 19.07.2019: Formbuilder für ist erstellt.

## Business-Logik
**Ziele**
* Business-Logik erstellt

**Verantwortlich / Durchführung bis**
* bheyser & mstuder, 15.08.2019

**Tasks**
* [ ] 15.07.2019 - 18.08.2019: Business-Logik erstellt.

## Services in Test und Fragenpool eingebaut
**Ziele**
* Service im Test und Fragenpool eingebaut.
**Verantwortlich / Durchführung bis**
* bheyser 31.07.2019

**Tasks**
* [ ] 08.07.2019 - 31.07.2019: Service im Test und Fragenpool eingebaut.

## ASQ Formbuilder in Test und Fragenpool eingebaut
**Ziele**
* Service im Test und Fragenpool eingebaut.
**Verantwortlich / Durchführung bis**
* bheyser / mstuder 15.08.2019

**Tasks**
* [ ] 01.08.2019 - 15.08.2019: Service im Test und Fragenpool eingebaut.

## Performance Tests
**Ziele**
* Es können Aussagen über die Performance der Authoring und der Testdurchführung getroffen werden.

**Verantwortlich / Durchführung bis**
* sschneider, Marburg 30.09.2019

**Tasks**
* [ ] 15.09.2019 - 30.09.2019: Performance Tests.

## Feature Implementierungen
**Ziele**
* Bauftragte Features implementieren.
	* Versionierung
	* Statistiken

**Verantwortlich / Durchführung bis**
* bheyser / mstuder / alüethi

**Tasks**
* [ ] 01.09.2019 - 15.10.2019: Feature Implementierung


