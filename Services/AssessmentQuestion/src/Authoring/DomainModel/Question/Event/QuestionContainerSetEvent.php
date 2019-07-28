<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionContainer;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\AbstractDomainEvent;

/**
 * Class QuestionAnswerTypeSetEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class QuestionContainerSetEvent extends AbstractDomainEvent {

	public const NAME = 'QuestionContainerSetEvent';

	/**
	 * @var QuestionContainer
	 */
	protected $question_container;

	public function __construct(DomainObjectId $question_uuid, int $initiating_user_id,QuestionContainer $question_container)
	{
		parent::__construct($question_uuid, $initiating_user_id);
		$this->question_container = $question_container;
	}

	/**
	 * @return string
	 *
	 * Add a Constant EVENT_NAME to your class: Name it: Classname
	 * e.g. 'QuestionCreatedEvent'
	 */
	public function getEventName(): string {
		return self::NAME;
	}

	/**
	 * @return QuestionContainer
	 */
	public function getQuestionContainer(): QuestionContainer {
		return $this->question_container;
	}


	public function getEventBody(): string {
		return json_encode($this->question_container);
	}

	/**
	 * @param string $json_data
	 */
	public function restoreEventBody(string $json_data) {
		$data = json_decode($json_data);
		$this->question_container = new QuestionContainer($data->container_obj_id);
	}
}