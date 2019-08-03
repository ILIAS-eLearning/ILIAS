<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Answer;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\AbstractDomainEvent;

/**
 * Class QuestionAnswerAddedEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class QuestionAnswerAddedEvent extends AbstractDomainEvent {

	public const NAME = 'QuestionAnswerAddedEvent';
	/**
	 * @var Answer
	 */
	private $answer;

	public function __construct(DomainObjectId $aggregate_id, int $initating_user_id, Answer $answer = null) {
		parent::__construct($aggregate_id, $initating_user_id);

		$this->answer = $answer;
	}


	/**
	 * @return Answer
	 */
	public function getAnswer(): Answer {
		return $this->answer;
	}

	/**
	 * @return string
	 *
	 * Add a Constant EVENT_NAME to your class: Name it: [aggregate].[event]
	 * e.g. 'question.created'
	 */
	public function getEventName(): string {
		return self::NAME;
	}


	public function getEventBody(): string {
		return json_encode($this->answer);
	}


	public function restoreEventBody(string $json_data) {
		$data = json_decode($json_data);
		$this->answer = new Answer($data->answerer_id,
		                           $data->question_id,
		                           $data->test_id,
		                           $data->value);
	}
}