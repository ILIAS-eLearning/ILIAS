<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\AbstractDomainEvent;

/**
 * Class QuestionCreatedEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class QuestionCreatedEvent extends AbstractDomainEvent {

	public const NAME = 'QuestionCreatedEvent';

	/**
	 * @var  DomainObjectId
	 */
	protected $question_uuid;
	/**
	 * @var int;
	 */
	protected $container_obj_id;
	/**
	 * @var int
	 */
	protected $initiating_user_id;
	/**
	 * @var string
	 */
	protected $answer_type_id;

	public function __construct(DomainObjectId $question_uuid, int $initiating_user_id)
	{
		parent::__construct($question_uuid, $initiating_user_id);
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
	 * @return DomainObjectId
	 */
	public function getQuestionUuid(): DomainObjectId {
		return $this->question_uuid;
	}


	/**
	 * @return int
	 */
	public function getInitiatingUserId(): int {
		return $this->initiating_user_id;
	}


	public function restoreEventBody(string $json_data) {
		//no other properties
	}
}