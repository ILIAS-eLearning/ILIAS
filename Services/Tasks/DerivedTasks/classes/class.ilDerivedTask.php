<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Derived task data object
 *
 * @author killing@leifos.de
 * @ingroup ServicesTasks
 */
class ilDerivedTask
{
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * @var int
	 */
	protected $deadline;

	/**
	 * @var int
	 */
	protected $starting_time;

	/**
	 * Constructor
	 * @param string $title
	 * @param int $ref_id
	 * @param int $deadline
	 * @param int $starting_time
	 */
	public function __construct(string $title, int $ref_id, int $deadline, int $starting_time)
	{
		$this->title = $title;
		$this->ref_id = $ref_id;
		$this->deadline = $deadline;
		$this->starting_time = $starting_time;
	}

	/**
	 * Get ref id
	 *
	 * @return int
	 */
	public function getRefId()
	{
		return $this->ref_id;
	}

	/**
	 * @return int
	 */
	public function getDeadline()
	{
		return $this->deadline;
	}

	/**
	 * @return int
	 */
	public function getStartingTime()
	{
		return $this->starting_time;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

}