<?php

namespace srag\IliasComponentCourse\Course\Command\Event;

class CourseMemberWasAdded implements CourseEvent
{
	private $id;
	private $usr_id;
	private $occurredOn;

	public function __construct(int $id, int $usr_id)
	{
		$this->id = $id;
		$this->usr_id = $usr_id;
		$this->occurredOn = new \DateTimeImmutable();
	}

	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}


	/**
	 * @return int
	 */
	public function getUsrId(): int {
		return $this->usr_id;
	}


	/**
	 * @return \DateTimeImmutable
	 */
	public function getOccurredOn(): \DateTimeImmutable {
		return $this->occurredOn;
	}
}