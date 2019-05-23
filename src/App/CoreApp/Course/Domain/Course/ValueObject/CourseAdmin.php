<?php
namespace ILIAS\App\CoreApp\Course\Domain\Value;

use ILIAS\App\CoreApp\Course\Domain\Entity\Course;
use ILIAS\App\CoreApp\User\Domain\Entity\User;
use ILIAS\App\Domain\ValueObject\ValueObject;

/**
 * CourseAdmin
 */
class CourseAdmin implements ValueObject
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
     * @var bool|null
     */
    private $admin = '0';

    /**
     * @var bool|null
     */
    private $tutor = '0';

    /**
     * @var int|null
     */
    private $member = '0';


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return CourseAdmin
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return CourseAdmin
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
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

    /**
     * Set blocked.
     *
     * @param bool $blocked
     *
     * @return CourseAdmin
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
     * @return CourseAdmin
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
     * @return CourseAdmin
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
     * @return CourseAdmin
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
     * @return CourseAdmin
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
     * @return CourseAdmin
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
     * Set admin.
     *
     * @param bool|null $admin
     *
     * @return CourseAdmin
     */
    public function setAdmin($admin = null)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin.
     *
     * @return bool|null
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set tutor.
     *
     * @param bool|null $tutor
     *
     * @return CourseAdmin
     */
    public function setTutor($tutor = null)
    {
        $this->tutor = $tutor;

        return $this;
    }

    /**
     * Get tutor.
     *
     * @return bool|null
     */
    public function getTutor()
    {
        return $this->tutor;
    }

    /**
     * Set member.
     *
     * @param int|null $member
     *
     * @return CourseAdmin
     */
    public function setMember($member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member.
     *
     * @return int|null
     */
    public function getMember()
    {
        return $this->member;
    }
}
