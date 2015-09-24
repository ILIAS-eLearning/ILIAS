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
	protected static $user_access = array();
	protected $pub_ref_id = 0;

		/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		include_once './Modules/Chatroom/classes/class.ilObjChatroom.php';
		$this->pub_ref_id = ilObjChatroom::_getPublicRefId();

		$chatSettings = new ilSetting('chatroom');
		$this->chat_enabled = $chatSettings->get('chat_enabled');
	}

	/**
	 * Check user chat access
	 *
	 * @param
	 * @return
	 */
	function checkUserChatAccess($a_user_id)
	{
		global $rbacsystem;

		if (!isset(self::$user_access[$a_user_id]))
		{
			self::$user_access[$a_user_id] =
				$rbacsystem->checkAccessOfUser($a_user_id, 'read', $this->pub_ref_id);
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

		if (!$this->chat_enabled)
		{
			return $coll;
		}

		if ($this->checkUserChatAccess($this->getUserId()))
		{
			// this check is not really needed anymore, since the current
			// user will never be listed in the awareness tool
			if ($a_target_user != $this->getUserId())
			{
				if ($this->checkUserChatAccess($a_target_user))
				{
					$f = new ilAwarenessFeature();
					$f->setText($this->lng->txt('chat_invite_public_room'));
					$f->setHref('./ilias.php?baseClass=ilRepositoryGUI&ref_id='.$this->pub_ref_id.
						'&usr_id='.$a_target_user.'&cmd=view-invitePD');
					//$this->tpl->setVariable('TXT_CHAT_INVITE_TOOLTIP', $lng->txt('chat_invite_public_room_tooltip'));
					$coll->addFeature($f);
				}
			}
		}

		return $coll;
	}
}
?>