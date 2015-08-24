<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessFeatureProvider.php");

/**
 * Adds link to mail feature
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessMailFeatureProvider extends ilAwarenessFeatureProvider
{
	static $user_access = array();

	/**
	 * Check user chat access
	 *
	 * @param
	 * @return
	 */
	function checkUserMailAccess($a_user_id)
	{
		global $rbacsystem;

		if (!isset(self::$user_access[$a_user_id]))
		{
			include_once("./Services/Mail/classes/class.ilMailGlobalServices.php");
			self::$user_access[$a_user_id] =
				$rbacsystem->checkAccessOfUser($a_user_id, 'internal_mail', ilMailGlobalServices::getMailObjectRefId());
		}
		return self::$user_access[$a_user_id];
	}

	/**
	 * Collect all features
	 *
	 * @param int $a_target_user target user
	 * @return ilAwarenessUserCollection collection
	 */
	function collectFeaturesForTargetUser($a_target_user)
	{
		$coll = ilAwarenessFeatureCollection::getInstance();
		include_once("./Services/Awareness/classes/class.ilAwarenessFeature.php");
		include_once("./Services/Mail/classes/class.ilMailFormCall.php");

		// check mail permission of user
		if ($this->getUserId() == ANONYMOUS_USER_ID || !$this->checkUserMailAccess($this->getUserId()))
		{
			return $coll;
		}

		// check mail permission of target user
		if ($this->checkUserMailAccess($a_target_user))
		{
			$f = new ilAwarenessFeature();
			$f->setText($this->lng->txt("mail"));
			$tn = ilObjUser::_lookupName($a_target_user);
			$f->setHref(ilMailFormCall::getLinkTarget("", '', array(), array('type' => 'new', 'rcp_to' => urlencode($tn["login"]))));
			$coll->addFeature($f);
		}

		return $coll;
	}
}
?>