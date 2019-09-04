<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Event;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Event\AbstractIlContainerDomainEvent;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;

/**
 * Class QuestionDataSetEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionDataSetEvent extends AbstractIlContainerDomainEvent {

	public const NAME = 'QuestionDataSetEvent';
	/**
	 * @var QuestionData
	 */
	protected $data;


    /**
     * QuestionDataSetEvent constructor.
     *
     * @param DomainObjectId    $id
     * @param int               $creator_id
     * @param QuestionData|null $data
     *
     * @throws \ilDateTimeException
     */
	public function __construct(DomainObjectId $id, 
	                            int $container_obj_id, 
	                            int $initating_user_id, 
	                            int $object_id,
	                            QuestionData $data = null)
	{
		parent::__construct($id, $container_obj_id, $initating_user_id, $object_id);
		$this->data = $data;
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
	 * @return QuestionData
	 */
	public function getData(): QuestionData {
		return $this->data;
	}

    /**
     * @return string
     */
	public function getEventBody(): string {
		return json_encode($this->data);
	}

	/**
	 * @param string $json_data
	 */
	public function restoreEventBody(string $json_data) {
		$this->data = AbstractValueObject::deserialize($json_data);
	}
}