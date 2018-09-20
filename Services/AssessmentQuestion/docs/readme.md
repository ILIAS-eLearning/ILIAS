# Introduction

The implementation of assessment questions needs to be decoupled from the large component Test and Assessment. The codebase is to be extracted to its own service AssessmentQuestion.

This concept describes the interfaces that will be introduced and how they are to be used by developers who want to integrate assessment questions to their components. Furthermore it describes how the database gets decoupled since the supposed separation in two different table spaces within the current state does not reflect a neccessary strict distinction.

The AssessmentQuestion service is designed as a component that offers complex functionality but it keeps stupid for itself. The way other components can integrate assessment questions keeps as most flixible as possible. This strategy makes it possible to define most of the assessment logic within a consumer.

This concept keeps focus on the decoupling, not on fullfilling future requirements.

# Service Interfaces

The AssessmentQuestion service will come with the following interfaces that can be used by other developers that want to integrate assessment questions to their component. The current obect structure consisting of a GUI class and an Object class gets overhauled.

Objects implementing `ilAsqQuestion` represents what was also formaly known as the Object class while objects implementing `ilAsqQuestionAuthoring` are about the `executeCommand` structure that was formly knwon as the GUI classes. All other aspects have been extracted from these interfaces.

The interface `ilAsqPresentation` provides all functionality to output a question and its additional contents. These parts are fully extracted from the former GUI class. Solutions now get injected to keep the presentation as modular as possible.

The interface `ilAsqResultCalculator` provides all functionality of calculating right/wrong for a given solution as well as reached points. Having this functionality in an own object following implementing this interface makes it possible for consumers to surround this kind calculators with an own proxy calculator implementing the same interface (e.g.for any score cutting options).

The handling of solutions is defined by the interface `ilAsqSolution` so the future implementation is fully getting rid of dealing with solution values stored in row array structures that were queried from the database.

# Consumer Interface

Consumers need to implement the interface `ilAsqQuestionNavigationAware` with any object that they need to inject to any question type's presentation object. This way the question presentation gets the neccessary link used by the question presentation for any self round trip (e.g fileupload, imagemap select).

Additionally the consuming component has an opportunity to provide any command link either as a button (like the well known check button) rendered within the question canvas or as an entry in an question actions menu (e.g. discard or postpone solution).

# Service Factory

For any use case other developers need to handle within their component when integrating the assessment questions, the `ilAsqFactoy` provides neccessary factory methods. Since the different interfaces of the assessment questions need to be used together, this factory is to be used in the consuming components multiple times.

The factory is integrated into the global DIC. Use `$DIC->question()` to get an instance of ilAsqFactory.

# Export / Import

The assessment question service has two classes for the export and import. For the export `ilAssessmentQuestionExporter` extends `ilXmlExporter` and for the import `ilAssessmentQuestionImporter` extends `ilXmlImporter`. With these classes the assessment questions docks to the common export/import structure of ILIAS.

Consumers of the assessment question service can declare questions as a tail depency within their `il<Module>Exporter` class. The export architecture of ILIAS will address the assessment question service and imports the questions. Consumers also need to finally process question id mappings within their `il<Module>Importer` class.

When consumers want to export the assessment questions as a single QTI xml file, they can simply use `ilAsqQuestion::toQTIXml()` interface methods. It is to be used for each question that needs to get exported. An overall QTI xml file can be created by simply concatinating the xml pieces got from the question instance.

For importing assessment questions from any single QTI xml file, the QTI service is to be used to retieve a list of `QTIitem` instances. These items can be provided to an empty `ilAsqQuestion` instance to save the question to the database.

# Service Class

There are three requirements up to now that cannot be handled by any concrete and question type specific implementation of any assessment question interfaces. Therefore the `ilAsqService` class provides a container for methods handling this requirements. An instance of the service class can be requested using `$DIC->question()->service()`.

* When integrating questions to any component for authoring purposes, a forwarding needs to be implemented in the component's `executeCommand()` method. To check wether any concrete question type authoring implementation is indeed the current next class in the control flow, a suitable method is provided in the `ilAsqService` class.
* Due to the use of the QTI service during imports of QTI xmls a determination of the question type based on the QTI item is required, because an empty object instance needs to be requested. Currently the question type is provided by the QTI item, but this may get changed in the future. `ilAsqService` provides a suitable method for this purpose.
* When question managing components need to copy questions within the same consumer instance a method is required to check for existing question titles. `ilAsqService` provides this message.

