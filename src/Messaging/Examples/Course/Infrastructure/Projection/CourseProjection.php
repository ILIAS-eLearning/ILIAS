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
use MongoCollection;
use MongoDB\Driver\Manager;
use MongoClient;
use Collection;
use PhpOrient\PhpOrient;

class CourseProjection extends BaseProjection /* implements BaseCourseProjection*/
{
	/**
	 * @var Collection
	 */
	private $courseCollection;
	public function __construct($courseCollection)
	{
		$this->courseCollection = $courseCollection;
	}
	/**
	 * Projects a course creation event
	 *
	 * @param CourseWasCreated $event
	 *
	 * @return void
	 */
	public function projectCourseWasCreated(CourseWasCreated $event)
	{
		if ($this->courseCollection->count(['course_id' => (string) $event->getAggregateId()]) > 0) {
			return;
		}
		$this->courseCollection->insert([
			'course_id' => (string) $event->getAggregateId(),
			'title'   => $event->getTitle()
		]);
	}

	/**
	 * Projects when a course title was changed
	 *
	 * @param CourseTitleWasChanged $event
	 *
	 * @return void
	 */
	public function projectPostTitleWasChanged(CourseTitleWasChanged $event)
	{
		$course = $this->courseCollection->findOne(['course_id' => (string) $event->getAggregateId()]);
		if (null === $course || $event->getTitle() === $course['title']) {
			return;
		}
		$course['title'] = $event->getTitle();
		$this->courseCollection->update($course);
	}

	/**
	 * Projects when a course member is added
	 *
	 * @param CourseMemberWasAdded $event
	 *
	 * @return void
	 */
	public function projectCourseMemberWasAdded(CourseMemberWasAdded $event)
	{

		$crs = new CourseWasCreated($event->getAggregateId(),'test');
		$this->projectCourseWasCreated($crs);

		$course = $this->courseCollection->findOne(['course_id' => (string) $event->getAggregateId()]);


		if (!isset($course['course_members'])) {
			$course['course_members'] = [];
		}

		$memberAlreadyExists = count(array_filter($course['course_members'], function ($course_members) use ($event) {
				return $course_members['usr_id'] === $event->getUsrId();
			})) > 0;
		if ($memberAlreadyExists) {
			return;
		}
		$course['course_members'] = [
			'usr_id' => (string) $event->getUsrId()
		];
		$this->courseCollection->insert($course);
	}
}
