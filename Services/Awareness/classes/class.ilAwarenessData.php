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
	protected $filter = "";

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
	 * Set filter
	 *
	 * @param string $a_val filter string	
	 */
	function setFilter($a_val)
	{
		$this->filter = $a_val;
	}
	
	/**
	 * Get filter
	 *
	 * @return string filter string
	 */
	function getFilter()
	{
		return $this->filter;
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
	 * Get user counter
	 *
	 * @param
	 * @return
	 */
	function getUserCounter()
	{
		$this->getData(true);
		$this->data = null;		// todo improve
		return count($this->all_user_ids);
	}

	/**
	 * Get data
	 *
	 * @return array array of data objects
	 */
	function getData($a_counter_only = false)
	{
		if ($this->data == null)
		{
			$this->all_user_ids = array();
			$online_users = ilAwarenessUserCollector::getOnlineUsers();

			$this->user_collector->setRefId($this->getRefId());
			$this->user_collections = $this->user_collector->collectUsers();

			$this->data = array();
			foreach ($this->user_collections as $uc)
			{
				$user_collection = $uc["collection"];
				$user_ids = $user_collection->getUsers();

				foreach ($user_ids as $uid)
				{
					if (!in_array($uid, $this->all_user_ids))
					{
						$this->all_user_ids[] = $uid;
					}
				}
				if ($a_counter_only)
				{
					continue;
				}

				include_once("./Services/User/classes/class.ilUserUtil.php");
				$names = ilUserUtil::getNamePresentation($user_ids, true,
					false, "", false, false, true, true);

				// sort and add online information
				foreach ($names as $k => $n)
				{
					if (isset($online_users[$n["id"]]))
					{
						$names[$k]["online"] = true;
						$names[$k]["last_login"] = $online_users[$n["id"]]["last_login"];
						$sort_str = "1";
					}
					else
					{
						$names[$k]["online"] = false;
						$names[$k]["last_login"] = "";
						$sort_str = "2";
					}
					if ($n["public_profile"])
					{
						$sort_str.= $n["lastname"]." ".$n["firstname"];
					}
					else
					{
						$sort_str.= $n["login"];
					}
					$names[$k]["sort_str"] = $sort_str;
				}

				$names = ilUtil::sortArray($names, "sort_str", "asc", false, true);

				// todo: use adv data types with a PHP object (stdClass) bridge that is transferable to JSON in a trivial manner

				foreach ($names as $n)
				{
					// filter
					$filter = trim($this->getFilter());
					if ($filter != "" &&
						!is_int(stripos($n["login"], $filter)) &&
						(!$n["public_profile"] || (
							!is_int(stripos($n["firstname"], $filter)) &&
							!is_int(stripos($n["lastname"], $filter))
							)
						)
					)
					{
						continue;
					}

					$obj = new stdClass;
					$obj->lastname = $n["lastname"];
					$obj->firstname = $n["firstname"];
					$obj->login = $n["login"];
					$obj->id = $n["id"];
					$obj->collector = $uc["uc_title"];

					//$obj->img = $n["img"];
					$obj->img = ilObjUser::_getPersonalPicturePath($n["id"], "xsmall");
					$obj->public_profile = $n["public_profile"];

					$obj->online = $n["online"];
					$obj->last_login = $n["last_login"];;

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

					$this->data[] = $obj;
				}
			}
		}

		return $this->data;
	}

}

?>