# History

Assessment questions were once embeded in a large component called Test and Assessment. The Test Question Pool object and the Test object of ILIAS were not strictly separated and the assessment question integration was done within both components. This lead to a strong depency between the Test and the Test Question Pool object in the past. The codebase for the two modules was fully mixed up with the code for the questions.

Today, this failure in architecture got fixed by fully separating the components and by extracting a new service AssessmentQuestion.

Furthermore the database got decoupled since the supposed separation in two different table spaces within the former Test and Assessment component did not reflect a neccessary strict distinction. All information in the database about the assessment questions were migrated to the new table space of the AssessmentQuestion service.

# Introduction

This documentation describes the interfaces the AssessmentQuestion service comes with and how they are to be used by developers who want to integrate assessment questions to their components.

The AssessmentQuestion service is designed as a component that offers complex functionality for consumers. The way other components can integrate assessment questions keeps as most flexible as possible. Most of any business logic around assessment questions with a focus on a single question is covered in the Assessment Question Service.

The business logic for handling a group of questions is handled by the consumer. E.g. test passed at 80% of correctly answered questions.

# Service Interfaces

The AssessmentQuestion service has the following services that can be used by other developers that want to integrate assessment questions to their component.

## AsqAuthoringService
The service offers a complete authoring interface for the editing of questions.



Objects implementing `ilAsqQuestion` represents the question entity itself while objects implementing `ilAsqQuestionAuthoring` are about the authoring that can be integrated with the `executeCommand` control structure of ILIAS.

The interface `ilAsqPresentation` provides all functionality to output a question and its additional contents. Solutions get injected to keep the presentation as modular as possible.

The interface `ilAsqResultCalculator` provides all functionality of calculating right/wrong for a given solution as well as reached points. Having this functionality in an own object implementing this interface makes it possible for consumers to surround this kind calculators with an own proxy calculator implementing the same interface (e.g. for any score cutting options).

The handling of solutions is defined by the interface `ilAsqQuestionSolution` so the future implementation is fully getting rid of dealing with solution values stored in row array structures that were queried from the database.

When calculating results for an `ilAsqQuestionSolution` using the `ilAsqResultCalculator` an instance of `ilAsqQuestionResult` is returned that provides reached points as well as the state of right/wrong with corresponding getters.

The implementation for the offline presentation of assessment questions is currently separated from the regular presentation, because it acts fully different. Therefore an `ilAsqQuestionOfflinePresentationExporter` is available, that handles the neccessary javascripts as well as collecting the required question resources (media files, mobs, additional js/css). For collecting the question resources an  `ilAsqQuestionResourcesCollector` is provided by the `ilAsqFactory` that is able to collect the depencies for multiple questions at once

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
* Services/AssessmentQuestion/examples/class.exPageContentQuestions.php

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
    * Eine ilAsqQuestionSolution Objektinstanz repräsentiert eine Zeile in asq_solutions
    * Ein ilAsqQuestionSolutionValue Objekt repräsentiert eine Zeile in asq_solution_values
* Refactoring der bestehenden Fragenklassen durch Einbindung der Lösungsobjekte
    * Entfernen aller Parameter Übergaben betreffend Teilnehmer ID und Testdurchlauf
    * Umstellung aller betroffenen Methoden auf Verwendung eines Ersatzparameters vom Typ ilAsqQuestionSolution
* Abstraktion einer neuen Objektschicht zur Repräsentierung von Test Results
    * Eine Objekt Instanz vom Typ ilTestResult gewährt Zugriff auf ein Ergebnis eines Teilnehmers zu einer Frage
    * Gleichzeitig wird über ilTestResult eine zugehörige Lösungs ID verwaltet
    * Das Handling von Fragen im Test Player wird umgestellt
        * Für die Anzeige einer Frage mit Lösung wird über ilTestResult die zugehörige ilAsqQuestionSolution und bestückt die GUI Klasse der Frage damit
        * Rückwärts wird weiterhin die Fragen GUI die vom Teilnehmer übertragene Lösung aus den POST Parametern auslesen, dann aber eigenständig über ilAsqQuestionSolution abspeichern
        * Die dabei verwendete Lösungs ID wird dem Player zur Erstellung/Aktualisierung eines Ergebnis mittels ilTestResult zurückgereicht

# Open Questions

* Should ilTable(2) be changed to support the Assessment Question service?
    * ilTable(2) does not support list iterators
    * ilTable(2) does not support row objects

# Remaining Issues

* The current implemenation for an offline export of questions (question presentation that acts client side) and the regular presentation implementation for questions using the solution backend of the assessment question service needs to be merged in the future

# Future Requirements

The following known requirements that will probably come up with the next releases of ILIAS are not completely considered in the current concept of an Assessment Question Service. But extending the current concept accordingly will be possible to integrate these visions.

* Versioning for Assessment Questions
* Lifecycle for Assessment Questions
* Item Statistic for Assessment Questions
* Offline Rendering for Assessment Questions
