<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * High level business class, interface to front ends
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessAct
{
	protected static $instances = array();
	protected $user_id;
	protected static $collector;

	/**
	 * Constructor
	 *
	 * @param int $a_user_id user ud
	 */
	protected function __construct($a_user_id)
	{
		$this->user_id = $a_user_id;
	}

	/**
	 * Get instance (for a user)
	 *
	 * @param int $a_user_id user id
	 * @return ilAwarenessAct actor class
	 */
	static function getInstance($a_user_id)
	{
		if (!isset(self::$instances[$a_user_id]))
		{
			self::$instances[$a_user_id] = new ilAwarenessAct($a_user_id);
		}

		return self::$instances[$a_user_id];
	}

	/**
	 * Get awareness data
	 *
	 * @return ilAwarenessData awareness data
	 */
	function getAwarenessData()
	{
		include_once("./Services/Awareness/classes/class.ilAwarenessData.php");
		$data = ilAwarenessData::getInstance($this->user_id);
		return $data->getData();
	}

	/**
	 *
	 *
	 * @param
	 * @return
	 */
	function notifyOnNewOnlineContacts()
	{
		global $lng;

		$ts = ilSession::get("awr_online_user_ts");

		$data = ilAwarenessData::getInstance($this->user_id);
		$d = $data->getData();

		$new_online_users = array();
		foreach ($d as $u)
		{
			if ($ts == "" || $u->last_login > $ts)
			{
				$new_online_users[] = $u->firstname." ".$u->lastname;
			}
		}

		if (count($new_online_users) == 0)
		{
			return;
		}
//var_dump($d); exit;
		$lng->loadLanguageModule('mail');

		include_once("./Services/Object/classes/class.ilObjectFactory.php");
		//$recipient = ilObjectFactory::getInstanceByObjId($this->user_id);
		$bodyParams = array(
			'online_user_names'         => implode(", ", $new_online_users)
		);
//var_dump($bodyParams); exit;
		require_once 'Services/Notifications/classes/class.ilNotificationConfig.php';
		$notification = new ilNotificationConfig('osd_main');
		$notification->setTitleVar('awareness_now_online', $bodyParams, 'awrn');
		$notification->setShortDescriptionVar('awareness_now_online_users', $bodyParams, 'awrn');
		$notification->setLongDescriptionVar('', $bodyParams, '');
		$notification->setAutoDisable(false);
		//$notification->setLink();
		$notification->setIconPath('templates/default/images/icon_usr.svg');
		$notification->setValidForSeconds(0);

		//$notification->setHandlerParam('mail.sender', $sender_id);

		ilSession::set("awr_online_user_ts", date("Y-m-d H:i:s", time()));

		$notification->notifyByUsers(array($this->user_id));
	}


}

?>