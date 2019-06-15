<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Shared\QuestionId;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Event\QuestionCreatedEvent;
use ILIAS\Data\Domain\Entity\AggregateRevision;

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
	 * @var bool
	 */
	private $online = false;
	/**
	 * @var
	 */
	private $possible_answers;


//TODO ,AggregateRevision $rev asl zweiter Parameter
	private function __construct(QuestionId $id)
	{
		$this->id = $id;
		$this->rev= $rev;
		//$this->possible_answers = new PossibleAnswers();
	}


	/**
	 * @param $title
	 * @param $description
	 */
	public static function createFrom($title, $description) {
		//TODO DIC von aussen?
		global $DIC;

		$question = new Question(new QuestionId());
		$question_serialized = $DIC->refinery()->object()->JsonSerializedObject($question);
		$question->recordApplyAndPublishThat(new QuestionCreatedEvent($question->id,0,$DIC->user()->id,$question_serialized));
	}

	protected function applyQuestionCreatedEvent(QuestionCreatedEvent $event) {
		$this->id = $event->id();
		$this->title = $event->title();
		$this->description = $event->description();
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

	public function changeRevision($revision) {
		$this->recordApplyAndPublishThat(new RevisionWasChanged($this->id, $revision));
	}

	protected function applyRevisionWasChanged(RevisionWasChanged $event) {
		$this->revision = $event->revision();
	}
}