<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Value object for booking service settings of a repository object
 *
 * @author killing@leifos.de
 * @ingroup ModulesBookingManager
 */
class ilObjBookingServiceSettings
{
	/**
	 * repository object id (e.g. a course)
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var int[]
	 */
	protected $book_obj_ids;

	/**
	 * Constructor
	 * @param int $obj_id
	 * @param array $book_obj_ids
	 */
	public function __construct(int $obj_id, array $book_obj_ids)
	{
		$this->obj_id = $obj_id;
		$this->book_obj_ids = $book_obj_ids;
	}

	/**
	 * Get object id of repo object
	 *
	 * @return int
	 */
	public function getObjectId(): int
	{
		return $this->obj_id;
	}

	/**
	 * Get used booking object ids
	 *
	 * @return int[]
	 */
	public function getUsedBookingObjectIds(): array
	{
		return $this->book_obj_ids;
	}



}