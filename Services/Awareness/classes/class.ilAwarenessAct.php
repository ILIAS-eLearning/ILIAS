<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * High level business class, interface to front ends
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessAct
{
	protected static $instances = array();
	protected $user_id;
	protected static $collector;

	/**
	 * Constructor
	 *
	 * @param int $a_user_id user ud
	 */
	protected function __construct($a_user_id)
	{
		$this->user_id = $a_user_id;
		include_once("./Services/Awareness/classes/class.ilAwarenessCollector.php");
		$this->collector = ilAwarenessCollector::getInstance($a_user_id);
	}

	/**
	 * Get instance (for a user)
	 *
	 * @param int $a_user_id user id
	 * @return ilAwarenessAct actor class
	 */
	static function getInstance($a_user_id)
	{
		if (!isset(self::$instances[$a_user_id]))
		{
			self::$instances[$a_user_id] = new ilAwarenessAct($a_user_id);
		}

		return self::$instances[$a_user_id];
	}

	/**
	 * Get awareness data
	 *
	 * @return
	 */
	function getAwarenessData()
	{
		$coll = $this->collector->collectUsers();
		include_once("./Services/Awareness/classes/class.ilAwarenessData.php");
		$data = new ilAwarenessData();
		$data->setUserCollection($coll);
		return $data->getData();
	}

}

?>