<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Event;


use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Event\AbstractIlContainerDomainEvent;
use ILIAS\AssessmentQuestion\DomainModel\QuestionLegacyData;

/**
 * Class QuestionLegacyDataSetEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionLegacyDataSetEvent extends AbstractIlContainerDomainEvent {

	public const NAME = 'QuestionLegacyDataSetEvent';

	/**
	 * @var QuestionLegacyData
	 */
	protected $legacy_data;


    /**
     * QuestionLegacyDataSetEvent constructor.
     *
     * @param DomainObjectId          $question_uuid
     * @param int                     $container_obj_id
     * @param int                     $initiating_user_id
     * @param QuestionLegacyData|null $legacy_data
     *
     * @throws \ilDateTimeException
     */
	public function __construct
	(
		DomainObjectId $question_uuid,
		int $container_obj_id,
		int $initiating_user_id,
		QuestionLegacyData $legacy_data = null
	)
	{
		parent::__construct($question_uuid, $container_obj_id, $initiating_user_id);
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

    /**
     * @return string
     */
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