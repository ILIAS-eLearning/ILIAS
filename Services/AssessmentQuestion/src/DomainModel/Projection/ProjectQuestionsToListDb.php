<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Projection;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\AggregateRoot;
use ILIAS\AssessmentQuestion\CQRS\Event\Projection;
use ILIAS\AssessmentQuestion\DomainModel\Question;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\AssessmentQuestion\Infrastructure\Persistence\ilDB\QuestionListItem;

/**
 * Class ProjectQuestionsToListDb
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ProjectQuestionsToListDb implements Projection {

    /**
     * @param AggregateRoot $projectee
     *
     * @return mixed|void
     */
	public function project(AggregateRoot $projectee) {
	    /** @var Question $projectee */
		$projectee_key = $projectee->getRevisionId()->GetKey();
		$item = QuestionListItem::where(array('question_id' => $projectee_key))->first();

		if($item == null) {
			$new = true;
			$item = new QuestionListItem();
			$item->setQuestionId($projectee_key);
		} else {
			$new = false;
		}

		/** @var QuestionData $data */
		$data = $projectee->getData();
		$item->setTitle($data->GetTitle());
		$item->setDescription($data->getDescription());
		$item->setQuestion($data->getQuestionText());

		if($new) {
			$item->create();
		} else {
			$item->save();
		}
	}
}