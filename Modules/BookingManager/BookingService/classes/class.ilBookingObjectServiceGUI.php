<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Service (e.g. being used in a course) UI wrapper for booking objects
 *
 * @author killing@leifos.de
 * @ingroup ModulesBookingManager
 *
 * @ilCtrl_Calls ilBookingObjectServiceGUI: ilPropertyFormGUI, ilBookingProcessGUI
 */
class ilBookingObjectServiceGUI extends ilBookingObjectGUI
{
	/**
	 * @var int
	 */
	protected $host_obj_ref_id;

	/**
	 * @var ilObjUseBookDBRepository
	 */
	protected $use_book_repo;

	/**
	 * @var
	 */
	//protected $current_pool_ref_id;

	/**
	 * ilBookingObjectServiceGUI constructor.
	 * @param int $host_obj_ref_id Host object ref id (e.g. course)
	 */
	public function __construct(int $host_obj_ref_id, int $current_pool_ref_id, \ilObjUseBookDBRepository $use_book_repo,
		string $seed, string $sseed, ilBookingHelpAdapter $help)
	{
		parent::__construct(null, $seed, $sseed,
			$help,
			ilObject::_lookupObjId($host_obj_ref_id));
		$this->host_obj_ref_id = (int) $host_obj_ref_id;
		$this->use_book_repo = $use_book_repo;
		$this->pool_gui = new ilObjBookingPoolGUI("", $current_pool_ref_id, true, false);
		$this->activateManagement(false);
	}

}