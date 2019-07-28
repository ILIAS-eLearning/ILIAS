<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Projection;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionData;
use ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB\QuestionListItem;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateRoot;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\Projection;

class ProjectQuestionsToListDb implements Projection {
	/**
	 * @param \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateRoot $projectee
	 *
	 * @return mixed
	 */
	public function project(AggregateRoot $projectee) {
		$projectee_key = $projectee->getRevisionId()->GetKey();
		$item = QuestionListItem::where(array('question_id' => $projectee_key))->first();

		if($item == null) {
			$item = new QuestionListItem();
			$item->setQuestionId($projectee_key);
		}

		/** @var QuestionData $data */
		$data = $projectee->getData();
		$item->setTitle($data->GetTitle());
		$item->setDescription($data->getDescription());
		$item->setQuestion($data->getQuestionText());
		$item->store();
	}
}