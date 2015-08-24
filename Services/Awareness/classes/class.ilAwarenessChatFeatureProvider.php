<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Awareness/classes/class.ilAwarenessFeatureProvider.php");

/**
 * Adds link to chat feature
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessChatFeatureProvider extends ilAwarenessFeatureProvider
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

		include_once './Modules/Chatroom/classes/class.ilObjChatroom.php';

		$coll = ilAwarenessFeatureCollection::getInstance();
		include_once("./Services/Awareness/classes/class.ilAwarenessFeature.php");

		// todo optimize
		$chatSettings = new ilSetting('chatroom');
		$chat_enabled = $chatSettings->get('chat_enabled');
$chat_enabled = true;
		if(!$chat_enabled)
		{
			return $coll;
		}

		// todo this looks strange
//		if ($a_target_user == $this->getUserId() &&
//			$rbacsystem->checkAccessOfUser($this->getUserId(), 'read', ilObjChatroom::_getPublicRefId()))
		if (true)
		{
			$f = new ilAwarenessFeature();
			$f->setText($this->lng->txt('chat_enter_public_room'));
			$f->setHref('./ilias.php?baseClass=ilRepositoryGUI&cmd=view&ref_id='.ilObjChatroom::_getPublicRefId());
			$coll->addFeature($f);
		}

		return $coll;
	}
}
?>