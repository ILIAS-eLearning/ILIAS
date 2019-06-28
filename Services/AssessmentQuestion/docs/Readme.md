# Decisions

## Zeitplan (WIP)


CQRS / DDD
** Community-Workshops einberufen für je 90 Minuten für die Fragen, welche offen sind
*** EventBus
*** DDD Objekte / Interfaces / Abstract Classes
*** ...!
-> Freitags vor Weekly 09:15 - 10:45

* TA - 6.0 - Servicestruktur
	* Entscheid Ein Service / mehrere Services
	* ILIAS - TA - 6.0 - Schnittstelle ASQ-Auhtoring nach Aussen definiert
		* Public Service um Authoring-Umgebung zu initialisieren
		* Internal Service für die Verwendung der eigenenen ASQ-Authoring-Umgebung
	* ILIAS - TA - 6.0 - Schnittstelle ASQ-Consumer nach Aussen definiert / Public Service(s) für Consuming (alles was nicht Authoring ist)
		* Fragen anzeigen
		* Abspielen von Fragen
		* Statistiken
	* ILIAS - TA - 6.0 - die von den Services benötigten Command-Klassen sind implementiert
		*  Gliederbar!

* ILIAS - TA - 6.0 - Eventverteiler
Message Bus als Library nutzbar (Entscheidung gefällt ob Eigen-Implementierung oder Aufsetzen auf eine bestehende Library)
	* Externe Library?
	* Überhaupt eine Library / Eigenbau?
	* ILIAS Global / Komponenten Lokal


* ILIAS - TA - 6.0 - DDD Abstract AggregateRepository (Implmentiert und enschieden ob lokal oder global)
	* ILIAS Global / Komponenten Lokal?


* ILIAS - TA - 6.0 - Restliche Objekte (Interfaces / Abstract-Classes) für DDD-Konzept (restliche Interfaces und Abstract-Classes für DDD) (Implmentiert und enschieden ob lokal oder global)
** ILIAS Global / Komponenten Lokal?


* ILIAS - TA - 6.0 - Sämtliche Value Object / Entities und Repositories implementiert
	* Fortlaufender Entscheidungs-/Änderungsprozess ob Value Objet / Entity
	* Entscheid unterteilbar in AggregateQuestion und (?)AggregateSolution / AggregateTest (?)

* ILIAS - TA - 6.0 - Sämtliche Formulare implementiert
	* Anzahl xyz


* (ILIAS - TA - 6.0 - Business-Logik Question)


* (ILIAS - TA - 6.0 - Business-Logik Test)

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




