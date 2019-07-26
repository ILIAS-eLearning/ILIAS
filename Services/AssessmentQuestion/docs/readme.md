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

The Service offers:
* Links to the Authoring GUI
* A Delete-Question-Method
* A Method for Creating new Revisions of a Question. Use this Method if you like to have an immutable Questions Revision for the Play Service.

### Get the Service
#### For existing questions
```
$authoringService = $DIC->assessment()->service->authoring(
    $DIC->assessment->specification()->authoring(
        $myObjId, $myActorUserId, $myBacklink
    ),
    $DIC->assessment->consumer()->questionUuid('any-valid-uuid')
);
```
#### For not existing questions
```
$authoringService = $DIC->assessment()->service->authoring(
    $DIC->assessment->specification()->authoring(
        $myObjId, $myActorUserId, $myBacklink
    ),
    $DIC->assessment->consumer()->newQuestionUuid()
);
```
The Service needs following parameter:
* An ILIAS Object Id - This Id will be saved as Container Object Id. With this Id it will be checked if the ILIAS Container ask for the authoring of a question in his responsibility.
* An ActorId - ILIAS User Id - the Id is used for logging changes on the question.
* A Backlink - The Link is used to display a link back to the calling object.
* An uuid object of the question - This is the only ID for getting a question from outside. It's not possible and not allowed to get the question by the database Id. You can quite easy get this uuid object by the consumer factory of the assessment question service. Also if you like to create a new question you will give a pre generated uuid. 

### Create a question
The Assessment Question Service offers a creation form for questions. You can get the link to this form as follows:
```
$authoringService = $DIC->assessment()->service->authoring(
    $DIC->assessment->specification()->authoring(
        $myObjId, $myActorUserId, $myBacklink
    ),
    $DIC->assessment->consumer()->newQuestionUuid()
);

$creationLinkComponent = $authoringService->getCreationLink();
```
Please note that the ILIAS Ctrl-Flow will pass through your current GUI Class! And you are responsible for checking the permissions for this action!

### Edit a question
The Assessment Question Service offers an edit form for questions. You can get the link to this form as follows:
```
$authoringService->getEditLink()
```
Please note that the ILIAS Ctrl-Flow will pass through your current GUI Class!

### Delete a question
```
$authoringService->deleteQuestion()
```

### Additional Links
The Service offers the following additional methods for getting direct links to the authoring environment. With those links you are able to open directly a form of a specific tab of the authoring environment.
* getPreviewLink()
* getEditPageLink()
* getEditFeedbacksLink()
* getEditHintsLink()

