<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This extended public profile class allows users to add tabs and content
 * on their personal public profiles.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilExtendedPublicProfile
{
	/**
	 * Get tabs of user
	 *
	 * @param	int		user id
	 * @return
	 */
	static function getTabsOfUser($a_user_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM usr_ext_profile_page WHERE ".
			" user_id = ".$ilDB->quote($a_user_id, "integer").
			" ORDER BY order_nr");
		$tabs = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$tabs[] = $rec;
		}
		return $tabs;
	}
}
?>
