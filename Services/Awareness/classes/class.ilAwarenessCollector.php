<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Collects users from all providers
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessCollector
{
	protected static $instances = array();

	/**
	 * @var ilAwarenessUserCollection
	 */
	protected $collection;
	protected $user_id;

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
	 * Get instance (for a user)
	 *
	 * @param int $a_user_id user id
	 * @return ilAwarenessAct actor class
	 */
	static function getInstance($a_user_id)
	{
		if (!isset(self::$instances[$a_user_id]))
		{
			self::$instances[$a_user_id] = new ilAwarenessCollector($a_user_id);
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
		// overall collection of users
		include_once("./Services/Awareness/classes/class.ilAwarenessUserCollection.php");
		$this->collection = ilAwarenessUserCollection::getInstance();

		include_once("./Services/Awareness/classes/class.ilAwarenessProviderFactory.php");
		foreach (ilAwarenessProviderFactory::getAllProviders() as $prov)
		{
			$prov->setUserId($this->user_id);
			$coll = $prov->collectUsers();
			foreach ($coll->getUsers() as $user_id)
			{
				$this->collection->addUser($user_id);
			}
		}

		return $this->collection;
	}


}

?>