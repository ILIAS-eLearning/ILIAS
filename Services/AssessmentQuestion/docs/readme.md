# History

Assessment questions were once embeded in a large component called Test and Assessment. The Test Question Pool object and the Test object of ILIAS were not strictly separated and the assessment question integration was done within both components. This lead to a strong depency between the Test and the Test Question Pool object in the past. The codebase for the two modules was fully mixed up with the code for the questions.

Today, this failure in architecture got fixed by fully separating the components and by extracting a new service AssessmentQuestion.

Furthermore the database got decoupled since the supposed separation in two different table spaces within the former Test and Assessment component did not reflect a neccessary strict distinction. All information in the database about the assessment questions were migrated to the new table space of the AssessmentQuestion service.

# Introduction

This documentation describes the interfaces the AssessmentQuestion service comes with and how they are to be used by developers who want to integrate assessment questions to their components.

The AssessmentQuestion service is designed as a component that offers complex functionality for consumers. The way other components can integrate assessment questions keeps as most flexible as possible. The higher level business logic is handled by the consumer. E.g. the business Logig that a question can only be answered once or the business logic for handling a group of questions such as that a question can only be answered once. The lower level business logic around assessment questions with a focus on a single question is covered in the Assessment Question Service. E.g. the arrangement of points for answer options.

# Usage
The Assessment Question API provides the following Services:
*Authoring*
** Question Authoring
** Question List of a Container like ILIAS Test or ILIAS Question Pool
** Question Import

*Processing*
** Question Processing (presentation, save user answer, scoring)
** Question List for Processing

*Entity Id Builder*
** Build new AssessmentEntityId (uuid) or build an AssessmentEntityId Object from a uuid string.

When integrating questions to any component for authoring purposes, a ctrlCalls to class.ilAsqQuestionAuthoringGui.php has to be implementet and as well as a forwarding in the consumer's `executeCommand()` method.

The consuming component is also repsonsible fot checking the RBAC Permissions. 

Additionally the consuming component has an opportunity to provide any command link either as a button (like the well known check button) rendered within the question canvas or as an entry in an question actions menu (e.g. discard or postpone solution).

# Public Services

## Authoring Service
[/Services/AssessmentQuestion/PublicApi/Authoring/AuthoringService.php](../PublicApi/Authoring/AuthoringService.php)

Use this Service for every authoring use cases.

The Service offers the following sub services:
* Authoring Question Service
* Authoring Question List Service
* Authoring Question Import Service

### Authoring Question Service
With the authoring question service offers:
* Links to the Authoring GUI
* The possibility to get an instance of the AuthoringGUI object$
* A method for publish a new revision of a question
* A method for deleting a questions


#### Get the Service

Please note you have to provide a question uuid object yourself. You have this possibility, so that you are able to create and manage additional data for a question before(!) you use the question service. Therefore you as a consumer are responsible for the allocation of uuids. However, the service offers you the possibility to generate such a uuid object for you.
 
 Depending on this, you will get a new uuid object or, if you are in the editing process, the one you are currently using. 

```
//1. Get the general authoring service
$authoring_service = $DIC->assessment()->questionAuthoring($this->object->getId(), $DIC->user()->getId());

//2. Get the specific question authoring service
$authoring_service->question(authoring_service->currentOrNewQuestionId(), $my_back_link)->getAuthoringGUI());
```

The Service needs following parameter:
* An ILIAS Object Id - This Id will be saved as Container Object Id. With this Id it will be checked if the ILIAS Container ask for the authoring of a question in his responsibility.
* An ActorId - ILIAS User Id - the Id is used for logging changes on the question.
*  An uuid object of the question - This is the only ID for getting a question from outside. It's not possible and not allowed to get the question by the database Id. You can quite easy get this uuid object by the consumer factory of the assessment question service. Also if you like to create a new question you will give a pre generated uuid. 
* A Backlink - The link is used to display a link back to the calling object.

