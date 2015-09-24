<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Collects features from all feature providers
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessFeatureCollector
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
	 * @param int $a_user_id user id (usually the current user logged in)
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
			self::$instances[$a_user_id] = new ilAwarenessFeatureCollector($a_user_id);
		}

		return self::$instances[$a_user_id];
	}

	/**
	 * Collect users
	 *
	 * @return ilAwarenessUserCollection user collection
	 */
	public function getFeaturesForTargetUser($a_target_user)
	{
		// overall collection of users
		include_once("./Services/Awareness/classes/class.ilAwarenessFeatureCollection.php");
		$this->collection = ilAwarenessFeatureCollection::getInstance();

		include_once("./Services/Awareness/classes/class.ilAwarenessFeatureProviderFactory.php");
		foreach (ilAwarenessFeatureProviderFactory::getAllProviders() as $prov)
		{
			$prov->setUserId($this->user_id);
			$coll = $prov->collectFeaturesForTargetUser($a_target_user);
			foreach ($coll->getFeatures() as $feature)
			{
				$this->collection->addFeature($feature);
			}
		}

		return $this->collection;
	}


}

?>