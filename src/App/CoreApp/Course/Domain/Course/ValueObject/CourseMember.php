<?php

namespace ILIAS\App\CoreApp\Course\Domain\Value;

use ILIAS\App\CoreApp\Course\Domain\Entity\Course;
use ILIAS\App\CoreApp\User\Domain\Entity\User;
use ILIAS\App\Domain\ValueObject\ValueObject;

/**
 * CourseMember
 */
class CourseMember implements ValueObject
{
	/**
	 * @var Course
	 */
	protected $course;

	/**
	 * @var User
	 */
	protected $user;
	/**
	 * @var int
	 */
	private $objId = '0';

	/**
	 * @var int
	 */
	private $usrId = '0';

	/**
	 * @var bool
	 */
	private $blocked = '0';

	/**
	 * @var bool|null
	 */
	private $passed;

	/**
	 * @var int|null
	 */
	private $origin = '0';

	/**
	 * @var int|null
	 */
	private $originTs = '0';


	/**
	 * @return int
	 */
	public function getObjId(): int {
		return $this->objId;
	}


	/**
	 * @param int $objId
	 */
	public function setObjId(int $objId): void {
		$this->objId = $objId;
	}


	/**
	 * @return int
	 */
	public function getUsrId(): int {
		return $this->usrId;
	}


	/**
	 * @param int $usrId
	 */
	public function setUsrId(int $usrId): void {
		$this->usrId = $usrId;
	}

	/**
	 * Set blocked.
	 *
	 * @param bool $blocked
	 *
	 * @return CourseMember
	 */
	public function setBlocked($blocked)
	{
		$this->blocked = $blocked;

		return $this;
	}

	/**
	 * Get blocked.
	 *
	 * @return bool
	 */
	public function getBlocked()
	{
		return $this->blocked;
	}

	/**
	 * Set passed.
	 *
	 * @param bool|null $passed
	 *
	 * @return CourseMember
	 */
	public function setPassed($passed = null)
	{
		$this->passed = $passed;

		return $this;
	}

	/**
	 * Get passed.
	 *
	 * @return bool|null
	 */
	public function getPassed()
	{
		return $this->passed;
	}

	/**
	 * Set origin.
	 *
	 * @param int|null $origin
	 *
	 * @return CourseMember
	 */
	public function setOrigin($origin = null)
	{
		$this->origin = $origin;

		return $this;
	}

	/**
	 * Get origin.
	 *
	 * @return int|null
	 */
	public function getOrigin()
	{
		return $this->origin;
	}

	/**
	 * Set originTs.
	 *
	 * @param int|null $originTs
	 *
	 * @return CourseMember
	 */
	public function setOriginTs($originTs = null)
	{
		$this->originTs = $originTs;

		return $this;
	}

	/**
	 * Get originTs.
	 *
	 * @return int|null
	 */
	public function getOriginTs()
	{
		return $this->originTs;
	}

	/**
	 * @return Course
	 */
	public function getCourse(): Course {
		return $this->course;
	}


	/**
	 * @param Course $course
	 */
	public function setCourse(Course $course): void {
		$this->course = $course;
	}


	/**
	 * @return User
	 */
	public function getUser(): User {
		return $this->user;
	}


	/**
	 * @param User $user
	 */
	public function setUser(User $user): void {
		$this->user = $user;
	}
}