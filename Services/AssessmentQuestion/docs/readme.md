# History

Assessment questions were once embeded in a large component called Test and Assessment. The Test Question Pool object and the Test object of ILIAS were not strictly separated and the assessment question integration was done within both components. This lead to a strong depency between the Test and the Test Question Pool object in the past. The codebase for the two modules was fully mixed up with the code for the questions.

Today, this failure in architecture got fixed by fully separating the components and by extracting a new service AssessmentQuestion.

Furthermore the database got decoupled since the supposed separation in two different table spaces within the former Test and Assessment component did not reflect a neccessary strict distinction. All information in the database about the assessment questions were migrated to the new table space of the AssessmentQuestion service.

# Introduction

This documentation describes the interfaces the AssessmentQuestion service comes with and how they are to be used by developers who want to integrate assessment questions to their components.

The AssessmentQuestion service is designed as a component that offers complex functionality for consumers. The way other components can integrate assessment questions keeps as most flexible as possible. The higher level business logic is handled by the consumer. E.g. the business Logig that a question can only be answered once or the business logic for handling a group of questions such as that a question can only be answered once. The lower level business logic around assessment questions with a focus on a single question is covered in the Assessment Question Service. E.g. the arrangement of points for answer options.


# Public Services

The AssessmentQuestion service has the following services that can be used by other developers that want to integrate assessment questions to their component.

## Authoring Service
[/Services/AssessmentQuestion/PublicApi/AuthoringService.php](../PublicApi/AuthoringService.php)
```
$authoringService = $DIC->assessment()->service->authoring(
    $DIC->assessment->specification()->authoring(
        $myObjId, $myActorId, $myBacklink
    ),
    $DIC->assessment->consumer()->questionUuid('any-valid-uuid')
);
```
The Service offers:
* Links to the Authoring GUI
* A Delete-Question-Method
* A Method for Creating new Revisions of a Question. Use this Method if you like to have an immutable Questions Revision for the Play Service.

## Query Service
[/Services/AssessmentQuestion/PublicApi/QueryService.php](../PublicApi/QueryService.php)
```
$queryService = $DIC->assessment()->service->query();
```
The service offers a query method for getting questions as associative of a question.


## Play Service
```
$playService = $DIC->assessment()->service->play(
    $DIC->assessment->specification()->play(
        $myObjId, $myActorId
    ),
    $DIC->assessment->consumer()->questionUuid('any-valid-uuid')
);
```
This Service Offers 
* Presentation Components for rendering at the consumer side. 
* The possibility to save and score user answers.



# Consumer

When integrating questions to any component for authoring purposes, a ctrlCalls to class.ilAsqQuestionAuthoringGui.php has to be implementet and as well as a forwarding in the consumer's `executeCommand()` method.

The consumer is also repsonsible fot checking the RBAC Permissions. 

Additionally the consuming component has an opportunity to provide any command link either as a button (like the well known check button) rendered within the question canvas or as an entry in an question actions menu (e.g. discard or postpone solution).

# Export / Import

The assessment question service has two classes for the export and import. For the export `ilAssessmentQuestionExporter` extends `ilXmlExporter` and for the import `ilAssessmentQuestionImporter` extends `ilXmlImporter`. With these classes the assessment questions docks to the common export/import structure of ILIAS.

Consumers of the assessment question service can declare questions as a tail depency within their `il<Module>Exporter` class. The export architecture of ILIAS will address the assessment question service and imports the questions. Consumers also need to finally process question id mappings within their `il<Module>Importer` class.

When consumers want to export the assessment questions as a single QTI xml file, they can simply use `ilAsqQuestion::toQTIXml()` interface methods. It is to be used for each question that needs to get exported. An overall QTI xml file can be created by simply concatinating the xml pieces got from the question instance.

For importing assessment questions from any single QTI xml file, the QTI service is to be used to retieve a list of `QTIitem` instances. These items can be provided to an empty `ilAsqQuestion` instance to save the question to the database.

# Example Consumers

[/Services/AssessmentQuestion/examples/class.exObjQuestionPoolGUI.php](../examples/class.exObjQuestionPoolGUI.php)
[/Services/AssessmentQuestion/examples/class.exQuestionsTableGUI.php](../examples/class.exQuestionsTableGUI.php)
[/Services/AssessmentQuestion/examples/class.exTestPlayerGUI.php](../examples/class.exTestPlayerGUI.php)
[/Services/AssessmentQuestion/examples/class.exPageContentQuestions.php](../examples/class.exPageContentQuestions.php)
[/Services/AssessmentQuestion/examples/class.exQuestionPoolExporter.php](../examples/class.exQuestionPoolExporter.php)
[/Services/AssessmentQuestion/examples/class.exQuestionPoolImporter.php](../examples/class.exQuestionPoolImporter.php)
