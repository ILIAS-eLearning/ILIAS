# Roadmap

Priorities for the development of the Test & Assessment and the Test Question Pool depend on developer resources provided by a handfull of organizations. Thus no promises are made on timeframes.

## Prioritized
* Reducing the number of reported issues in Test & Assessment
* Defining a concise interface for questions.
* Separating the Test-Player from the Questions and the Question-Pool.

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