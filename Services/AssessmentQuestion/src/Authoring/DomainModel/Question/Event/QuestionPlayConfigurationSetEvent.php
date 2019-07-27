<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\QuestionPlayConfiguration;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\AbstractDomainEvent;

/**
 * Class QuestionCreatedEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class QuestionPlayConfigurationSetEvent extends AbstractDomainEvent {

	public const NAME = 'QuestionPlayConfigurationSetEvent';
	/**
	 * @var QuestionPlayConfiguration
	 */
	protected $play_configuration;

	public function __construct(DomainObjectId $id, int $creator_id, QuestionPlayConfiguration $play_configuration = null)
	{
		parent::__construct($id, $creator_id);
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

	public function getEventBody(): string {
		return json_encode($this->play_configuration);
	}

	/**
	 * @param string $json_data
	 */
	public function restoreEventBody(string $json_data) {
		$data = json_decode($json_data);
		$this->play_configuration = new QuestionPlayConfiguration
		(
			$data->presenter_class,
			$data->editor_class,
			$data->scoring_class,
			$data->working_time,
			call_user_func(array($data->editor_class, 'deserialize'), $data->editor_configuration),
			call_user_func(array($data->presenter_class, 'deserialize'), $data->presenter_configuration),
			call_user_func(array($data->scoring_class, 'deserialize'), $data->scoring_configuration));
	}
}