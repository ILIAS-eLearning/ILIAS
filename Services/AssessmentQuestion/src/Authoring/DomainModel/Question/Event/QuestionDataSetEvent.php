<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use ILIAS\Data\Domain\Entity\AggregateId;
use ILIAS\Data\Domain\Event\AbstractDomainEvent;
use QuestionData;

/**
 * Class QuestionCreatedEvent
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class QuestionDataSetEvent extends AbstractDomainEvent {

	public const NAME = 'QuestionDataSetEvent';
	/**
	 * @var QuestionData
	 */
	public $data;

	public function __construct(AggregateId $id, int $creator_id, QuestionData $data)
	{
		parent::__construct($id, $creator_id);
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
	 * @return string
	 */
	public function getData(): string {
		return $this->data;
	}

	public function getEventBody(): string {
		return json_encode($this->data);
	}


	/**
	 * @param string $json_data
	 */
	public function restoreEventBody(string $json_data) {
		$data = json_decode($json_data);
		$this->data = new QuestionData($data->title,
		                               $data->description,
		                               $data->text);
	}
}