<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type\AnswerType;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\AbstractDomainEvent;

/**
 * Class QuestionAnswerTypeSetEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class QuestionAnswerTypeSetEvent extends AbstractDomainEvent {

	public const NAME = 'QuestionAnswerTypeSetEvent';

	/**
	 * @var AnswerType
	 */
	protected $answer_type;

	public function __construct(DomainObjectId $question_uuid, int $initiating_user_id, AnswerType $answer_type = null)
	{
		parent::__construct($question_uuid, $initiating_user_id);
		$this->answer_type = $answer_type;
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
	 * @return AnswerType
	 */
	public function getAnswerType(): AnswerType {
		return $this->answer_type;
	}


	public function getEventBody(): string {
		return json_encode($this->answer_type);
	}

	/**
	 * @param string $json_data
	 */
	public function restoreEventBody(string $json_data) {
		$data = json_decode($json_data);
		$this->answer_type = new AnswerType($data->answer_type_id);
	}
}