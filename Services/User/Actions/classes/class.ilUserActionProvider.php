<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * A class that provides a collection of actions on users
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
abstract class ilUserActionProvider
{
	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilDB
	 */
	protected $db;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $DIC;

		$this->lng = $DIC->language();
		$this->db = $DIC->database();
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
	 * Collect actions for a target user
	 *
	 * @param int $a_target_user target user
	 * @return ilUserActionCollection collection of users
	 */
	abstract function collectActionsForTargetUser($a_target_user);

	/**
	 * @return string component id as defined in services.xml/module.xml
	 */
	abstract function getComponentId();

	/**
	 * @return array[string] keys must be unique action ids (strings), values should be the names of the actions (from ilLanguage)
	 */
	abstract function getActionTypes();

	/**
	 * Get js scripts
	 *
	 * @param string $a_action_type
	 * @return array
	 */
	function getJsScripts($a_action_type)
	{
		return array();
	}


}

?>