### Publish New Revision
With revision of a question we would like to fulfill the requirement described under [https://docu.ilias.de/goto_docu_wiki_wpage_5309_1357.html|https://docu.ilias.de/goto_docu_wiki_wpage_5309_1357.html]

_Conceptual Comment: In this proposal we suggest to use a uuid for versioning and not an auto number. This is a conceptual change to the feature wiki entries [Question Versioning in Test Object](https://docu.ilias.de/goto_docu_wiki_wpage_5309_1357.html) and [Unique IDs for Test Questions](https://docu.ilias.de/goto_docu_wiki_wpage_5312_1357.html) which we have to discuss again at the ILIAS Jour Fixe. The ordering of the versions will be made by the versioning date. With this proposal it would be possible - it's not a must - that a question could be plattform independent identified by his uuid, which has never to be changed._

You can generate a new question revision as follows:
```
$authoringService->publishNewRevision($DIC->assessment->consumer()->newRevisionUuid('any-valid_question-uuid));
```

### Import Qti Item
If you like to import a Qti Item you can do that as follows:
```
$authoringService->importQtiItem($qtiItem);
```

### Change Question Container
By transfering a question to a new container use:
```
$authoringService->changeQuestionContainer($container_obj_id);
```

## Query Service
[/Services/AssessmentQuestion/PublicApi/QueryService.php](../PublicApi/QueryService.php)

The service offers query methods for getting questions as associative array of a question.

### Get the Service
```
$queryService = $DIC->assessment()->service->query();
```

### Get all questions of the current container
```
$queryService->GetQuestionsOfContainerAsAssocArray(
			$this->object->getId()
		);
```

## Play Service
[/Services/AssessmentQuestion/PublicApi/QueryService.php](../PublicApi/QueryService.php)

The Play Service you use for presenting a question to a user (student). And you use this service also for calculating the scoring for a user answer.

### Get the Service
```
$playService = $DIC->assessment()->service->play(
    $DIC->assessment->specification()->play(
        $myObjId, $myActorId
    ),
    $DIC->assessment->consumer()->questionUuid('any-valid-question_uuid')
    $DIC->assessment->consumer()->revisionUuid('any-valid-question_uuid','any-valid-revision_uuid')
);
```

### Get the question form and render it

Without a previously submited answer of the user:
```
$asqPlayService->GetQuestionPresentation(
    $DIC->assessment()->consumer()->newUserAnswerUuid()
    );
```
With a previously submited answer of a user:
```
$asqPlayService->GetQuestionPresentation(
    $DIC->assessment()->consumer()->userAnswerUuid(
        'any-valid-user-answer-uuid')
    );
```

### Get a standalone question for export
You can use this method if you like to display and play a question independent from the Assessment Question Service
//TODO
```

```

### Submit a user answer
A new user's answer to a question is saved as follows.
```
$asqPlayService->CreateUserAnswer(
    new UserAnswerSubmitContract(
        $DIC->assessment()->consumer()->UserAnswerUuid(
            new PostDataFromServerRequest($request)->get('user_answer_uuid)
        ),
        $DIC->assessment()->consumer()->questionUuid(
            new PostDataFromServerRequest($request)->get('question_uuid)
        ),
        $DIC->assessment()->consumer()->revisionUuid(
            new PostDataFromServerRequest($request)->get('revision_uuid)
        ),
        $user_id,
        json_encode(
            new PostDataFromServerRequest($request)->get('user_answer)
        )
    )
);
```
If you like to update a previously submited answer you can do that as follows:
```
$asqPlayService->UpdateUserAnswer(
    new UserAnswerSubmitContract(
            $DIC->assessment()->consumer()->UserAnswerUuid(
                        new PostDataFromServerRequest($request)->get('user_answer_uuid)
                    ),
                    $DIC->assessment()->consumer()->questionUuid(
                        new PostDataFromServerRequest($request)->get('question_uuid)
                    ),
                    $DIC->assessment()->consumer()->revisionUuid(
                        new PostDataFromServerRequest($request)->get('revision_uuid)
                    ),
                    $user_id,
                    json_encode(
                        new PostDataFromServerRequest($request)->get('user_answer)
                    )
                )
);
```

### Generic Feedback Output
```
$asqPlayService->getGenericFeedbackOutput(
        $DIC->assessment()->consumer()->UserAnswerUuid('any_valid_user_id)
    );
```

### Generic Specific Feedback Output
```
$asqPlayService->getGenericFeedbackOutput(
        $DIC->assessment()->consumer()->UserAnswerUuid('any_valid_user_id)
    );
```

### User Score
```
$asqPlayService->getUserScore(
        $DIC->assessment()->consumer()->UserAnswerUuid('any_valid_user_id)
    );
```
	

# Consumer

When integrating questions to any component for authoring purposes, a ctrlCalls to class.ilAsqQuestionAuthoringGui.php has to be implementet and as well as a forwarding in the consumer's `executeCommand()` method.

The consumer is also repsonsible fot checking the RBAC Permissions. 

Additionally the consuming component has an opportunity to provide any command link either as a button (like the well known check button) rendered within the question canvas or as an entry in an question actions menu (e.g. discard or postpone solution).

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

