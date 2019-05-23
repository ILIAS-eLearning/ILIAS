<?php

namespace ILIAS\App\CoreApp\Course\Domain\Entity;

/**
 * Course
 */
class Course
{
	/**
     * @var CourseAdmin[]
     */
    private $admins = [];

    /**
     * @var CourseMember[]
     */
    private $members = [];

	/**
	 * @var int
	 */
	private $objId = '0';

	/**
	 * @var string|null
	 */
	private $type = 'none';

	/**
	 * @var string|null
	 */
	private $title;

	/**
	 * @var string|null
	 */
	private $description;

	/**
	 * @var int
	 */
	private $owner = '0';

	/**
	 * @var \DateTime|null
	 */
	private $createDate;

	/**
	 * @var \DateTime|null
	 */
	private $lastUpdate;

	/**
	 * @var string|null
	 */
	private $importId;

	/**
	 * @var bool|null
	 */
	private $offline;


	/**
	 * @return CourseAdmin[]
	 */
	public function getAdmins(): array {
		return $this->admins;
	}


	/**
	 * @param CourseAdmin[] $admins
	 */
	public function setAdmins(array $admins): void {
		$this->admins = $admins;
	}


	/**
	 * @return CourseMember[]
	 */
	public function getMembers(): array {
		return $this->members;
	}


	/**
	 * @param CourseMember[] $members
	 */
	public function setMembers(array $members): void {
		$this->members = $members;
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
	 * Set type.
	 *
	 * @param string|null $type
	 *
	 * @return Course
	 */
	public function setType($type = null)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Get type.
	 *
	 * @return string|null
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set title.
	 *
	 * @param string|null $title
	 *
	 * @return Course
	 */
	public function setTitle($title = null)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * Get title.
	 *
	 * @return string|null
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set description.
	 *
	 * @param string|null $description
	 *
	 * @return Course
	 */
	public function setDescription($description = null)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Get description.
	 *
	 * @return string|null
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set owner.
	 *
	 * @param int $owner
	 *
	 * @return Course
	 */
	public function setOwner($owner)
	{
		$this->owner = $owner;

		return $this;
	}

	/**
	 * Get owner.
	 *
	 * @return int
	 */
	public function getOwner()
	{
		return $this->owner;
	}

	/**
	 * Set createDate.
	 *
	 * @param \DateTime|null $createDate
	 *
	 * @return Course
	 */
	public function setCreateDate($createDate = null)
	{
		$this->createDate = $createDate;

		return $this;
	}

	/**
	 * Get createDate.
	 *
	 * @return \DateTime|null
	 */
	public function getCreateDate()
	{
		return $this->createDate;
	}

	/**
	 * Set lastUpdate.
	 *
	 * @param \DateTime|null $lastUpdate
	 *
	 * @return Course
	 */
	public function setLastUpdate($lastUpdate = null)
	{
		$this->lastUpdate = $lastUpdate;

		return $this;
	}

	/**
	 * Get lastUpdate.
	 *
	 * @return \DateTime|null
	 */
	public function getLastUpdate()
	{
		return $this->lastUpdate;
	}

	/**
	 * Set importId.
	 *
	 * @param string|null $importId
	 *
	 * @return Course
	 */
	public function setImportId($importId = null)
	{
		$this->importId = $importId;

		return $this;
	}

	/**
	 * Get importId.
	 *
	 * @return string|null
	 */
	public function getImportId()
	{
		return $this->importId;
	}

	/**
	 * Set offline.
	 *
	 * @param bool|null $offline
	 *
	 * @return Course
	 */
	public function setOffline($offline = null)
	{
		$this->offline = $offline;

		return $this;
	}

	/**
	 * Get offline.
	 *
	 * @return bool|null
	 */
	public function getOffline()
	{
		return $this->offline;
	}
}
