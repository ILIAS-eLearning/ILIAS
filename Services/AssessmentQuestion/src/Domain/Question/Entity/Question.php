<?php

namespace ILIAS\AssessmentQuestion\Domainmodel\Question;

use ilException;
use ILIAS\Data\Domain\AggregateRoot;
use ILIAS\Data\Domain\DomainEvent;
use ILIAS\Data\Domain\IsEventSourced;
use ILIAS\Messaging\Example\ExampleCourse\Command\Events\CourseMemberWasAdded;
use ILIAS\Data\Domain\RecordsEvents;
use ILIAS\Data\Domain\AggregateHistory;
use ILIAS\Data\Domain\DomainEvents;
use ILIAS\Data\Domain\IdentifiesAggregate;
use ILIAS\AssessmentQuestion\Domainmodel\Event\QuestionWasCreated;

class Question extends AggregateRoot implements isEventSourced {

	const STATE_DRAFT = 10;

	/**
	 * @var IdentifiesAggregate
	 */
	private $question_id;
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
	private $state;
	/**
	 * @var PossibleAnswers[]
	 */
	private $possible_answers;


	public function __construct(IdentifiesAggregate $question_id, string $title, string $description, int $state) {
		$this->question_id = $question_id;
		$this->description = $description;
		$this->state = $state;
	}

	public static function create($question_id, $title, $description)
	{
		$question = new Question($question_id, $title, $description, static::STATE_DRAFT);
		$question->recordThat(
			new QuestionWasCreated($question_id, $title, $description, static::STATE_DRAFT)
		);
		return $question;
	}



	/**
	 * @param $possible_answer_id
	 */
	public function addPossibleAnswer($possible_answer_id) {

		$this->possible_answer_id[] = new PossibleAnswer($this, $possible_answer_id);

		$this->recordThat(new PossibleAnswerWasAdded($this->question_id, $possible_answer_id));
	}


	public function question_id() {
		return $this->question_id;
	}

	public function title() {
		return $this->title;
	}


	public function possible_answers() {
		return $this->possible_answers;
	}


	public static function reconstituteFrom(AggregateHistory $aggregate_history): RecordsEvents {
		$aggregate = static::createInstanceForGivenHistory($aggregate_history);
		foreach ($aggregate_history as $event) {
			$aggregate->apply($event);
		}

		return $aggregate;
	}


	private function apply($anEvent) {
		$method = 'apply' . short($anEvent);
		$this->$method($anEvent);
	}

/*
	private function applyCourseMemberWasAdded(CourseMemberWasAdded $event) {
		$this->course_id = $event->getAggregateId();
		//$this->course_members[] = $event->getUsrId();
	}*/


	// ---------------------------------------------------------------------

	protected static function createInstanceForGivenHistory(AggregateHistory $aggregate_history) {
		return new static($aggregate_history->getAggregateId(), array());
	}


	public function getAggregateId(): IdentifiesAggregate {
		// TODO: Implement getAggregateId() method.
	}


	public function hasChanges(): bool {
		return false;
		// TODO: Implement hasChanges() method.
	}
}


/*
	 *
	 *
	 * https://github.com/jkuchar/talk-EventSourcing101-Vienna-2019-02-21/blob/master/src/EventSourcing/AbstractAggregate.php
	 *https://github.com/slavkoss/fwphp/blob/181f989a0ef215469734699835f8dea5f95349f9/fwphp/glomodul4/help_sw/test/ddd/blog/src/CQRSBlog/BlogEngine/DomainModel/Post.php
	 */
