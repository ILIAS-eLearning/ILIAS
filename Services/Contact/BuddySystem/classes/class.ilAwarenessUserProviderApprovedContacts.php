<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessUserProvider.php");

/**
 * All approved contacts listed
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Michael Jansen <mjansen@databay.de>
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
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		if($ilUser->isAnonymous())
		{
			return array();
		}

		require_once 'Services/Contact/BuddySystem/classes/class.ilBuddyList.php';
		$buddylist = ilBuddyList::getInstanceByGlobalUser();
		return $buddylist->getLinkedRelations()->getKeys();
	}
}
?>