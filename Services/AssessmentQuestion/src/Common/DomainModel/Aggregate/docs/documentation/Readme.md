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

#### Implementation of an common DDD Aggregate Root
```
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AbstractAggregateRoot;
use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\AggregateId;

class Question extends AbstractAggregateRoot {

	/**
	 * @var AggregateId
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
	private $creator_id;


	private function __construct(AggregateId $id, string $title, string $description, int $creator_id) {
		$this->id = $id;
		$this->title = $title;
		$this->description = $description;
		$this->creator_id = $creator_id;
	}


	public function editTitle(string $title) {
		$this->title = $title;
	}


	public function editDescription(string $description) {
		$this->description = $description;
	}

	
	function getAggregateId(): AggregateId {
		return $this->id;
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


	/**
	 * @return int
	 */
	public function getCreatorId(): int {
		return $this->creator_id;
	}
}
```

#### Implementation of an event sourced Aggregate Root
//TODO

### Entity
Entities are domain classes that are uniquely defined by a unique identifier - but are not the root entity.

#### Implementation
//TODO

### Repository