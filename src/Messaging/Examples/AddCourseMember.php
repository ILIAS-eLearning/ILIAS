<?php

require __DIR__ . '/../../../libs/composer/vendor/autoload.php';

use ILIAS\Messaging\CommandBusBuilder;
use ILIAS\Messaging\Contract\Command\Command;
use ILIAS\Messaging\Contract\Command\CommandHandler;
use ILIAS\Data\Domain\AggregateRepository;
use ILIAS\Data\Domain\IdentifiesAggregate;
use ILIAS\Data\Domain\AggregateRoot;
use ILIAS\Data\Domain\RecordsEvents;
use ILIAS\Data\Domain\DomainEvents;
use ILIAS\Data\Domain\DomainEvent;


/**
 * If you're working with a very constrained domain where there's not so many
 * dependencies, you could skip handlers altogether and implement a more
 * classic version of the command pattern where commands execute themselves.
 *
 * Here's a Tactician version of the wikipedia (https://en.wikipedia.org/wiki/Command_pattern) Light Switch example.
 */
class Course implements AggregateRoot {

	/**
	 * @var int
	 */
	private $course_id;
	/**
	 * @var CourseMember[]
	 */
	private $course_members;


	public function __construct(int $course_id, array $course_members) {
		$this->course_id = $course_id;
		$this->course_members = $course_members;
	}


	/**
	 * @param $usr_id
	 */
	public function addCourseMember($usr_id) {
		if (!$this->hasManageMemberPermission()) {
			throw new RuntimeException('No Permission');
		}

		$this->course_members[] = new CourseMember($this, $usr_id);
	}


	private function hasManageMemberPermission(): bool {
		return true;
	}


	public function course_id() {
		return $this->course_id;
	}


	public function course_members() {
		return $this->course_members;
	}


	public static function reconstituteFrom(\ILIAS\Data\Domain\AggregateHistory $aggregate_history): RecordsEvents {
		// TODO: Implement reconstituteFrom() method.
	}


	public function getRecordedEvents(): \ILIAS\Data\Domain\DomainEvents {
		// TODO: Implement getRecordedEvents() method.
	}


	public function clearRecordedEvents(): void {
		// TODO: Implement clearRecordedEvents() method.
	}


	public function getAggregateId(): IdentifiesAggregate {
		// TODO: Implement getAggregateId() method.
	}


	public function hasChanges(): bool {
		// TODO: Implement hasChanges() method.
	}
}


class CourseMember {

	/**
	 * @var Course
	 */
	private $course;
	/**
	 * @var int
	 */
	private $usr_id;


	/**
	 * CourseMember constructor.
	 *
	 * @param Course $course
	 * @param int    $usr_id
	 */
	public function __construct(Course $course, int $usr_id) {
		$this->course = $course;
		$this->usr_id = $usr_id;
	}

	public function usr_id() {
		return $this->usr_id;
	}

	public function course() {
		return $this->course;
	}
}

interface CourseRepository {

	public function add(RecordsEvents $aggregate);

	//TODO UUI


	/**
	 * @param int $course_id
	 *
	 * @return Course
	 */
	public function get(int $course_id);


	public function addAll(array $courses);


	public function remove(Course $course);
	public function removeAll(array $courses);
}

class InMemoryCourseRepository implements CourseRepository {

	/**
	 * @var Course[]
	 */
	private $courses = [];

	/**
	 * @var EventStore
	 */
	private $eventStore;
	/**
	 * @var $courseProjection
	 */
	private $course_projection;

	public function __construct($event_store, $course_projection)
	{
		$this->event_store = $event_store;
		$this->course_projection = $course_projection;

	}


	public function add(RecordsEvents $aggregate) {

		$events = $aggregate->getRecordedEvents();
		$this->eventStore->commit($events);
		$this->eventStore->commit($events);print_r("sdfdsf");exit;
		$this->course_projection->project($events);

	}


	public function remove(Course $course) {
		//unset($this->courses[$course->id()->id()]);
	}


	/**
	 * @param int $course_id
	 *
	 * @return Course
	 */
	public function get(int $course_id) {
		//TODO
	    return new Course(2, array());
	//	return $this->courses[$course_id];
	}


	private function filterCourses(callable $fn) {
		return array_values(array_filter($this->courses, $fn));
	}


	public function nextIdentity() {
		//return new ();
	}


	public function addAll(array $courses) {
		// TODO: Implement addAll() method.
	}


