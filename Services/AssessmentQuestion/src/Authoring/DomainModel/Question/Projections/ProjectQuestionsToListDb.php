<?php

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