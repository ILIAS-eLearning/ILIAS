<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Event\AbstractDomainEvent;

/**
 * Class QuestionCreatedEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class QuestionCreatedEvent extends AbstractDomainEvent {

	public const NAME = 'QuestionCreatedEvent';

	public $title;

	public $description;

	public function __construct(AggregateId $id, int $creator_id, string $title = "", string $description = "")
	{
		parent::__construct($id, $creator_id);
		$this->title = $title;
		$this->description = $description;
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
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}


	public function restoreEventBody(string $json_data) {
		$data = json_decode($json_data);
		$this->title = $data->title;
		$this->description = $data->description;
	}
}