# Decisions

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




