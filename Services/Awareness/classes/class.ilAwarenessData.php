<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessData
{
	protected $user_id;
	protected $ref_id = 0;
	protected $user_collector;
	protected $feature_collector;
	protected $user_collection;
	protected $data = null;
	static protected $instances = array();

	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	protected function __construct($a_user_id)
	{
		$this->user_id = $a_user_id;

		include_once("./Services/Awareness/classes/class.ilAwarenessUserCollector.php");
		$this->user_collector = ilAwarenessUserCollector::getInstance($a_user_id);
		include_once("./Services/Awareness/classes/class.ilAwarenessFeatureCollector.php");
		$this->feature_collector = ilAwarenessFeatureCollector::getInstance($a_user_id);
	}

	/**
	 * Set ref id
	 *
	 * @param int $a_val ref id	
	 */
	function setRefId($a_val)
	{
		$this->ref_id = $a_val;
	}
	
	/**
	 * Get ref id
	 *
	 * @return int ref id
	 */
	function getRefId()
	{
		return $this->ref_id;
	}
	
	/**
	 * Get instance (for a user)
	 *
	 * @param int $a_user_id user id
	 * @return ilAwarenessData actor class
	 */
	static function getInstance($a_user_id)
	{
		if (!isset(self::$instances[$a_user_id]))
		{
			self::$instances[$a_user_id] = new ilAwarenessData($a_user_id);
		}

		return self::$instances[$a_user_id];
	}



	/**
	 * Get data
	 *
	 * @return array array of data objects
	 */
	function getData()
	{
		if ($this->data == null)
		{
			$this->user_collector->setRefId($this->getRefId());
			$this->user_collection = $this->user_collector->collectUsers();

			$user_ids = $this->user_collection->getUsers();

			include_once("./Services/User/classes/class.ilUserUtil.php");
			$names = ilUserUtil::getNamePresentation($user_ids, true,
				false, "", false, false, true, true);

			// todo: some setting to control this?
//			$only_online = true;

			// todo: use adv data types with a PHP object (stdClass) bridge that is transferable to JSON in a trivial manner

			$data = array();
			foreach ($names as $n)
			{
				$obj = new stdClass;
				$obj->lastname = $n["lastname"];
				$obj->firstname = $n["firstname"];
				$obj->login = $n["login"];
				$obj->id = $n["id"];
				//$obj->img = $n["img"];
				$obj->img = ilObjUser::_getPersonalPicturePath($n["id"], "xsmall");
				$obj->public_profile = $n["public_profile"];

				if (isset($online_users[$obj->id]))
				{
					$obj->online = true;
					$obj->last_login = $online_users[$obj->id]["last_login"];
				}
				else
				{
					$obj->online = false;
					$obj->last_login = "";
				}

				// collect only online users, if desired
				if (!$only_online || $obj->online)
				{
					// get features
					$feature_collection = $this->feature_collector->getFeaturesForTargetUser($n["id"]);
					$obj->features = array();
					foreach ($feature_collection->getFeatures() as $feature)
					{
						$f = new stdClass;
						$f->text = $feature->getText();
						$f->href = $feature->getHref();
						$obj->features[] = $f;
					}

					$data[] = $obj;
				}
			}
			$this->data = $data;
		}

		return $this->data;
	}

}

?>