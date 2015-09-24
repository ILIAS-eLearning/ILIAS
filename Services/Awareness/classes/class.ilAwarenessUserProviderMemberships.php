<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessUserProvider.php");

/**
 * All members of the same courses/groups as the user
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderMemberships extends ilAwarenessUserProvider
{
	/**
	 * Get provider id
	 *
	 * @return string provider id
	 */
	function getProviderId()
	{
		return "mmbr_user_grpcrs";
	}

	/**
	 * Provider title (used in awareness overlay and in administration settings)
	 *
	 * @return string provider title
	 */
	function getTitle()
	{
		$this->lng->loadLanguageModule("mmbr");
		return $this->lng->txt("mmbr_awrn_my_groups_courses");
	}

	/**
	 * Provider info (used in administration settings)
	 *
	 * @return string provider info text
	 */
	function getInfo()
	{
		$this->lng->loadLanguageModule("crs");
		return $this->lng->txt("mmbr_awrn_my_groups_courses_info");
	}

	/**
	 * Get initial set of users
	 *
	 * @return array array of user IDs
	 */
	function getInitialUserSet()
	{
		global $ilDB;


		include_once("./Services/Membership/classes/class.ilParticipants.php");
		$groups_and_courses_of_user = ilParticipants::_getMembershipByType($this->getUserId(), array("grp", "crs"));

		$set = $ilDB->query("SELECT DISTINCT usr_id FROM obj_members ".
			" WHERE ".$ilDB->in("obj_id", $groups_and_courses_of_user, false, "integer"));
		$ub = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$ub[] = $rec["usr_id"];
		}
		return $ub;
	}
}
?>