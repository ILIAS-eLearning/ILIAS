<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Event;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Event\AbstractIlContainerDomainEvent;
use ILIAS\AssessmentQuestion\DomainModel\QuestionData;
use ILIAS\Services\AssessmentQuestion\DomainModel\Feedback\Feedback;

/**
 * Class QuestionFeedbackSetEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionFeedbackSetEvent extends AbstractIlContainerDomainEvent {

	public const NAME = 'QuestionFeedbackSetEvent';
	/**
	 * @var Feedback
	 */
	protected $feedback;


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
	                            int $question_int_id,
                                Feedback $feedback = null)
	{
	    parent::__construct($id, $container_obj_id, $initating_user_id, $question_int_id);
		$this->feedback = $feedback;
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
	 * @return Feedback
	 */
	public function getFeedback(): Feedback {
		return $this->feedback;
	}

    /**
     * @return string
     */
	public function getEventBody(): string {
		return json_encode($this->feedback);
	}

	/**
	 * @param string $json_data
	 */
	public function restoreEventBody(string $json_data) {
		$this->feedback = Feedback::deserialize($json_data);
	}
}