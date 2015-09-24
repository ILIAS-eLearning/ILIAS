<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * A class that provides a collection of features for the awareness tool
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
abstract class ilAwarenessFeatureProvider
{
	protected $user_id;
	protected $lng;
	protected $db;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $lng, $ilDB;

		$this->lng = $lng;
		$this->db = $ilDB;
	}

	/**
	 * Set user id
	 *
	 * @param int $a_val user id
	 */
	function setUserId($a_val)
	{
		$this->user_id = $a_val;
	}

	/**
	 * Get user id
	 *
	 * @return int user id
	 */
	function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * Collect features for a target user
	 *
	 * @param int $a_target_user target user
	 * @return ilAwarenessFeatureCollection collection of users
	 */
	abstract function collectFeaturesForTargetUser($a_target_user);

}

?>