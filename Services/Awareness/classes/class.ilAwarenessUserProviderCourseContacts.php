<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessUserProvider.php");

/**
 * All course contacts listed
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderCourseContacts extends ilAwarenessUserProvider
{
	/**
	 * Collect all users
	 *
	 * @return ilAwarenessUserCollection collection
	 */
	function collectUsers()
	{
		include_once("./Services/Membership/classes/class.ilParticipants.php");
		$support_contacts = ilParticipants::_getAllSupportContactsOfUser($this->getUserId(), "crs");

		$coll = ilAwarenessUserCollection::getInstance();

		foreach ($support_contacts as $c)
		{
			$coll->addUser($c["usr_id"]);
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