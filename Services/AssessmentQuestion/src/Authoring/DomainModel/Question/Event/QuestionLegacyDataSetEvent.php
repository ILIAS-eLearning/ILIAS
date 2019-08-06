<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionLegacyData;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\AbstractDomainEvent;

/**
 * Class QuestionAnswerTypeSetEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class QuestionLegacyDataSetEvent extends AbstractDomainEvent {

	public const NAME = 'QuestionLegacyDataSetEvent';

	/**
	 * @var QuestionLegacyData
	 */
	protected $legacy_data;

	public function __construct
	(
		DomainObjectId $question_uuid,
		int $initiating_user_id,
		QuestionLegacyData $legacy_data = null
	)
	{
		parent::__construct($question_uuid, $initiating_user_id);
		$this->legacy_data = $legacy_data;
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
	 * @return QuestionLegacyData
	 */
	public function getLegacyData(): QuestionLegacyData {
		return $this->legacy_data;
	}


	public function getEventBody(): string {
		return json_encode($this->legacy_data);
	}

	/**
	 * @param string $json_data
	 */
	public function restoreEventBody(string $json_data) {
		$this->legacy_data = AbstractValueObject::deserialize($json_data);
	}
}