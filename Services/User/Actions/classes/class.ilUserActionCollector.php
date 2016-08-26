<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Collects actions from all action providers
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserActionCollector
{
	protected static $instances = array();

	/**
	 * @var ilUserActionCollection
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
	 * @return ilUserActionCollector
	 */
	static function getInstance($a_user_id)
	{
		if (!isset(self::$instances[$a_user_id]))
		{
			self::$instances[$a_user_id] = new ilUserActionCollector($a_user_id);
		}

		return self::$instances[$a_user_id];
	}

	/**
	 * Collect actions
	 *
	 * @return ilUserActionCollection action
	 */
	public function getActionsForTargetUser($a_target_user, $a_context_component_id, $a_context_id)
	{
		// overall collection of users
		include_once("./Services/User/Actions/classes/class.ilUserActionCollection.php");
		$this->collection = ilUserActionCollection::getInstance();

		include_once("./Services/User/Actions/classes/class.ilUserActionAdmin.php");

		include_once("./Services/User/Actions/classes/class.ilUserActionProviderFactory.php");
		foreach (ilUserActionProviderFactory::getAllProviders() as $prov)
		{
			$prov->setUserId($this->user_id);
			$coll = $prov->collectActionsForTargetUser($a_target_user);
			foreach ($coll->getActions() as $action)
			{
				if (ilUserActionAdmin::lookupActive($a_context_component_id, $a_context_id, $prov->getComponentId(), $action->getType()))
				{
					$this->collection->addAction($action);
				}
			}
		}

		return $this->collection;
	}


}

?>