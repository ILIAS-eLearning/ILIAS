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
         * @var $ilClientIniFile ilIniFile
         * @var $lng             ilLanguage
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $ilClientIniFile = $DIC['ilClientIniFile'];

        include_once 'Services/JSON/classes/class.ilJsonUtil.php';
        
        
        $GLOBALS['DIC']->logger()->auth()->debug('Session reminder call for: ' . $sessionId);
        
        // disable session writing and extension of expire time
        include_once './Services/Authentication/classes/class.ilSession.php';
        ilSession::enableWebAccessWithoutSession(true);

        $response = array('remind' => false);

        $res = $ilDB->queryF(
            '
			SELECT expires, user_id, data
			FROM usr_session
			WHERE session_id = %s',
            array('text'),
            array($sessionId)
        );
        
        $num = $ilDB->numRows($res);

        if ($num > 1) {
            $response['message'] = 'The determined session data is not unique.';
            return ilJsonUtil::encode($response);
        }

        if ($num == 0) {
            $response['message'] = 'ILIAS could not determine the session data.';
            return ilJsonUtil::encode($response);
        }

        $data = $ilDB->fetchAssoc($res);
        if (!$this->isAuthenticatedUsrSession($data)) {
            $response['message'] = 'ILIAS could not fetch the session data or the corresponding user is no more authenticated.';
            return ilJsonUtil::encode($response);
        }

        /**
         * @todo: php7: refactored session data; new implementation for idle time calcluation DONE
         *
         */
        $expiretime = $data['expires'];
        if ($this->isSessionAlreadyExpired($expiretime)) {
            $response['message'] = 'The session is already expired. The client should have received a remind command before.';
            return ilJsonUtil::encode($response);
        }

        if (null === $expiretime) {
            $response['message'] = 'ILIAS could not determine the expire time from the session data.';
            return ilJsonUtil::encode($response);
        }

        if ($this->isSessionAlreadyExpired($expiretime)) {
            $response['message'] = 'The session is already expired. The client should have received a remind command before.';
            return ilJsonUtil::encode($response);
        }

        /**
         * @var $user ilObjUser
         */
        $ilUser = ilObjectFactory::getInstanceByObjId($data['user_id']);

        include_once './Services/Authentication/classes/class.ilSessionReminder.php';
        $remind_time = $expiretime - max(ilSessionReminder::MIN_LEAD_TIME, (float) $ilUser->getPref('session_reminder_lead_time')) * 60;
        if ($remind_time > time()) {
            // session will expire in <lead_time> minutes
            $response['message'] = 'Lead time not reached, yet. Current time: ' . date('Y-m-d H:i:s', time()) . ', Reminder time: ' . date('Y-m-d H:i:s', $remind_time);
            return ilJsonUtil::encode($response);
        }

        $dateTime = new ilDateTime($expiretime, IL_CAL_UNIX);
        switch ($ilUser->getTimeFormat()) {
            case ilCalendarSettings::TIME_FORMAT_12:
                $formatted_expiration_time = $dateTime->get(IL_CAL_FKT_DATE, 'g:ia', $ilUser->getTimeZone());
                break;

            case ilCalendarSettings::TIME_FORMAT_24:
            default:
                $formatted_expiration_time = $dateTime->get(IL_CAL_FKT_DATE, 'H:i', $ilUser->getTimeZone());
                break;
        }

        $response = array(
            'extend_url' => './ilias.php?baseClass=ilPersonalDesktopGUI',
            'txt' => str_replace("\\n", '%0A', sprintf($lng->txt('session_reminder_alert'), ilDatePresentation::secondsToString($expiretime - time()), $formatted_expiration_time, $ilClientIniFile->readVariable('client', 'name') . ' | ' . ilUtil::_getHttpPath())),
            'remind' => true
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
