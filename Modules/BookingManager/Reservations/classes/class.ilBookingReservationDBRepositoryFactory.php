<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for reservation repo
 *
 * @author killing@leifos.de
 * @ingroup ModulesBookingManager
 */
class ilBookingReservationDBRepositoryFactory
{
	/**
	 * @var ilDBInterface
	 */
	protected $db;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $DIC;

		$this->db = $DIC->database();
	}

	/**
	 * Get repo without any preloaded data
	 *
	 * @return ilBookingReservationDBRepository
	 */
	public function getRepo(): ilBookingReservationDBRepository
	{
		return new ilBookingReservationDBRepository($this->db);
	}

	/**
	 * Get repo with reservation information preloaded for context obj ids
	 *
	 * @return ilBookingReservationDBRepository
	 */
	public function getRepoWithContextObjCache($context_obj_ids): ilBookingReservationDBRepository
	{
		return new ilBookingReservationDBRepository($this->db, $context_obj_ids);
	}


}