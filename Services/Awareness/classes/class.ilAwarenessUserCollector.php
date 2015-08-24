<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Collects users from all providers
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserCollector
{
	protected static $instances = array();
	protected static $online_users = false;

	/**
	 * @var ilAwarenessUserCollection
	 */
	protected $collection;
	protected $user_id;
	protected $ref_id;

	/**
	 * Constructor
	 *
	 * @param int $a_user_id user id
	 */
	protected function __construct($a_user_id)
	{
		$this->user_id = $a_user_id;
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
	 * @return ilAwarenessAct actor class
	 */
	static function getInstance($a_user_id)
	{
		if (!isset(self::$instances[$a_user_id]))
		{
			self::$instances[$a_user_id] = new ilAwarenessUserCollector($a_user_id);
		}

		return self::$instances[$a_user_id];
	}

	/**
	 * Collect users
	 *
	 * @return ilAwarenessUserCollection user collection
	 */
	public function collectUsers()
	{
		if (self::$online_users === false)
		{
			include_once("./Services/User/classes/class.ilObjUser.php");
			foreach (ilObjUser::_getUsersOnline() as $u)
			{
				self::$online_users[] = $u["user_id"];
			}
		}

		// overall collection of users
		include_once("./Services/Awareness/classes/class.ilAwarenessUserCollection.php");
		$this->collection = ilAwarenessUserCollection::getInstance();

		include_once("./Services/Awareness/classes/class.ilAwarenessUserProviderFactory.php");
		foreach (ilAwarenessUserProviderFactory::getAllProviders() as $prov)
		{
			if ($prov->getActivationMode() != ilAwarenessUserProvider::MODE_INACTIVE)
			{
				$prov->setUserId($this->user_id);
				$prov->setRefId($this->ref_id);
				$prov->setOnlineUserFilter(false);
				if ($prov->getActivationMode() == ilAwarenessUserProvider::MODE_ONLINE_ONLY)
				{
					$prov->setOnlineUserFilter(self::$online_users);
				}
				$coll = $prov->collectUsers();
				foreach ($coll->getUsers() as $user_id)
				{
					// cross check online
					if ($prov->getActivationMode() == ilAwarenessUserProvider::MODE_INCL_OFFLINE
						|| in_array($user_id, self::$online_users))
					{
						$this->collection->addUser($user_id);
					}
				}
			}
		}

		return $this->collection;
	}


}

?>