	public function removeAll(array $courses) {
		// TODO: Implement removeAll() method.
	}
}

class addCourseMemberToCourseCommand implements Command {

	//TODO - should be IdentifiesAggregate
	/**
	 * @var int
	 */
	public $course_id;
	/**
	 * @var int
	 */
	public $user_id;


	public function __construct(int $course_id, int $user_id) {
		$this->course_id = $course_id;
		$this->user_id = $user_id;
	}
}

class addCourseMemberToCourseCommandHandler implements CommandHandler {

	/**
	 * @var CourseRepository
	 */
	private $course_repository;


	public function __construct(/*$course_repository*/) {
		//TODO

		$this->course_repository = new InMemoryCourseRepository(new InMemoryEventStore(),new CourseProjection());
	}


	/**
	 * @param addCourseMemberToCourseCommand $command
	 */
	public function handle(Command $command) {
		$course = $this->course_repository->get($command->course_id);

		$course->addCourseMember($command->user_id);
		$this->course_repository->add($course);


	}
}

interface EventStore
{
	/**
	 * @param DomainEvents $events
	 *
	 * @return void
	 */
	public function commit(DomainEvents $events);
	/**
	 * @param IdentifiesAggregate $id
	 *
	 * @return AggregateHistory
	 */
	public function getAggregateHistoryFor(IdentifiesAggregate $id);
}


class InMemoryEventStore implements EventStore {

	private $events = [];


	public function commit(DomainEvents $events) {
		foreach ($events as $event) {
			$this->events[] = $event;
		}
	}


	public function getAggregateHistoryFor(IdentifiesAggregate $id) {
		return new AggregateHistory($id, array_filter($this->events, function (DomainEvent $event) use ($id) {
			return $event->getAggregateId()->equals($id);
		}));
	}
}


final class AggregateHistory extends DomainEvents
{
	/**
	 * @var IdentifiesAggregate
	 */
	private $aggregateId;

	public function __construct(IdentifiesAggregate $aggregateId, array $events)
	{
		/** @var $event DomainEvent */
		foreach($events as $event) {
			if(!$event->getAggregateId()->equals($aggregateId)) {
				throw new CorruptAggregateHistory;
			}
		}
		parent::__construct($events);
		$this->aggregateId = $aggregateId;
	}

	/**
	 * @return IdentifiesAggregate
	 */
	public function getAggregateId()
	{
		return $this->aggregateId;
	}

	/**
	 * @param DomainEvent $domainEvent
	 * @return AggregateHistory
	 */
	public function append(DomainEvent $domainEvent)
	{
		throw new \Exception("@todo  Implement append() method.");
	}
}


abstract class BaseProjection
{
	public function project(DomainEvents $eventStream)
	{
		foreach ($eventStream as $event) {
			$projectMethod = 'project' . ClassFunctions::short($event);
			$this->$projectMethod($event);
		}
	}
}

class CourseProjection {

	private $questions = [];


	public function project(DomainEvents $eventStream) {
		foreach ($eventStream as $event) {
			$projectMethod = 'project' . $this->short($event);
			$this->$projectMethod($event);
		}
	}
	//TODO extract

	/**
	 * The class name of an object, without the namespace
	 * @param object|string $object
	 * @return string
	 */
	function short($object)
	{
		$parts = explode('\\', $this->fqcn($object));
		return end($parts);
	}

	/**
	 * Fully qualified class name of an object, without a leading backslash
	 * @param object|string $object
	 * @return string
	 */
	function fqcn($object)
	{
		if (is_string($object)) {
			return str_replace('.', '\\', $object);
		}
		if (is_object($object)) {
			return trim(get_class($object), '\\');
		}
		throw new \InvalidArgumentException(sprintf("Expected an object or a string, got %s", gettype($object)));
	}
}

interface CourseMemberWasAdded {

	/**
	 * Projects a posts creation event
	 *
	 * @param CourseMemberWasAdded $event
	 *
	 * @return void
	 */
	public function projectCourseMemberWasAdded($course_id, $user_id);
}








/*
interface Projection {

	public function project(DomainEvents $eventStream);
}*/




// Why doesn't the SelfExecutionMiddleware call $next anywhere? Well, it could
// but it's convention to not call $next any further once the command has been
// executed, which stops the chain from going further. Otherwise, you might
//have the same command get handled twice.

$command_bus = new CommandBusBuilder();
$command_bus->handle(new AddCourseMemberToCourseCommand(2,56));


