<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Event;



use ILIAS\AssessmentQuestion\CQRS\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\CQRS\Event\AbstractIlContainerDomainEvent;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOption;
use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\AnswerOptions;

/**
 * Class QuestionAnswerOptionsSetEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class QuestionAnswerOptionsSetEvent extends AbstractIlContainerDomainEvent {

	public const NAME = 'QuestionAnswerOptionsSetEvent';
	/**
	 * @var AnswerOptions
	 */
	protected $answer_options;


    /**
     * QuestionAnswerOptionsSetEvent constructor.
     *
     * @param DomainObjectId     $id
     * @param int                $container_obj_id
     * @param int                $initiating_user_id
     * @param AnswerOptions|null $options
     *
     * @throws \ilDateTimeException
     */
	public function __construct(DomainObjectId $id, int $container_obj_id, int $initiating_user_id, AnswerOptions $options = null)
	{
		parent::__construct($id, $container_obj_id, $initiating_user_id);
		$this->answer_options = $options;
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
	 * @return AnswerOptions
	 */
	public function getAnswerOptions(): AnswerOptions {
		return $this->answer_options;
	}

	public function getEventBody(): string {
		return json_encode($this->answer_options->getOptions());
	}

	/**
	 * @param string $json_data
	 */
	public function restoreEventBody(string $json_data) {
		$data = json_decode($json_data);
		$options = new AnswerOptions();

		foreach($data as $option) {
			$aoption = new AnswerOption($option->option_id);
			$aoption->deserialize($option);
			$options->addOption($aoption);
		}

		$this->answer_options = $options;
	}
}