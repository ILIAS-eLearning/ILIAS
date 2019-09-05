<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Event;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Event\AbstractIlContainerDomainEvent;

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
class QuestionCreatedEvent extends AbstractIlContainerDomainEvent {

	public const NAME = 'QuestionCreatedEvent';

	/**
	 * @param string $question_uuid
	 * @param int $container_obj_id
	 * @param int $initiating_user_id
	 * @param int $object_id
	 */
	public function __construct(DomainObjectId $question_uuid,
	                            int $container_obj_id,
	                            int $initiating_user_id,
	                            int $question_int_id) 
	{
	    parent::__construct($question_uuid, $container_obj_id, $initiating_user_id, $question_int_id);
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
	 * {@inheritDoc}
	 * @see \ILIAS\AssessmentQuestion\CQRS\Event\AbstractIlContainerDomainEvent::restoreEventBody()
	 */
    public function restoreEventBody(string $json_data)
    {
        //no additional fields
    }

}