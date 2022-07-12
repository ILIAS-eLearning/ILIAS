<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Data\Clock\ClockInterface;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\ResponseHeader;
use Psr\Http\Message\ResponseInterface;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

class ilSessionReminderCheck
{
    private GlobalHttpState $http;
    private Refinery $refinery;
    private ilLanguage $lng;
    private ilDBInterface $db;
    private ilIniFile $clientIni;
    private ilLogger $logger;
    private ClockInterface $clock;

    public function __construct(
        GlobalHttpState $http,
        Refinery $refinery,
        ilLanguage $lng,
        ilDBInterface $db,
        ilIniFile $clientIni,
        ilLogger $logger,
        ClockInterface $utcClock
    ) {
        $this->http = $http;
        $this->refinery = $refinery;
        $this->lng = $lng;
        $this->db = $db;
        $this->clientIni = $clientIni;
        $this->logger = $logger;
        $this->clock = $utcClock;
    }

    public function handle() : ResponseInterface
    {
        $sessionIdHash = ilUtil::stripSlashes(
            $this->http->wrapper()->post()->retrieve(
                'hash',
                $this->refinery->kindlyTo()->string()
            )
        );

        $this->logger->debug('Session reminder call for session id hash: ' . $sessionIdHash);

        // disable session writing and extension of expiration time
        ilSession::enableWebAccessWithoutSession(true);

        $response = ['remind' => false];

        $res = $this->db->queryF(
            'SELECT expires, user_id, data FROM usr_session WHERE MD5(session_id) = %s',
            ['text'],
            [$sessionIdHash]
        );

        $num = $this->db->numRows($res);

        if (0 === $num) {
            $response['message'] = 'ILIAS could not determine the session data.';
            return $this->toJsonResponse($response);
        }

        if ($num > 1) {
            $response['message'] = 'The determined session data is not unique.';
            return $this->toJsonResponse($response);
        }

        $data = $this->db->fetchAssoc($res);
        if (!$this->isAuthenticatedUsrSession($data)) {
            $response['message'] = 'ILIAS could not fetch the session data or the corresponding user is no more authenticated.';
            return $this->toJsonResponse($response);
        }

        $expirationTime = (int) $data['expires'];
        if (null === $expirationTime) {
            $response['message'] = 'ILIAS could not determine the expiration time from the session data.';
            return $this->toJsonResponse($response);
        }

        if ($this->isSessionAlreadyExpired($expirationTime)) {
            $response['message'] = 'The session is already expired. The client should have received a remind command before.';
            return $this->toJsonResponse($response);
        }

        /** @var ilObjUser $user */
        $ilUser = ilObjectFactory::getInstanceByObjId((int) $data['user_id'], false);
        if (!($ilUser instanceof ilObjUser)) {
            $response['message'] = 'ILIAS could not fetch the session data or the corresponding user is no more authenticated.';
            return $this->toJsonResponse($response);
        }

        $reminderTime = $expirationTime - ((int) max(
            ilSessionReminder::MIN_LEAD_TIME,
            (float) $ilUser->getPref('session_reminder_lead_time')
        )) * 60;
        if ($reminderTime > $this->clock->now()->getTimestamp()) {
            // session will expire in <lead_time> minutes
            $response['message'] = 'Lead time not reached, yet. Current time: ' .
                date('Y-m-d H:i:s') . ', Reminder time: ' . date('Y-m-d H:i:s', $reminderTime);
            return $this->toJsonResponse($response);
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
                    $this->lng->txt('session_reminder_alert'),
                    ilDatePresentation::secondsToString($expirationTime - $this->clock->now()->getTimestamp()),
                    $formatted_expiration_time,
                    $this->clientIni->readVariable('client', 'name') . ' | ' . ilUtil::_getHttpPath()
                )
            ),
            'remind' => true
        ];

        return $this->toJsonResponse($response);
    }

    /**
     * @param mixed $data
     * @return ResponseInterface
     */
    private function toJsonResponse($data) : ResponseInterface
    {
        return $this->http->response()
            ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
            ->withBody(Streams::ofString(json_encode($data, JSON_THROW_ON_ERROR)));
    }

    private function isSessionAlreadyExpired(int $expirationTime) : bool
    {
        return $expirationTime < $this->clock->now()->getTimestamp();
    }

    private function isAuthenticatedUsrSession(?array $data) : bool
    {
        return (
            is_array($data) &&
            isset($data['user_id']) &&
            (int) $data['user_id'] > 0 &&
            (int) $data['user_id'] !== ANONYMOUS_USER_ID
        );
    }
}
