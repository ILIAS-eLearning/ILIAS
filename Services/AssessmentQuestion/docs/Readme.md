# Decisions

## Zeitplan (WIP)


CQRS / DDD

* ILIAS - TA - 6.0 - Service-Gruppe Authring-Service definiert
	* Public Service um Authoring-Umgebung zu initialisieren
	* Internal Service für die Verwendung der eigenenen ASQ-Authoring-Umgebung


* ILIAS - TA - 6.0 - Service-Gruppe Consuming-Service definiert
	* Public Service(s) für Consuming (alles was nicht Authoring ist)
		* Fragen anzeigen
		* Abspielen von Fragen
		* Statistiken

* ILIAS - TA - 6.0 - die von den Services benötigten Command-Klassen sind implementiert
	* Gliederbar!

* ILIAS - TA - 6.0 - Message Bus als Library nutzbar (Entscheidung gefällt ob Eigen-Implementierung oder Aufsetzen auf eine bestehende Library)
	* Externe Library?
	* Überhaupt eine Library / Eigenbau?
	* ILIAS Global / Komponenten Lokal


* ILIAS - TA - 6.0 - DDD Abstract AggregateRepository (Implmentiert und enschieden ob lokal oder global)
	* ILIAS Global / Komponenten Lokal?


* ILIAS - TA - 6.0 - Restliche Objekte (Interfaces / Abstract-Classes) für DDD-Konzept (restliche Interfaces und Abstract-Classes für DDD) (Implmentiert und enschieden ob lokal oder global)
** ILIAS Global / Komponenten Lokal?


* ILIAS - TA - 6.0 - Sämtliche Value Object / Entities und Repositories implementiert
	* Fortlaufender Entscheidungs-/Änderungsprozess ob Value Objet / Entity
	* Unterteilbar in AggregateQuestion und AggregateSolution

* ILIAS - TA - 6.0 - Sämtliche Formulare implementiert


* (ILIAS - TA - 6.0 - Business-Logik AggregateQuestion)


* (ILIAS - TA - 6.0 - Business-Logik AggregateSolution)


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




