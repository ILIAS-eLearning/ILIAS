<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Event;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\AbstractValueObject;
use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Event\AbstractDomainEvent;
use ILIAS\AssessmentQuestion\CQRS\Event\AbstractIlContainerDomainEvent;
use ILIAS\AssessmentQuestion\DomainModel\QuestionPlayConfiguration;

/**
 * Class QuestionPlayConfigurationSetEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionPlayConfigurationSetEvent extends AbstractIlContainerDomainEvent {

	public const NAME = 'QuestionPlayConfigurationSetEvent';
	/**
	 * @var QuestionPlayConfiguration
	 */
	protected $play_configuration;


    /**
     * QuestionPlayConfigurationSetEvent constructor.
     *
     * @param DomainObjectId                 $id
     * @param int                            $container_obj_id
     * @param int                            $initiating_user_id
     * @param QuestionPlayConfiguration|null $play_configuration
     *
     * @throws \ilDateTimeException
     */
	public function __construct(DomainObjectId $id, 
	                            int $container_obj_id, 
	                            int $initiating_user_id, 
	                            int $question_int_id, 
	                            QuestionPlayConfiguration $play_configuration = null)
	{
	    parent::__construct($id, $container_obj_id, $initiating_user_id, $question_int_id);
		$this->play_configuration = $play_configuration;
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
	 * @return QuestionPlayConfiguration
	 */
	public function getPlayConfiguration(): QuestionPlayConfiguration {
		return $this->play_configuration;
	}

    /**
     * @return string
     */
	public function getEventBody(): string {
		return json_encode($this->play_configuration);
	}

	/**
	 * @param string $json_data
	 */
	public function restoreEventBody(string $json_data) {
		$this->play_configuration = AbstractValueObject::deserialize($json_data);
	}
}