#### Create a question
The authoring question service offers a creation form for questions. You can get the link to this form as follows:
```
$authoring_service->question(authoring_service->currentOrNewQuestionId(), $my_back_link)->getCreationLink()
```
Please note that the ILIAS Ctrl-Flow will pass through your current GUI Class! And you are responsible for checking the permissions for this action!

#### Edit a question
The Assessment Question Service offers an edit form for questions. You can get the link to this form as follows:
```
$authoring_service->question(authoring_service->currentOrNewQuestionId(), $my_back_link)->getEditLink()
```
Please note that the ILIAS Ctrl-Flow will pass through your current GUI Class!

#### Delete a question
```
$authoring_service->question($DIC->assessment()->entityIdBuilder()->fromString($question_uuid), $my_back_link)
```

#### Additional Links
The Service offers the following additional methods for getting direct links to the authoring environment. With those links you are able to open directly a form of a specific tab of the authoring environment.
* getPreviewLink()
* getEditPageLink()
* getEditFeedbacksLink()
* getEditHintsLink()

#### Publish New Revision
With revision of a question we would like to fulfill the already scheduled requirement for ILIAS 6.0 described under [Question Versioning in Test Object|https://docu.ilias.de/goto_docu_wiki_wpage_5309_1357.html]

These feature requests adress a high value functionality the community has been waiting for a long time. When we consider the basic aspects like question revisioning now with the ongoing refactoring we can save a lot of additioal effort (even when the features should be postponed to ILIAS 6.1).

_Conceptual Comment: In this proposal we suggest to use a uuid for versioning and not an auto number. This is a conceptual change to the feature wiki entries [Question Versioning in Test Object](https://docu.ilias.de/goto_docu_wiki_wpage_5309_1357.html) and [Unique IDs for Test Questions](https://docu.ilias.de/goto_docu_wiki_wpage_5312_1357.html) which we have to discuss again at the ILIAS Jour Fixe. The ordering of the versions will be made by the versioning date. With this proposal it would be possible - it's not a must - that a question could be plattform independent identified by his uuid, which has never to be changed._

You can generate a new question revision as follows:
```
$authoring_service->question(n$DIC->assessment()->entityIdBuilder()->fromString('my-valid-question-uuid'), $my_back_link)->publishNewRevision());
```

#### Change Question Container
By transfering a question to a new container use:
```
$authoring_service->question($DIC->assessment()->entityIdBuilder()->fromString($question_uuid), $my_back_link)->changeQuestionContainer($container_obj_id);
```

### Question Import Service


#### Get the Service
```
1. Get the general authoring service
$authoring_service = $DIC->assessment()->questionAuthoring($this->object->getId(), $DIC->user()->getId());

2. Get the question import service
$question_import = $authoring_service->questionImport();
```

#### Import Qti Item
If you like to import a Qti Item you can do that as follows:
```
$question_import->importQtiItem($qtiItem);
```

### Question List Service
[/Services/AssessmentQuestion/PublicApi/Authoring/QuestionList.php](../PublicApi/Authoring/QuestionList.php)

The service offers query methods for getting questions for authoring purpose as associative array of a question.

#### Get the Service
```
1. Get the general authoring service
$authoring_service = $DIC->assessment()->questionAuthoring($this->object->getId(), $DIC->user()->getId());

2. Get the specific question authoring service
$question_list = $authoring_service->questionList();
```

#### Get all questions of the current container
As Assoc Array
```
$question_list->getQuestionsOfContainerAsAssocArray();
```

As List of DTO's 
```
$question_list->getQuestionsOfContainerAsDtoList();
```

## Processing Service
[/Services/AssessmentQuestion/PublicApi/Processing/ProcessingService.php](../PublicApi/Processing/ProcessingService.php)

The Processing Service you use for presenting a question to a user (student). To store his answer and you use this service also for calculating the scoring for a user answer.

### Processing Question Service
#### Get the Service
```
//1. Get the general Processing Service
$question_processing_service = $DIC->assessment()->questionProcessing($DIC->user()->getId());

//2. Get the specific question processing service
////Without a previously submited answer of the user:
$question_processing_service = $processing_service->question($DIC->assessment()->entityIdBuilder()->fromString('any-valid-revision-uuid'),$DIC->assessment()->entityIdBuilder()->new()):

////With a previously submited answer of the user:
$question_processing_service = $processing_service->question($DIC->assessment()->entityIdBuilder()->fromString($revision_uuid),$DIC->assessment()->entityIdBuilder()->fromString($user_answer_uuid));
```

#### Get the question form and render it
```
$question_component = $processing_service->getQuestionPresentation();
$DIC->ui()->renderer()->render($question_component);
```


#### Submit a user answer
A new user's answer to a question is saved with _$asqPlayService->storeUserAnswer([...])_. StoreUserAnswer will give you no direct feedback. If there are any errors exceptions will be thrown.
```

$question_processing_service->CreateUserAnswer(
    new UserAnswerSubmit(json_encode($DIC->http()->request('user_answer')));
);
```

#### Generic Feedback Output
```
$question_processing_service->getGenericFeedbackOutput();
```

#### Generic Specific Feedback Output
```
$question_processing_service->getGenericFeedbackOutput();
```

### User Score
_$asqPlayService->getUserScore(UserAnswerUuid)_ returns the score in form of an object ([./Services/AssessmentQuestion/PublicApi/Processing/ScoredUserAnswerDto.php](../PublicApi/Processing/ScoredUserAnswerDto.php) for the given answer from the point of view of the question service. 

The consumer specific settings like _For Each Questions Negative Points are set to '0 Points'_ are not considered here. This is a matter for the consumer. 

For using this Service a valid already deposited AnswerUUID has to be provided.

```
$question_processing_service->getUserScore();
```

### Get a standalone question for export
You can use this method if you like to display and play a question independent from the Assessment Question Service.

A collection of resources the question requires can be fetched by using a collector that is to be passed as parameter for the offline export.

```
//TODO
$questionResourcesCollector = $DIC->assessment()->questionRessourcesCollector();

//TODO
$uestionOfflinePresentationComponent = ...-getStandaloneQuestionExportPresentation(
	$this->questionResourcesCollector, $image_path, $a_mode, $a_no_interaction
);

$questionResourcesCollector->getMobs();
$questionResourcesCollector->getMediaFiles();
$questionResourcesCollector->getJsFiles();
```
	
# Export / Import

The assessment question service has two classes for the export and import. For the export `ilAssessmentQuestionExporter` extends `ilXmlExporter` and for the import `ilAssessmentQuestionImporter` extends `ilXmlImporter`. With these classes the assessment questions docks to the common export/import structure of ILIAS.

Consumers of the assessment question service can declare questions as a tail depency within their `il<Module>Exporter` class. The export architecture of ILIAS will address the assessment question service and imports the questions. Consumers also need to finally process question id mappings within their `il<Module>Importer` class.

When consumers want to export the assessment questions as a single QTI xml file, they can simply use `ilAsqQuestion::toQTIXml()` interface methods. It is to be used for each question that needs to get exported. An overall QTI xml file can be created by simply concatinating the xml pieces got from the question instance.

For importing assessment questions from any single QTI xml file, the QTI service is to be used to retieve a list of `QTIitem` instances. These items can be provided to an empty `ilAsqQuestion` instance to save the question to the database.

# Example Consumers (Test/Pool/LearningModule)

[Services/AssessmentQuestion/examples/class.exObjQuestionPoolGUI.php](../examples/class.exObjQuestionPoolGUI.php)

[Services/AssessmentQuestion/examples/class.exQuestionsTableGUI.php](../examples/class.exQuestionsTableGUI.php)

[Services/AssessmentQuestion/examples/class.exTestPlayerGUI.php](../examples/class.exTestPlayerGUI.php)

[Services/AssessmentQuestion/examples/class.exPageContentQuestions.php](../examples/class.exPageContentQuestions.php)

[Services/AssessmentQuestion/examples/class.exQuestionPoolExporter.php](../examples/class.exQuestionPoolExporter.php)

[Services/AssessmentQuestion/examples/class.exQuestionPoolImporter.php](../examples/class.exQuestionPoolImporter.php)

