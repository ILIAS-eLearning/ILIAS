<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Handles exercise repository object assignments. Main entry point for consumers.
 *
 * @author @leifos.de
 * @ingroup
 */
class ilExcRepoObjAssignment implements ilExcRepoObjAssignmentInterface
{

	/**
	 * Constructor
	 *
	 */
	protected function __construct()
	{
	}

	/**
	 * Get instance
	 *
	 * @param
	 * @return
	 */
	static public function getInstance()
	{
		return new self();
	}

	/**
	 * Get assignment(s) information of repository object
	 *
	 * @param int $a_ref_id ref id
	 * @param int $a_user_id if user id is provided, only readable links will be added
	 * @return ilExcRepoObjAssignmentInfoInterface[]
	 */
	function getAssignmentInfoOfObj($a_ref_id, $a_user_id)
	{
		return ilExcRepoObjAssignmentInfo::getInfo($a_ref_id, $a_user_id);
	}

	/**
	 * Get assignment access info for a repository object
	 *
	 * @param int $a_ref_id ref id
	 * @param int $a_user_id user id
	 * @return ilExcRepoObjAssignmentAccessInfoInterface
	 */
	function getAccessInfo($a_ref_id, $a_user_id)
	{
		return ilExcRepoObjAssignmentAccessInfo::getInfo($a_ref_id, $a_user_id);
	}

	/**
	 * Is access denied
	 *
	 * @param int $a_ref_id ref id
	 * @param int $a_user_id user id
	 * @return bool
	 */
	function isGranted($a_ref_id, $a_user_id)
	{
		$info = ilExcRepoObjAssignmentAccessInfo::getInfo($a_ref_id, $a_user_id);
		return !$info->isGranted();
	}




}