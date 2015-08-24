<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessUserProvider.php");

/**
 * All approved contacts listed
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserProviderApprovedContacts extends ilAwarenessUserProvider
{
	/**
	 * Get provider id
	 *
	 * @return string provider id
	 */
	function getProviderId()
	{
		return "contact_approved";
	}

	/**
	 * Provider title (used in awareness overlay and in administration settings)
	 *
	 * @return string provider title
	 */
	function getTitle()
	{
		$this->lng->loadLanguageModule("contact");
		return $this->lng->txt("contact_awrn_ap_contacts");
	}

	/**
	 * Provider info (used in administration settings)
	 *
	 * @return string provider info text
	 */
	function getInfo()
	{
		$this->lng->loadLanguageModule("contact");
		return $this->lng->txt("contact_awrn_ap_contacts_info");
	}

	/**
	 * Get initial set of users
	 *
	 * @return array array of user IDs
	 */
	function getInitialUserSet()
	{
		global $ilDB;

		// currently a dummy implementation
		// finally all approved contacts of $this->getUserId() should be returned

		$ub = array();
		$set = $ilDB->query("SELECT usr_id FROM usr_data ");
		while ($rec = $ilDB->fetchAssoc($set))
		{
			if (in_array(ilObjUser::_lookupPref($rec["usr_id"], "public_profile"),
				array("y", "g")))
			{
				$ub[] = $rec["usr_id"];
			}
		}
		return $ub;
	}
}
?>