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
		 * @var $ilDB   ilDB
		 * @var $ilUser ilObjUser
		 */
		global $ilDB, $ilUser;

		include_once 'Services/JSON/classes/class.ilJsonUtil.php';

		// Define response array
		$response = array('remind' => false);

		$res  = $ilDB->queryF('
			SELECT expires, user_id, data
			FROM usr_session
			WHERE session_id = %s',
			array('text'),
			array($sessionId)
		);
		$data = $ilDB->fetchAssoc($res);

		if(!$data || !$data['data'] || !$data['user_id'] || $data['user_id'] == ANONYMOUS_USER_ID)
		{
			// No data found in database, of the current user is anonymous
			return ilJsonUtil::encode($response);
		}

		// Unserialize the session
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
			// No idle value found
			return ilJsonUtil::encode($response);
		}

		$expiretime = $idletime + ilSession::getIdleValue();
		if($expiretime < time())
		{
			// Session is already expired
			return ilJsonUtil::encode($response);
		}

		/**
		 * @var $user ilObjUser
		 */
		$ilUser = ilObjectFactory::getInstanceByObjId($data['user_id']);

		if($expiretime - (float)$ilUser->getPref('session_reminder_lead_time') * 60 > time())
		{
			// session will expire in <lead_time> minutes
			return ilJsonUtil::encode($response);
		}

		$response = array(
			'remind'                   => true,
			'seconds_until_expiration' => ilFormat::_secondsToString((float)$ilUser->getPref('session_reminder_lead_time') * 60, true),
			'current_time_string'      => ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX)),
		);

		return ilJsonUtil::encode($response);
	}
}
