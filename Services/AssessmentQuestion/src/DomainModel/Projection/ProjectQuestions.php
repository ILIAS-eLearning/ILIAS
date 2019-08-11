<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Projection;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\AggregateRoot;
use ILIAS\AssessmentQuestion\CQRS\Event\Projection;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\AnswerOption;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\AnswerOptionImageAr;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\AnswerOptionTextAr;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\PublishedQuestionRepository;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\Projection\QuestionListItem;

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


        $answer_option_to_project = [];
        //TODO move this array to a arrayMap or DB - if plugins needs other storage possibilities

        /**
         * @var AnswerOption[] $arr_answer_option
         */
        $arr_answer_option_storage[] = new AnswerOptionImageAr();
        $arr_answer_option_storage[] = new AnswerOptionTextAr();


        //TODO clean up this foreach in foreach! And use an AnswerDTO Object
        foreach($projectee->getAnswerOptions()->getOptions() as $answer_option) {
            $values = $answer_option->getDisplayDefinition()->getValues();
            foreach($answer_option->getDisplayDefinition()->getFields() as $field_key => $field) {
                foreach ($arr_answer_option_storage as $answer_option_storage) {
                    //if ($answer_option->satisfy($field->getType()) === true ) {
                    $answer_option_storage->setData(
                            $projectee->getContainerObjId(),
                            $projectee->getAggregateId()->getId(),
                            $revision_id,
                            $values[$field_key]);
                    $answer_option_to_project[] = $answer_option_storage;
                    //}
                }
            }
        }

		$repository = new PublishedQuestionRepository();
        $repository->saveNewQuestionRevision(
            $projectee->getContainerObjId(),
            $projectee->getAggregateId()->getId(),
            $revision_id,
            $projectee->getData()->getTitle(),
            $projectee->getData()->getDescription(),
            $projectee->getData()->getQuestionText(),
            $answer_option_to_project
        );

	}
}