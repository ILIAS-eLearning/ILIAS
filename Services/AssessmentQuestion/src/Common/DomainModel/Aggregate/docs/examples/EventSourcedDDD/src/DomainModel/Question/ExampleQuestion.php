<?php

namespace ILIAS\AssessmentQuestion\Common\examples\EventSourcedDDD\DomainModel\Aggregate;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractEventSourcedAggregateRoot;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateRoot;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\DomainObjectId;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Event\DomainEvents;
use ILIAS\AssessmentQuestion\Common\IsRevisable;
use ILIAS\AssessmentQuestion\Common\AbstractRevisionId;
use ILIAS\AssessmentQuestion\Common\RevisionId;

/**
 * Class Question
 *
 * @package ILIAS\AssessmentQuestion\Common\examples\EventSourcedDDD\DomainModel\Aggregate
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ExampleQuestion extends AbstractEventSourcedAggregateRoot implements IsRevisable {

	/**
	 * @var DomainObjectId
	 */
	private $id;
	/**
	 * @var RevisionId
	 */
	private $revision_id;
	/**
	 * @var string
	 */
	private $revision_name = "";
	/**
	 * @var int
	 */
	private $creator;
	/**
	 * @var bool
	 */
	private $online = false;
	/**
	 * @var QuestionData
	 */
	private $data;
	/**
	 * @var
	 */
	private $possible_answers;


	protected function __construct() {
		parent::__construct();
	}


	/**
	 * @param string $title
	 * @param string $description
	 *
	 * @param int    $creator
	 *
	 * @return Question
	 */
	public static function createNewQuestion(int $creator) {
		$question = new Question();
		$question->ExecuteEvent(new QuestionCreatedEvent(new QuestionId(), $creator));
		return $question;
	}


	protected function applyQuestionCreatedEvent(QuestionCreatedEvent $event) {
		$this->id = $event->getAggregateId();
		$this->creator = $event->getInitiatingUserId();
	}

	protected function applyQuestionDataSetEvent(QuestionDataSetEvent $event) {
		$this->data = $event->data;
	}

	public function setOnline() {
		$this->ExecuteEvent(new QuestionStatusHasChangedToOnlineEvent($this->id));
	}


	protected function applyQuestionStatusHasChangedToOnline(QuestionStatusHasChangedToOnlineEvent $event) {
		$this->online = true;
	}


	public function setOffline() {
		$this->ExecuteEvent(new QuestionStatusHasChangedToOfflineEvent($this->id));
	}


	protected function applyQuestionStatusHasChangedToOffline(QuestionStatusHasChangedToOfflineEvent $event) {
		$this->online = false;
	}

	public function createRevision() {
		$this->ExecuteEvent(new RevisionWasCreated($this->id));
	}

	protected function applyRevisionWasCreated(RevisionWasCreated $event) {
		//TODO implement me
	}


	public function changeSettingsFor($settings) {
		$this->ExecuteEvent(new QuestionSettingsWereChanged($this->id, $settings));
	}


	protected function applyQuestionSettingsWereChanged(QuestionSettingsWereChanged $event) {
		$this->settings = $event->settings();
	}

	/**
	 * @return QuestionData
	 */
	public function getData(): QuestionData {
		return $this->data;
	}


	/**
	 * @param QuestionData $data
	 * @param int          $creator_id
	 */
	public function setData(QuestionData $data, int $creator_id = 3): void {
		$this->ExecuteEvent(new QuestionDataSetEvent($this->getAggregateId(), $creator_id, $data));
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


	/**
	 * @return RevisionId revision id of object
	 */
	public function getRevisionId(): RevisionId {
		return $this->revision_id;
	}


	/**
	 * @param RevisionId $id
	 *
	 * Revision id is only to be set by the RevisionFactory when generating a
	 * revision or by the persistance layer when loading an object
	 *
	 * @return mixed
	 */
	public function setRevisionId(RevisionId $id) {
		$this->revision_id = $id;
	}


	/**
	 * @return string
	 *
	 * Name of the revision used by the RevisionFactory when generating a revision
	 * Using of Creation Date and or an increasing Number are encouraged
	 *
	 */
	public function getRevisionName(): string {
		return $this->revision_name;
	}


	/**
	 * @return array
	 *
	 * Data used for signing the revision, so this method needs to to collect all
	 * Domain specific data of an object and return it as an array
	 */
	public function getRevisionData(): array {
		//TODO when implementing revisions
	}


	public static function reconstitute(DomainEvents $event_history): AggregateRoot {
		$question = new Question();
		foreach ($event_history->getEvents() as $event) {
			$question->applyEvent($event);
		}
		return $question;
	}


	function getAggregateId(): DomainObjectId {
		return $this->id;
	}
}