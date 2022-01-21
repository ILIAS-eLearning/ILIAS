<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

class ilSessionReminderCheck
{
    public function getJsonResponse(string $sessionIdHash) : string
    {
        /**
         * @var $ilDB            ilDBInterface
         * @var $ilClientIniFile ilIniFile
         * @var $lng             ilLanguage
         */
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $lng = $DIC['lng'];
        $ilClientIniFile = $DIC['ilClientIniFile'];

        $GLOBALS['DIC']->logger()->auth()->debug('Session reminder call for session id hash: ' . $sessionIdHash);

        // disable session writing and extension of expiration time
        ilSession::enableWebAccessWithoutSession(true);

        $response = ['remind' => false];

        $res = $ilDB->queryF(
            '
                SELECT expires, user_id, data
                FROM usr_session
                WHERE MD5(session_id) = %s
            ',
            ['text'],
            [$sessionIdHash]
        );

        $num = $ilDB->numRows($res);

        if (0 === $num) {
            $response['message'] = 'ILIAS could not determine the session data.';
            return json_encode($response, JSON_THROW_ON_ERROR);
        }

        if ($num > 1) {
            $response['message'] = 'The determined session data is not unique.';
            return json_encode($response, JSON_THROW_ON_ERROR);
        }

        $data = $ilDB->fetchAssoc($res);
        if (!$this->isAuthenticatedUsrSession($data)) {
            $response['message'] = 'ILIAS could not fetch the session data or the corresponding user is no more authenticated.';
            return json_encode($response, JSON_THROW_ON_ERROR);
        }

        $expirationTime = (int) $data['expires'];
        if (null === $expirationTime) {
            $response['message'] = 'ILIAS could not determine the expiration time from the session data.';
            return json_encode($response, JSON_THROW_ON_ERROR);
        }

        if ($this->isSessionAlreadyExpired($expirationTime)) {
            $response['message'] = 'The session is already expired. The client should have received a remind command before.';
            return json_encode($response, JSON_THROW_ON_ERROR);
        }

        /** @var $user ilObjUser */
        $ilUser = ilObjectFactory::getInstanceByObjId((int) $data['user_id']);

        $reminderTime = $expirationTime - ((int) max(
            ilSessionReminder::MIN_LEAD_TIME,
            (float) $ilUser->getPref('session_reminder_lead_time')
        )) * 60;
        if ($reminderTime > time()) {
            // session will expire in <lead_time> minutes
            $response['message'] = 'Lead time not reached, yet. Current time: ' .
                date('Y-m-d H:i:s', time()) . ', Reminder time: ' . date('Y-m-d H:i:s', $reminderTime);
            return json_encode($response, JSON_THROW_ON_ERROR);
        }

        $dateTime = new ilDateTime($expirationTime, IL_CAL_UNIX);
        switch ($ilUser->getTimeFormat()) {
            case ilCalendarSettings::TIME_FORMAT_12:
                $formatted_expiration_time = $dateTime->get(IL_CAL_FKT_DATE, 'g:ia', $ilUser->getTimeZone());
                break;

            case ilCalendarSettings::TIME_FORMAT_24:
            default:
                $formatted_expiration_time = $dateTime->get(IL_CAL_FKT_DATE, 'H:i', $ilUser->getTimeZone());
                break;
        }

        $response = [
            'extend_url' => './ilias.php?baseClass=ilDashboardGUI',
            'txt' => str_replace(
                "\\n",
                '%0A',
                sprintf(
                    $lng->txt('session_reminder_alert'),
                    ilDatePresentation::secondsToString($expirationTime - time()),
                    $formatted_expiration_time,
                    $ilClientIniFile->readVariable('client', 'name') . ' | ' . ilUtil::_getHttpPath()
                )
            ),
            'remind' => true
        ];

        return json_encode($response, JSON_THROW_ON_ERROR);
    }

    protected function isSessionAlreadyExpired(int $expirationTime) : bool
    {
        return $expirationTime < time();
    }

    protected function isAuthenticatedUsrSession(?array $data) : bool
    {
        return (
            is_array($data) &&
            isset($data['user_id']) &&
            (int) $data['user_id'] > 0 &&
            (int) $data['user_id'] !== ANONYMOUS_USER_ID
        );
    }
}
