# Roadmap

Priorities for the development of the Test & Assessment and the Test Question Pool depend on developer resources provided by a handfull of organizations. Thus no promises are made on timeframes.

## Prioritized
* Reducing the number of reported issues in Test & Assessment
* Defining a concise interface for questions.
* Separating the Test-Player from the Questions and the Question-Pool.
* Refactoring `ilTestParticipantList`: This Class has a lot of very expensive loops in it (https://mantis.ilias.de/view.php?id=33596), but we can not remove them right now as it is used in very different contexts for all kind of lists. Sometimes users don't have $active_ids (participant list in test with access limited to selected participants), sometimes they don't have $usr_ids (anonymous tests in public section).

## Others
* Fixing access to Learning Status when access to test results is limited (see: [Mantis 25064](https://mantis.ilias.de/view.php?id=25064&nbn=9))

## Open Warnings / Issues without Tickets
These are open findings from the PHP8 Project which couldn't be solved in the scope of the project itself. They are documented here for transparency.
Remarks on the individual items are marked with "@PHP8-CR"
### Test
* \ilTestPlayerAbstractGUI::autosaveCmd / This looks like another issue in the autosaving. Left for review/analysis by TechSquad
* \ilTestSkillEvaluation::determineReachedSkillPointsWithSolutionCompare / Incompatible type. Left for review/analysis by TechSquad
* \ilAssLacCompositeValidator::validateSubTree / Incompatible type. Left for review/analysis by TechSquad
### TestQuestionPool
* \ilObjQuestionPoolGUI::exportQuestionObject / Void result used. Left for review/analysis by TechSquad
* \assMatchingQuestionGUI::writeAnswerSpecificPostData / Incompatible type. Left for review/analysis by TechSquad
* \assMatchingQuestionGUI::populateAnswerSpecificFormPart / Incompatible type. Left for review/analysis by TechSquad
* \assMatchingQuestionImport::fromXML / Incompatible type. Left for review/analysis by TechSquad
* \ilAssQuestionSkillAssignmentsGUI::validateSolutionCompareExpression / Incompatible type. Left for review/analysis by TechSquad
* \ilTestSkillEvaluation::determineReachedSkillPointsWithSolutionCompare / Incompatible type. Left for review/analysis by TechSquad
* \ilAssLacCompositeValidator::validateSubTree / Incompatible type. Left for review/analysis by TechSquad
* \assOrderingHorizontalGUI::saveFeedback / Undefined method. Left for review/analysis by TechSquad
* \ilObjQuestionPoolGUI::questionObject / Undefined method. Left for review/analysis by TechSquad
* \ilObjTestGUI::executeCommand / Undefined method. Left for review/analysis by TechSquad
* \ilObjTestGUI::questionsObject / Undefined method. Left for review/analysis by TechSquad
* \ilTestEvaluationGUI::passDetails / Undefined method. Left for review/analysis by TechSquad
* \ilAssLacCompositeEvaluator::evaluateSubTree / Undefined method. Left for review/analysis by TechSquad


## A [RepoPattern](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/repository-pattern.md) Approach to reduce complexity
A lot of the current pain in the T&A is due to the ruthless mixing of Logic and GUI, making decisions very late based on 
variables transported a long way and - in general - having properties set and evaluated in a quite obscured way by several components.
There are some aspects I'd like to focus upon to ease the situation _without_ rebuilding the whole lot at once and disabling functionality
in the process.
In the following, I'll talk about questions mainly, but answers are implicitly included, since they work almost the same.

### current entanglements
Currently, loading a question means: lookup type, load specific question GUI, load base and specific values from DB, 
write back to question object, use in base- and specific gui. Goal of the process is to configure a question object 
that has all the required props and features.  This is due to mainly two concepts, that look outdated to me:

#### obese classes and hip-hop-loading
Specific Questions extend a baseclass, both in object and GUI. While this is fine in general, the T&A implementation is 
somewhat obscure with loading and modifying object properties. Both, architectural structure and unclear program-flows 
make it quite easy to miss out on a setting or certain property on the one hand, while on the other, it is somewhat painful to
alter a GUI. All eventualities have to be treated over the entirety of a "common question object". 
When saving questions/answers, it works the same way - the entire object is stored, additional features, answertexts and all.
Additionally, there are "satellite" properties like suggested solutions, that actually live completely in parallel structures
with rather a reference by id than a "real" intersection.

[assTextQuestionGUI with its question-obj](https://github.com/ILIAS-eLearning/ILIAS/blob/aa0f9afbfcf722ea802e30ffc1999dbf2230411d/Modules/TestQuestionPool/classes/class.assTextQuestionGUI.php#L44)
extends assQuestionGUI [working on it](https://github.com/ILIAS-eLearning/ILIAS/blob/a96ff8c06303fb523fe1a9f3b9abe1ea4b77c5d9/Modules/TestQuestionPool/classes/class.assQuestionGUI.php#L367-L374)
while [this is actually a constant](https://github.com/ILIAS-eLearning/ILIAS/blob/393027e6d5258f0a2d67ce87e5a4061b8385521f/Modules/TestQuestionPool/classes/class.assQuestion.php#L155)
in the baseclass of all questions.
Here is the [specific TextQuestion] (https://github.com/ILIAS-eLearning/ILIAS/blob/393027e6d5258f0a2d67ce87e5a4061b8385521f/Modules/TestQuestionPool/classes/class.assTextQuestion.php#L145)
loading [base-props](https://github.com/ILIAS-eLearning/ILIAS/blob/393027e6d5258f0a2d67ce87e5a4061b8385521f/Modules/TestQuestionPool/classes/class.assTextQuestion.php#L163).

#### setters
Assumingly for the purpose described above, there are a lot of (public!) direct property setters:
- [Separator property in assOrderingHorizontal](https://github.com/ILIAS-eLearning/ILIAS/blob/aa0f9afbfcf722ea802e30ffc1999dbf2230411d/Modules/TestQuestionPool/classes/class.assOrderingHorizontal.php#L589-L608)
- [some setter in assAnswerMatching](https://github.com/ILIAS-eLearning/ILIAS/blob/aa0f9afbfcf722ea802e30ffc1999dbf2230411d/Modules/TestQuestionPool/classes/class.assAnswerMatching.php#L176-L188)
- [same property, different setter](https://github.com/ILIAS-eLearning/ILIAS/blob/aa0f9afbfcf722ea802e30ffc1999dbf2230411d/Modules/TestQuestionPool/classes/class.assAnswerMatching.php#L190-L200)
- [the according getter](https://github.com/ILIAS-eLearning/ILIAS/blob/aa0f9afbfcf722ea802e30ffc1999dbf2230411d/Modules/TestQuestionPool/classes/class.assAnswerMatching.php#L133-L144)

From my point of view, this should be disentangled in a way that a question is used by
1. loading generic data
2. loading specific data 
3. handing over the well-configured object(s) to the guis - but only those, that are actually needed.
4. (modifying data and then repeating from 1)

### why/how repo-pattern will help 
In order to limit and isolate changes, we should use [repo-pattern](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/repository-pattern.md) to cluster settings into immutable objects.
Doing so
- will only add one additional layer around the properties, the getters might even remain
- will separate question-type specific data 
- will separate additional functionality, like suggested solution or hints
- will make construction/manipulation way more decisive
- will give us specific and distinct elements to talk about

A "question" will thus be a collection of otherwise isolated things, while those things should still be treated in a 
tendentiously isolated way.

Here is an example for a [repo on options of OrderingQuestions](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/Modules/TestQuestionPool/classes/questions/OrderingQuestion/assOrderingQuestionDatabaseRepository.php),
[used in the question class](https://github.com/ILIAS-eLearning/ILIAS/blob/1d2cf022cb62ec55069ffbe9afcc14736116f26b/Modules/TestQuestionPool/classes/class.assOrderingQuestion.php#L124-L132)
[to read and store Elements](https://github.com/ILIAS-eLearning/ILIAS/blob/1d2cf022cb62ec55069ffbe9afcc14736116f26b/Modules/TestQuestionPool/classes/class.assOrderingQuestion.php#L612-L630)


#### separation and clustering properties
There are only a few base_settings common to questions. Those are to _describe_ a question.
Then, there are answers, metadata, suggested solutions, etc. They are more or less connected to the question, 
but they are not necessary an integral part of it in a way that a question cannot exist without them.
This can and should be reflected by wrapping those "property clusters" into immutable objects with little or none logic,
which will also result in a number of repositories - one for each cluster.

construction and getters might look like this:
```php
public function __construct(assQuestionBaseSettings $base_settings) {
    $this->base_settings = $base_settings;
    //.....
}
public function getId() : int {
    return $this->base_settings->getId();
}

```

#### Dependency Injection, proper constructors
With smaller, immutable properties, construction is way more explicit than handing over "a question".
If the GUI, e.g., behaves differently based on a flag in properties, calculate before and split up GUI classes, 
or calculate early and only once.


#### Use factories to instantiate things
What we need to instantiate a question is not "the question" itself, but information about it, namely the type.
After reading those shared information, we can instantiate a specific question directly and hand over the common properties.
This is usually done by a factory, which will need not much more information than the question's id - just like before.

Consider something like this: 
```php
class factory {

    public function question(int $question_id) : assQuestion
    {
        $base_settings = $this->base_repo->select($question_id);
        $page_object = $this->page_repo->select($question_id);
        //....

        $classname = $this->getSpecificQuestionClassname($base_settings->getTypeTag());

        $question = (
            new $classname(
                $base_settings,
                $page_object
                //,....
            )
        );
        return $question;
    }
}

```     

### Consequences/Candidates
I'd consider these the most valuable and in the same way feasible steps to improve T&A-structures:
- separate "satellites", like suggested solutions in [PR #4587](https://github.com/ILIAS-eLearning/ILIAS/pull/4587)
- isolate base_settings (with type/repo/injection/getters, delete setters)
- clear out loading from GUIs, use a factory (might probably be done successively)
- get rid of "additionalTable" in favor of specific types and repos.
