<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\AbstractDomainEvent;

class QuestionRevisionCreatedEvent extends AbstractDomainEvent {
	public const NAME = 'QuestionRevisionCreatedEvent';
	/**
	 * @var string
	 */
	public $revision_key;

	public function __construct(DomainObjectId $id, int $creator_id, string $revision_key = "")
	{
		parent::__construct($id, $creator_id);
		$this->revision_key = $revision_key;
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

	public function restoreEventBody(string $json_data) {
		$data = json_decode($json_data);
		$this->revision_key = $data->revision_key;
	}

	/**
	 * @return string
	 */
	public function getRevisionKey(): string {
		return $this->revision_key;
	}
}