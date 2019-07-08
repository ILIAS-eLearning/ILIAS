# Manual Domain Driven Design
The abstract classes and interfaces provided here can be used to implement the Domain Driven Design Pattern in two Variants:
1. Common DDD
2. Event Sourced DDD

Currently the classes are provided exclusively for the Assessment Question Service. To be able to use them, you need to understand the following concepts.

## Terms
### Bounded Context
Bounded Context is one of the most important concepts of DDD. A Bounded Context is a conceptual limit where a domain model is applicable.

A single Bounded Context can include many Aggregate Roots, or we can organise a single aggregate root into a single Bounded Context

### Aggregate

A collection of objects (Domain Models) that are bound together by a root entity, otherwise known as an aggregate root. An Aggregate has a boundary. The Boundary defines what is inside the aggregate. 

There’s a good rule for working with aggregates that says that we should not update more than one aggregate per transaction.

#### Implementation
Folder Structure in your Module / Service
```
src/[BoundedContext]/Domain/[Aggregate]/...
```

### Aggregate Root
The Aggregate Root is a single, specific Entity contained in the Aggregate. It guarantees the consistency of changes being made within the aggregate by forbidding external objects from holding references to its members.

This means that Aggregate Roots are the only objects that can be loaded from a repository.


#### Implementation of an event sourced Aggregate Root

/**
 * Class Question
 * 
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
	private $revision_name;
	/**
	 * @var int
	 */
	private $creator_id;
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
	 * @param int    $creator_id
	 *
	 * @return \ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question
	 */
	public static function createNewQuestion(int $creator_id) {
		$question = new Question();
		$question->ExecuteEvent(new QuestionCreatedEvent(new DomainObjectId(), $creator_id));
		return $question;
	}


	protected function applyQuestionCreatedEvent(QuestionCreatedEvent $event) {
		$this->id = $event->getAggregateId();
		$this->creator_id = $event->getInitiatingUserId();
	}

	protected function applyQuestionDataSetEvent(QuestionDataSetEvent $event) {
		$this->data = $event->getData();
	}

	protected function applyQuestionRevisionCreatedEvent(QuestionRevisionCreatedEvent $event) {
		$this->revision_id = new RevisionId($event->getRevisionKey());
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

	public function getOnlineState() : bool {
		return $this->online;
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
	public function getCreatorId(): int {
		return $this->creator_id;
	}


	/**
	 * @param int $creator_id
	 */
	public function setCreatorId(int $creator_id): void {
		$this->creator_id = $creator_id;
	}


	/**
	 * @return RevisionId revision id of object
	 */
	public function getRevisionId(): ?RevisionId {
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
		$this->ExecuteEvent(new QuestionRevisionCreatedEvent($this->getAggregateId(), $this->creator_id, $id->GetKey()));
	}

	/**
	 * @return string
	 *
	 * Name of the revision used by the RevisionFactory when generating a revision
	 * Using of Creation Date and or an increasing Number are encouraged
	 *
	 */
	public function getRevisionName(): ?string {
		return time();
	}


	/**
	 * @return array
	 *
	 * Data used for signing the revision, so this method needs to to collect all
	 * Domain specific data of an object and return it as an array
	 */
	public function getRevisionData(): array {
		$data[] = $this->getAggregateId()->getId();
		$data[] = $this->getData()->jsonSerialize();
		return $data;
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
```

### Entity
Entities are domain classes that are uniquely defined by a unique identifier - but are not the root entity.

#### Implementation
//TODO

### Repository
A repository is an abstraction over the persistence store, returning aggregate roots. Typically there is one repository per aggregate root.


#### Implementation
```
class ExampleQuestionRepository extends AbstractEventSourcedAggregateRepository {

	/**
	 * @var EventStore
	 */
	private $event_store;
	/**
	 * @var ExampleQuestionRepository
	 */
	private static $instance;


	protected function __construct() {
		parent::__construct();
		$this->event_store = new ilDBQuestionEventStore();
	}


	public static function getInstance() {
		if (self::$instance === null) {
			self::$instance = new QuestionRepository();
		}

		return self::$instance;
	}


	/**
	 * @return EventStore
	 */
	protected function getEventStore(): EventStore {
		return $this->event_store;
	}


	/**
	 * @param DomainEvents $event_history
	 *
	 * @return \ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateRoot
	 */
	protected function reconstituteAggregate(DomainEvents $event_history): AggregateRoot {
		return ExampleQuestion::reconstitute($event_history);
	}
}
```