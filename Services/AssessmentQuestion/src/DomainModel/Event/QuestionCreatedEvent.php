<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Event;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Event\AbstractDomainEvent;

/**
 * Class QuestionCreatedEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
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