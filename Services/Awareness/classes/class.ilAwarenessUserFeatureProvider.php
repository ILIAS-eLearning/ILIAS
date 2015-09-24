<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessFeatureProvider.php");

/**
 * Adds link to profile feature
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessUserFeatureProvider extends ilAwarenessFeatureProvider
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

		if (!in_array(ilObjUser::_lookupPref($a_target_user, "public_profile"),
			array("y", "g")))
		{
			return $coll;
		}

		$f = new ilAwarenessFeature();
		$f->setText($this->lng->txt('profile'));
		$f->setHref("./goto.php?target=usr_".$a_target_user);
		$coll->addFeature($f);

		return $coll;
	}
}
?>