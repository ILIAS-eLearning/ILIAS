<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ServicesAuthentication
 */
class ilSessionReminderCheck
{
	/**
	 * @param int $sessionId
	 * @param int $secondsUntilReminder
	 * @return string
	 */
	public function getJsonResponse($sessionId, $secondsUntilReminder)
	{
		/**
		 * @var $ilDB ilDB
		 * @var $ilUser ilObjUser
		 */
		global $ilDB, $ilUser;

		include_once 'Services/JSON/classes/class.ilJsonUtil.php';

		// Define response array
		$response = array('remind' => false);

		$ilDB->setLimit(1);
		$res = $ilDB->queryF('
			SELECT data, last_remind_ts, user_id
			FROM usr_session
			WHERE session_id = %s
			ORDER BY expires DESC',
			array('text'),
			array(ilUtil::stripSlashes($sessionId))
		);
		$data = $ilDB->fetchAssoc($res);

		if(!$data || !$data['data'] || !$data['user_id'] || $data['user_id'] == ANONYMOUS_USER_ID)
		{
			// No data found in database, of the current user is anonymous
			return ilJsonUtil::encode($response);
		}

		if($data['last_remind_ts'] > time() - $secondsUntilReminder)
		{
			/* Reminder not necessary: There was a request (which extends the session) between the
			   start of the javascript countdown and the current time */
			return ilJsonUtil::encode($response);
		}

		// Unserialize the session
		$session = ilUtil::unserializeSession($data['data']);
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
			// No idle value found
			return ilJsonUtil::encode($response);
		}

		$expirestime = $idletime + ilSession::getIdleValue();

		if($expirestime < time())
		{
			// Session is already expired
			return ilJsonUtil::encode($response);
		}

		/**
		 * @var $user ilObjUser
		 */
		$ilUser = ilObjectFactory::getInstanceByObjId($data['user_id']);

		$ilDB->manipulateF('
			UPDATE usr_session SET last_remind_ts = %s WHERE session_id = %s',
			array('integer', 'text'),
			array(time(), $sessionId)
		);

		$response = array(
			'remind' => true,
			'seconds_until_expiration' => ilFormat::_secondsToString((float)$ilUser->getPref('session_reminder_lead_time') * 60, true),
			'current_time_string' => ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX)),
		);

		return ilJsonUtil::encode($response);
	}
}
