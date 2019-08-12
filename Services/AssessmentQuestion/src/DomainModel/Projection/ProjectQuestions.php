<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Projection;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\AggregateRoot;
use ILIAS\AssessmentQuestion\CQRS\Event\Projection;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\PublishedQuestionRepository;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\QuestionListItemAr;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\AnswerOptionChoiceAr;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\ChoiceEditorDisplayDefinition;
use ILIAS\AssessmentQuestion\DomainModel\Scoring\MultipleChoiceScoringDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Component\Editor\MultipleChoiceEditorConfiguration;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\MultipleChoiceQuestionAr;

/**
 * Class ProjectQuestions
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ProjectQuestions implements Projection {

    /**
     * @param AggregateRoot $projectee
     *
     * @return mixed|void
     */
	public function project(AggregateRoot $projectee) {
	    /** @var Question $projectee */
		$revision_id = $projectee->getRevisionId()->GetKey();

        /**
         * @var AnswerOption[] $arr_answer_option
         */
		$projected_answer_options = [];

        //TODO clean up this foreach in foreach! And use an AnswerDTO Object
        foreach($projectee->getAnswerOptions()->getOptions() as $answer_option) {
            $values = $answer_option->rawValues();
            $projected_answer_option = new AnswerOptionChoiceAr();
            $projected_answer_option->setData(
                $projectee->getContainerObjId(), 
                $projectee->getAggregateId()->getId(), 
                $revision_id, 
                $values[ChoiceEditorDisplayDefinition::VAR_MCDD_TEXT], 
                $values[ChoiceEditorDisplayDefinition::VAR_MCDD_IMAGE], 
                $values[MultipleChoiceScoringDefinition::VAR_MCSD_SELECTED], 
                $values[MultipleChoiceScoringDefinition::VAR_MCSD_UNSELECTED]
            );
            $projected_answer_options[] = $projected_answer_option;
        }

        /** @var MultipleChoiceEditorConfiguration $mc_config */
        $mc_config = $projectee->getPlayConfiguration()->getEditorConfiguration();
        $mc_ar = new MultipleChoiceQuestionAr();
        $mc_ar->setData(
            $projectee->getContainerObjId(),
            $projectee->getAggregateId()->getId(),
            $revision_id, 
            $mc_config->isShuffleAnswers(), 
            $mc_config->getMaxAnswers(), 
            $mc_config->getThumbnailSize(), 
            $mc_config->isSingleLine());
        
		$repository = new PublishedQuestionRepository();
        $repository->saveNewQuestionRevision(
            $projectee->getContainerObjId(),
            $projectee->getAggregateId()->getId(),
            $revision_id,
            $projectee->getData(),
            $mc_ar,
            $projected_answer_options
        );
	}
}