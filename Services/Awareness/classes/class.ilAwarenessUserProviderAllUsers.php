<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessUserProvider.php");

/**
 * Test provider, adds all users
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderAllUsers extends ilAwarenessUserProvider
{
	/**
	 * Collect all users
	 *
	 * @return ilAwarenessUserCollection collection
	 */
	function collectUsers()
	{
		global $ilDB;

		$coll = ilAwarenessUserCollection::getInstance();

		$set = $ilDB->query("SELECT usr_id FROM usr_data ");
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$coll->addUser($rec["usr_id"]);
		}

		return $coll;
	}

	/**
	 * Collect all users
	 *
	 * @return ilAwarenessUserCollection collection
	 */
	function collectOnlineUsers()
	{
		return $this->collectUsers();
	}
}
?>