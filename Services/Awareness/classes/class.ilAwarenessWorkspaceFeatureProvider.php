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
	protected $wsp_activated;

	/**
	 * Construct
	 *
	 * @param
	 * @return
	 */
	function __construct()
	{
		global $lng, $ilSetting;

		$this->wsp_activated = (!$ilSetting->get("disable_personal_workspace"));
		$lng->loadLanguageModule("wsp");
		parent::__construct();
	}


	/**
	 * Collect all features
	 *
	 * @param int $a_target_user target user
	 * @return ilAwarenessUserCollection collection
	 */
	function collectFeaturesForTargetUser($a_target_user)
	{
		global $ilCtrl, $lng;

		$coll = ilAwarenessFeatureCollection::getInstance();
		include_once("./Services/Awareness/classes/class.ilAwarenessFeature.php");

		if (!$this->wsp_activated)
		{
			return $coll;
		}

		$f = new ilAwarenessFeature();
		$f->setText($lng->txt("wsp_shared_resources"));
		$ilCtrl->setParameterByClass("ilobjworkspacerootfoldergui", "user", ilObjUser::_lookupLogin($a_target_user));
		$f->setHref($ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilpersonalworkspacegui", "ilobjworkspacerootfoldergui"),
			"listSharedResourcesOfOtherUser"));

		//$f->setData(array("test" => "you", "user" => $a_target_user));

		$coll->addFeature($f);

		return $coll;
	}
}
?>