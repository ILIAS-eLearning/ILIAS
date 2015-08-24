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
	/**
	 * Collect all features
	 *
	 * @param int $a_target_user target user
	 * @return ilAwarenessUserCollection collection
	 */
	function collectFeaturesForTargetUser($a_target_user)
	{
		global $rbacsystem;

		$coll = ilAwarenessFeatureCollection::getInstance();
		include_once("./Services/Awareness/classes/class.ilAwarenessFeature.php");
		include_once("./Services/Mail/classes/class.ilMailFormCall.php");

		// check mail permission of user
		// todo: optimization
		$this->mail_allowed = ($this->getUserId() != ANONYMOUS_USER_ID &&
			$rbacsystem->checkAccessOfUser($this->getUserId(), 'internal_mail', ilMailGlobalServices::getMailObjectRefId()));
		if (!$this->mail_allowed)
		{
			return $coll;
		}

		// check mail permission of target user
		if ($rbacsystem->checkAccessOfUser($a_target_user,'internal_mail', ilMailGlobalServices::getMailObjectRefId()))
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