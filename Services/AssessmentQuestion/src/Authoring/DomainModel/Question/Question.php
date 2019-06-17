<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared\QuestionId;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event\QuestionCreatedEvent;
use ILIAS\Data\Domain\Entity\AggregateRevision;
use ILIAS\Data\Domain\Entity\AggregateRoot;

/**
 * Class Question
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question
 * @author  Martin Studer <ms@studer-raimann.ch>
 */
class Question extends AggregateRoot {

	/**
	 * @var QuestionId
	 */
	private $id;
	/**
	 * @var string
	 */
	private $title;
	/**
	 * @var string
	 */
	private $description;
	/**
	 * @var int
	 */
	private $creator;
	/**
	 * @var bool
	 */
	private $online = false;
	/**
	 * @var
	 */
	private $possible_answers;

	public function __construct(QuestionId $id)
	{
		parent::__construct();
		$this->id = $id;
	}


	/**
	 * @param $title
	 * @param $description
	 */
	public static function createFrom(string $title, string $description, int $creator) {
		$question = new Question(new QuestionId());
		$question->recordApplyAndPublishThat(new QuestionCreatedEvent($question->getId(), $creator, $title, $description));
		return $question;
	}

	protected function applyQuestionCreatedEvent(QuestionCreatedEvent $event) {
		$this->id = $event->getAggregateId();
		$this->creator = $event->getInitiatingUserId();
		$this->description = $event->getDescription();
		$this->title = $event->getTitle();
	}


	public function setOnline() {
		$this->recordApplyAndPublishThat(new QuestionStatusHasChangedToOnline($this->id));
	}

	protected function applyQuestionStatusHasChangedToOnline(QuestionStatusHasChangedToOnline $event) {
		$this->online = true;
	}

	public function setOffline() {
		$this->recordApplyAndPublishThat(new QuestionStatusHasChangedToOffline($this->id));
	}

	protected function applyQuestionStatusHasChangedToOffline(QuestionStatusHasChangedToOffline $event) {
		$this->online = false;
	}

	public function changeSettingsFor($settings) {
		$this->recordApplyAndPublishThat(new QuestionSettingsWereChanged($this->id, $settings));
	}

	protected function applyQuestionSettingsWereChanged(QuestionSettingsWereChanged $event) {
		$this->settings = $event->settings();
	}

	public function changeTitleFor($title) {
		$this->recordApplyAndPublishThat(new QuestionTitleWasChanged($this->id, $title));
	}

	protected function applyQuestionTitleWasChanged(QuestionTitleWasChanged $event) {
		$this->title = $event->title();
	}

	// TODO intressiert mich revision hier wirklich, schlussendlich projektion mit id -> wenn bereits projektion mit id dann revision += 1 sonst revision = 1 change revision würde implizieren das ich nach revision 4 plötzlich revision 2 erstellen möchte
	public function changeRevision($revision) {
		$this->recordApplyAndPublishThat(new RevisionWasChanged($this->id, $revision));
	}

	protected function applyRevisionWasChanged(RevisionWasChanged $event) {
		$this->revision = $event->revision();
	}

	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title): void {
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription(string $description): void {
		$this->description = $description;
	}

	/**
	 * @return int
	 */
	public function getCreator(): int {
		return $this->creator;
	}

	/**
	 * @param int $creator
	 */
	public function setCreator(int $creator): void {
		$this->creator = $creator;
	}

	public function getId() : QuestionId {
		return $this->id;
	}
}