# Usage of the Service

## Authoring Consume

Usage examples can be viewed within the file:  
* Services/AssessmentQuestion/examples/class.exObjQuestionPoolGUI.php
* Services/AssessmentQuestion/examples/class.exQuestionsTableGUI.php
* Services/AssessmentQuestion/examples/class.exQuestionPoolExporter.php

## Presentation Consume

Usage examples can be viewed within the file:  
* Services/AssessmentQuestion/examples/class.exTestPlayerGUI.php

## Offline Export Consume

Usage examples can be viewed within the file:  
* Services/AssessmentQuestion/examples/class.exOfflinePresentationQuestionExporter.php

# Decoupled Database

Die eigentliche, notwendige Entkopplung findet in diesem Schritten statt: Verletzt der Zugriff auf Daten die geplanten Zuständigkeiten so werden diese bereinigt. Konsumenten sollen lediglich IDs von Fragen und Lösungen kennen und diese in eigener Zuständigkeit Ergebnissen zuordnen. Fragen und Lösungen benötigen keine Informationen der Konsumenten mehr.

* Portierung der Lösungsdatenbank des Test-Objekts in den Fragenservice
    * Umbenennung der tst_solutions Tabelle in asq_solution_values
        * Diese Tabelle speichert weiterhin nach dem Key/Value Prinzip die Lösungsinformationen
        * Zu einer Lösung gehören beliebig viele Datensätze
    * Ergänzung einer Tabelle asq_solutions
        * Diese Tabelle verwaltet je Teilnehmerlösung eine neue Lösungs-ID
        * Die Lösungs-ID wird in asq_solution_values verwendet
        * Konsumenten können die Lösungs-ID in Ergebnisdaten verwenden
    * Eigentliche Entkopplung durch Umstrukturieren der IDs und Referenzen
        * Die Tabelle tst_test_result wird mit einer neuen Spalte für die Lösungs-ID aktualisiert
* Abstraktion einer neuen Objektschicht zur Repräsentierung von eingereichten Lösungen
    * Eine ilAsqSolution Objektinstanz repräsentiert eine Zeile in asq_solutions
    * Ein ilAsqSolutionValue Objekt repräsentiert eine Zeile in asq_solution_values
* Refactoring der bestehenden Fragenklassen durch Einbindung der Lösungsobjekte
    * Entfernen aller Parameter Übergaben betreffend Teilnehmer ID und Testdurchlauf
    * Umstellung aller betroffenen Methoden auf Verwendung eines Ersatzparameters vom Typ ilAsqSolution
* Abstraktion einer neuen Objektschicht zur Repräsentierung von Test Results
    * Eine Objekt Instanz vom Typ ilTestResult gewährt Zugriff auf ein Ergebnis eines Teilnehmers zu einer Frage
    * Gleichzeitig wird über ilTestResult eine zugehörige Lösungs ID verwaltet
    * Das Handling von Fragen im Test Player wird umgestellt
        * Für die Anzeige einer Frage mit Lösung wird über ilTestResult die zugehörige ilAsqSolution und bestückt die GUI Klasse der Frage damit
        * Rückwärts wird weiterhin die Fragen GUI die vom Teilnehmer übertragene Lösung aus den POST Parametern auslesen, dann aber eigenständig über ilAsqSolution abspeichern
        * Die dabei verwendete Lösungs ID wird dem Player zur Erstellung/Aktualisierung eines Ergebnis mittels ilTestResult zurückgereicht

# Open Questions

* Should ilTable(2) be changed to support the Assessment Question Service?
    * ilTable(2) does not support list iterators
    * ilTable(2) does not support row objects

# Future Requirements

The following known requirements that will probably come up with the next releases of ILIAS are not completely considered in the current concept of an Assessment Question Service. But extending the current concept accordingly will be possible to integrate these visions.

* Versioning for Assessment Questions
* Lifecycle for Assessment Questions
* Item Statistic for Assessment Questions
* Offline Rendering for Assessment Questions