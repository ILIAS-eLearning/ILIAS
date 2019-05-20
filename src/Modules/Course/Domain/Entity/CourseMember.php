<?php

namespace ILIAS\Modules\Course\Domain\Entity;
require_once "/var/www/ilias/src/Modules/Course/Domain/Entity/ObjMembers.php";
use ILIAS\Modules\User\Domain\Entity\User;

/**
 * CourseMember
 */
class CourseMember extends ObjMembers
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
	 * @var integer
	 */
	private $member = 1;

	/**
	 * @var bool
	 */
	private $blocked = '0';

	/**
	 * @var bool
	 */
	private $notification = '0';

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
	 * @var bool|null
	 */
	private $contact = '0';


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
	 * Set notification.
	 *
	 * @param bool $notification
	 *
	 * @return CourseMember
	 */
	public function setNotification($notification)
	{
		$this->notification = $notification;

		return $this;
	}

	/**
	 * Get notification.
	 *
	 * @return bool
	 */
	public function getNotification()
	{
		return $this->notification;
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
	 * Set contact.
	 *
	 * @param bool|null $contact
	 *
	 * @return CourseMember
	 */
	public function setContact($contact = null)
	{
		$this->contact = $contact;

		return $this;
	}

	/**
	 * Get contact.
	 *
	 * @return bool|null
	 */
	public function getContact()
	{
		return $this->contact;
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