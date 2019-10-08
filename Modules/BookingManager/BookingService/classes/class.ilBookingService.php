<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Low level api for booking service
 *
 * @author @leifos.de
 * @ingroup 
 */
class ilBookingService
{
	/**
	 * @var ilDBInterface
	 */
	protected $db;

	/**
	 * Constructor
	 */
	public function __construct(ilDBInterface $db = null)
	{
		global $DIC;

		$this->db = ($db == null)
			? $DIC->database()
			: $db;
	}
	
	/**
	 * Clone settings
	 *
	 * @param $source_obj_id
	 * @param $target_obj_id
	 */
	public function cloneSettings($source_obj_id, $target_obj_id)
	{
		$use_book_repo = new ilObjUseBookDBRepository($this->db);
		$book_ref_ids = $use_book_repo->getUsedBookingPools($source_obj_id);
		$use_book_repo->updateUsedBookingPools($target_obj_id, $book_ref_ids);
	}
}