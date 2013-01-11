<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesAuthentication
 */
class ilSessionReminderCheck
{
	/**
	 * @param int $sessionId
	 * @return string
	 */
	public function getJsonResponse($sessionId)
	{
		/**
		 * @var $ilDB            ilDB
		 * @var $ilUser          ilObjUser
		 * @var $ilClientIniFile ilIniFile
		 * @var $lng             ilLanguage
		 */
		global $ilDB, $ilUser, $lng, $ilClientIniFile;

		include_once 'Services/JSON/classes/class.ilJsonUtil.php';

		$response = array('remind' => false);

		$res  = $ilDB->queryF('
			SELECT expires, user_id, data
			FROM usr_session
			WHERE session_id = %s',
			array('text'),
			array($sessionId)
		);
		$data = $ilDB->fetchAssoc($res);

		if(!$this->isAuthenticatedUsrSession($data))
		{
			return ilJsonUtil::encode($response);
		}

		$session  = ilUtil::unserializeSession($data['data']);
		$idletime = null;
		foreach((array)$session as $key => $entry)
		{
			if(strpos($key, '_auth__') === 0)
			{
				$idletime = $entry['idle'];
				break;
			}
		}

		if(null === $idletime)
		{
			return ilJsonUtil::encode($response);
		}

		$expiretime = $idletime + ilSession::getIdleValue();
		if($this->isSessionAlreadyExpired($expiretime))
		{
			return ilJsonUtil::encode($response);
		}

		/**
		 * @var $user ilObjUser
		 */
		$ilUser = ilObjectFactory::getInstanceByObjId($data['user_id']);

		include_once './Services/Authentication/classes/class.ilSessionReminder.php';
		if($expiretime - max(ilSessionReminder::MIN_LEAD_TIME, (float)$ilUser->getPref('session_reminder_lead_time')) * 60 > time())
		{
			// session will expire in <lead_time> minutes
			return ilJsonUtil::encode($response);
		}

		$dateTime = new ilDateTime($expiretime, IL_CAL_UNIX);
		switch($ilUser->getTimeFormat())
		{
			case ilCalendarSettings::TIME_FORMAT_12:
				$formatted_expiration_time = $dateTime->get(IL_CAL_FKT_DATE, 'g:ia', $ilUser->getTimeZone());
				break;

			case ilCalendarSettings::TIME_FORMAT_24:
			default:
				$formatted_expiration_time = $dateTime->get(IL_CAL_FKT_DATE, 'H:i', $ilUser->getTimeZone());
				break;
		}

		$response = array(
			'extend_url'               => './ilias.php?baseClass=ilPersonalDesktopGUI',
			'txt'                      => str_replace("\\n", '%0A', sprintf($lng->txt('session_reminder_alert'), ilFormat::_secondsToString($expiretime - time()), $formatted_expiration_time, $ilClientIniFile->readVariable('client', 'name') . ' | ' . ilUtil::_getHttpPath())),
			'remind'                   => true
		);

		return ilJsonUtil::encode($response);
	}

	/**
	 * @param int $expiretime
	 * @return bool
	 */
	protected function isSessionAlreadyExpired($expiretime)
	{
		return $expiretime < time();
	}

	/**
	 * @param array|null $data
	 * @return bool
	 */
	protected function isAuthenticatedUsrSession($data)
	{
		return is_array($data) && isset($data['user_id']) && $data['user_id'] > 0 && $data['user_id'] != ANONYMOUS_USER_ID;
	}
}
