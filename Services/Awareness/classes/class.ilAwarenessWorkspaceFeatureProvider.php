<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessFeatureProvider.php");

/**
 * Adds link to shared resources feature
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessWorkspaceFeatureProvider extends ilAwarenessFeatureProvider
{
	/**
	 * Collect all features
	 *
	 * @param int $a_target_user target user
	 * @return ilAwarenessUserCollection collection
	 */
	function collectFeaturesForTargetUser($a_target_user)
	{
		global $ilCtrl;

		$coll = ilAwarenessFeatureCollection::getInstance();
		include_once("./Services/Awareness/classes/class.ilAwarenessFeature.php");

		// todo add checks
		if (false)
		{
			return $coll;
		}

		$f = new ilAwarenessFeature();

		// todo translate
		$f->setText("Shared Resources");
		//wsp_id=1&cmd=shareFilter&cmdClass=ilobjworkspacerootfoldergui&cmdNode=ph:pj:9t&baseClass=ilPersonalDesktopGUI
		$ilCtrl->setParameterByClass("ilobjworkspacerootfoldergui", "user", ilObjUser::_lookupLogin($a_target_user));
		$f->setHref($ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilpersonalworkspacegui", "ilobjworkspacerootfoldergui"),
			"listSharedResourcesOfOtherUser"));
		$coll->addFeature($f);

		return $coll;
	}
}
?>