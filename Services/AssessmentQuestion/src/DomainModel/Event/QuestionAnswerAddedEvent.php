<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Event;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Event\AbstractIlContainerDomainEvent;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;

/**
 * Class QuestionAnswerAddedEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionAnswerAddedEvent extends AbstractIlContainerDomainEvent {

	public const NAME = 'QuestionAnswerAddedEvent';
	/**
	 * @var Answer
	 */
	private $answer;


    /**
     * QuestionAnswerAddedEvent constructor.
     *
     * @param DomainObjectId $aggregate_id
     * @param int            $container_obj_id
     * @param int            $initating_user_id
     * @param Answer|null    $answer
     *
     * @throws \ilDateTimeException
     */
	public function __construct(DomainObjectId $aggregate_id, int $container_obj_id, int $initating_user_id, Answer $answer = null) {
		parent::__construct($aggregate_id, $container_obj_id, $initating_user_id);

		$this->answer = $answer;
	}

    /**
     * @return string
     *
     * Add a Constant EVENT_NAME to your class: Name it: [aggregate].[event]
     * e.g. 'question.created'
     */
    public function getEventName(): string {
        return self::NAME;
    }


	/**
	 * @return Answer
	 */
	public function getAnswer(): Answer {
		return $this->answer;
	}


	public function getEventBody(): string {
		return json_encode($this->answer);
	}


	public function restoreEventBody(string $json_data) {
		$data = json_decode($json_data);
		$this->answer = new Answer($data->answerer_id,
		                           $data->question_id,
		                           $data->test_id,
		                           $data->value);
	}
}