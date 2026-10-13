<?php

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

declare(strict_types=1);

use ILIAS\Authentication\SessionReminder\TokenCipher;
use ILIAS\Data\Clock\ClockInterface;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\ResponseHeader;
use Psr\Http\Message\ResponseInterface;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

readonly class ilSessionReminderCheck
{
    public function __construct(
        private GlobalHttpState $http,
        private Refinery $refinery,
        private ilLanguage $lng,
        private ilDBInterface $db,
        private ilIniFile $client_ini,
        private ilLogger $logger,
        private ClockInterface $clock,
        private ilSetting $settings,
        private ?TokenCipher $token_cipher
    ) {
    }

    public function handle(): ResponseInterface
    {
        // disable session writing and extension of expiration time
        ilSession::enableWebAccessWithoutSession(true);

        $response = ['remind' => false];

        if ($this->token_cipher === null) {
            $this->logger->warning('Session reminder call without encryption key, please run the ILIAS setup (update).');
            $response['message'] = 'ILIAS could not decrypt the session reminder token.';
            return $this->toJsonResponse($response);
        }

        $token = $this->http->wrapper()->post()->retrieve(
            'token',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );

        // Invalid tokens are rejected before the database is accessed
        $token_data = $token !== '' ? $this->token_cipher->decode($token) : null;
        if ($token_data === null || $token_data['user_id'] <= 0) {
            $this->logger->debug('Session reminder call with missing or invalid token');
            $response['message'] = 'ILIAS could not decrypt the session reminder token.';
            return $this->toJsonResponse($response);
        }

        $user_id = $token_data['user_id'];
        $hash = $token_data['session_hash'];

        $this->logger->debug('Session reminder call for user ' . $user_id . ' and session id hash: ' . $hash);

        // The hash is compared in PHP: Hashing in the query would have to be done for every row by the database server
        $res = $this->db->queryF(
            'SELECT usess.session_id, usess.expires, usess.user_id, od.create_date ' .
            'FROM usr_session usess ' .
            'INNER JOIN object_data od ON od.obj_id = usess.user_id ' .
            'WHERE usess.user_id = %s',
            [ilDBConstants::T_INTEGER],
            [$user_id]
        );

        $matches = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $row_hash = hash(
                'sha256',
                implode('', [
                    $row['session_id'],
                    $row['user_id'],
                    $row['create_date']
                ])
            );
            if (hash_equals($row_hash, $hash)) {
                $matches[] = $row;
            }
        }

        if ($matches === []) {
            $response['message'] = 'ILIAS could not determine the session data.';
            return $this->toJsonResponse($response);
        }

        if (count($matches) > 1) {
            $response['message'] = 'The determined session data is not unique.';
            return $this->toJsonResponse($response);
        }

        $data = $matches[0];
        if (!$this->isAuthenticatedUsrSession($data)) {
            $response['message'] = 'ILIAS could not fetch the session data or the corresponding user is no more authenticated.';
            return $this->toJsonResponse($response);
        }

        $expiration_time = $data['expires'];
        if ($expiration_time === null) {
            $response['message'] = 'ILIAS could not determine the expiration time from the session data.';
            return $this->toJsonResponse($response);
        }
        $expiration_time = (int) $data['expires'];

        if ($this->isSessionAlreadyExpired($expiration_time)) {
            $response['message'] = 'The session is already expired. The client should have received a remind command before.';
            return $this->toJsonResponse($response);
        }

        /** @var ilObjUser $user */
        $ilUser = ilObjectFactory::getInstanceByObjId((int) $data['user_id'], false);
        if (!($ilUser instanceof ilObjUser)) {
            $response['message'] = 'ILIAS could not fetch the session data or the corresponding user is no more authenticated.';
            return $this->toJsonResponse($response);
        }

        $session_reminder = new ilSessionReminder(
            $ilUser,
            $this->clock,
            $this->settings
        );
        $reminder_time = $expiration_time - ($session_reminder->getEffectiveLeadTime() * 60);
        if ($reminder_time > $this->clock->now()->getTimestamp()) {
            // session will expire in <lead_time> minutes
            $response['message'] = 'Lead time not reached, yet. Current time: ' .
                date('Y-m-d H:i:s') . ', Reminder time: ' . date('Y-m-d H:i:s', $reminder_time);
            return $this->toJsonResponse($response);
        }

        $dateTime = new ilDateTime($expiration_time, IL_CAL_UNIX);
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
            'extend_url' => './ilias.php?baseClass=' . ilDashboardGUI::class,
            'txt' => str_replace(
                "\\n",
                '%0A',
                sprintf(
                    $this->lng->txt('session_reminder_alert'),
                    ilDatePresentation::secondsToString($expiration_time - $this->clock->now()->getTimestamp()),
                    $formatted_expiration_time,
                    $this->client_ini->readVariable('client', 'name') . ' | ' . ilUtil::_getHttpPath()
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
    private function toJsonResponse($data): ResponseInterface
    {
        return $this->http->response()
            ->withHeader(ResponseHeader::CONTENT_TYPE, 'application/json')
            ->withBody(Streams::ofString(json_encode($data, JSON_THROW_ON_ERROR)));
    }

    private function isSessionAlreadyExpired(int $expirationTime): bool
    {
        return $expirationTime < $this->clock->now()->getTimestamp();
    }

    private function isAuthenticatedUsrSession(?array $data): bool
    {
        return (
            is_array($data) &&
            isset($data['user_id']) &&
            (int) $data['user_id'] > 0 &&
            (int) $data['user_id'] !== ANONYMOUS_USER_ID
        );
    }
}
