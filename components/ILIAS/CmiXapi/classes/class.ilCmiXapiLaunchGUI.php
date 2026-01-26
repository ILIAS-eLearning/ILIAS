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

/**
 * Class ilCmiXapiLaunchGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCmiXapiLaunchGUI
{
    public const XAPI_PROXY_ENDPOINT = 'xapiproxy.php';

    protected ilObjCmiXapi $object;

    protected ilCmiXapiUser $cmixUser;

    protected bool $plugin = false;

    private ilObjUser $user;

    private ilCtrlInterface $ctrl;

    public function __construct(ilObjCmiXapi $object)
    {
        $this->object = $object;
    }

    public function executeCommand(): void
    {
        global $DIC;
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->launchCmd();
    }

    protected function launchCmd(): void
    {
        $this->initCmixUser();
        $token = $this->getValidToken();
        if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
            $ret = $this->CMI5preLaunch($token);
            $token = $ret['token'];
        }
        $launchLink = $this->buildLaunchLink($token);
        $this->ctrl->redirectToURL($launchLink);
    }

    protected function buildLaunchLink(string $token): string
    {
        $launchLink = "";

        if ($this->object->getSourceType() == ilObjCmiXapi::SRC_TYPE_REMOTE) {
            $launchLink = $this->object->getLaunchUrl();
        } elseif ($this->object->getSourceType() == ilObjCmiXapi::SRC_TYPE_LOCAL) {
            if (preg_match("/^(https?:\/\/)/", $this->object->getLaunchUrl()) == 1) {
                $launchLink = $this->object->getLaunchUrl();
            } else {
                $launchLink = implode('/', [
                    ILIAS_HTTP_PATH,
                    ilFileUtils::getWebspaceDir(),
                    ilCmiXapiContentUploadImporter::RELATIVE_CONTENT_DIRECTORY_NAMEBASE . $this->object->getId()
                ]);

                $launchLink .= DIRECTORY_SEPARATOR . $this->object->getLaunchUrl();
            }
        }

        foreach ($this->getLaunchParameters($token) as $paramName => $paramValue) {
            $launchLink = ilUtil::appendUrlParameterString($launchLink, "{$paramName}={$paramValue}");
        }

        return $launchLink;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getLaunchParameters(string $token): array
    {
        $params = [];

        if ($this->object->isBypassProxyEnabled()) {
            $params['endpoint'] = urlencode(rtrim($this->object->getLrsType()->getLrsEndpoint(), '/') . '/');
        } else {
            $params['endpoint'] = urlencode(rtrim(ILIAS_HTTP_PATH . '/' . self::XAPI_PROXY_ENDPOINT, '/') . '/');
        }

        if ($this->object->isAuthFetchUrlEnabled()) {
            $params['fetch'] = urlencode($this->getAuthTokenFetchLink());
        } else {
            if ($this->object->isBypassProxyEnabled()) {
                $params['auth'] = urlencode($this->object->getLrsType()->getBasicAuth());
            } else {
                $params['auth'] = urlencode('Basic ' . base64_encode(
                    CLIENT_ID . ':' . $token
                ));
            }
        }

        $params['activity_id'] = urlencode($this->object->getActivityId());
        $params['activityId'] = urlencode($this->object->getActivityId());
        $params['actor'] = urlencode(json_encode($this->object->getStatementActor($this->cmixUser)));
        if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
            $registration = $this->cmixUser->getRegistration();
            // for old CMI5 Content after switch commit but before cmi5 bugfix
            if ($registration == '') {
                $registration = ilCmiXapiUser::generateRegistration($this->object, $this->user);
            }
            $params['registration'] = $registration;
        } else {
            $params['registration'] = urlencode((string) ilCmiXapiUser::generateRegistration($this->object, $this->user));
        }
        return $params;
    }

    /**
     * @throws ilCmiXapiException
     */
    protected function getAuthTokenFetchLink(): string
    {
        $link = ILIAS_HTTP_PATH . '/xapitoken.php';

        $param = $this->buildAuthTokenFetchParam();

        return iLUtil::appendUrlParameterString($link, "param={$param}");
    }

    /**
     * @throws ilCmiXapiException
     */
    protected function buildAuthTokenFetchParam(): string
    {
        $params = [
            session_name() => session_id(),
            'obj_id' => $this->object->getId(),
            'ref_id' => $this->object->getRefId(),
            'ilClientId' => CLIENT_ID
        ];

        $encryptionKey = ilCmiXapiAuthToken::getWacSalt();
        return urlencode(base64_encode(openssl_encrypt(
            json_encode($params),
            ilCmiXapiAuthToken::OPENSSL_ENCRYPTION_METHOD,
            $encryptionKey,
            0,
            ilCmiXapiAuthToken::OPENSSL_IV
        )));
    }

    protected function getValidToken(): string
    {
        $token = ilCmiXapiAuthToken::fillToken(
            $this->user->getId(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->object->getLrsType()->getTypeId()
        );
        return $token;
    }

    protected function initCmixUser(): void
    {
        $this->cmixUser = new ilCmiXapiUser($this->object->getId(), $this->user->getId(), $this->object->getPrivacyIdent());
        $user_ident = $this->cmixUser->getUsrIdent();
        if ($user_ident == '' || $user_ident == null) {
            $user_ident = ilCmiXapiUser::getIdent($this->object->getPrivacyIdent(), $this->user);
            $this->cmixUser->setUsrIdent($user_ident);

            if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
                $this->cmixUser->setRegistration((string) ilCmiXapiUser::generateCMI5Registration($this->object->getId(), $this->user->getId()));
            }
            $this->cmixUser->save();
            if (!ilObjUser::_isAnonymous($this->user->getId())) {
                ilLPStatusWrapper::_updateStatus($this->object->getId(), $this->user->getId());
            }
        }
    }

    /**
     * @return array<string, string>
     */
    protected function getCmi5LearnerPreferences(): array
    {
        $language = $this->user->getLanguage();
        $audio = "on";
        return [
            "languagePreference" => "{$language}",
            "audioPreference" => "{$audio}"
        ];
    }

    /**
     * Prelaunch
     * post cmi5LearnerPreference (agent profile)
     * post LMS.LaunchData
     * @return array<string, mixed>
     * @throws ilCmiXapiException
     * @throws ilDateTimeException
     */
    protected function CMI5preLaunch(string $token): array
    {
        global $DIC;
        $DIC->language()->loadLanguageModule("cmix");

        $duration = '';
        $lrsType = $this->object->getLrsType();
        $defaultLrs = $lrsType->getLrsEndpoint();
        $defaultBasicAuth = $lrsType->getBasicAuth();

        $defaultHeaders = [
            'X-Experience-API-Version: 1.0.3',
            'Authorization: ' . $defaultBasicAuth,
            'Content-Type: application/json;charset=utf-8',
            'Cache-Control: no-cache, no-store, must-revalidate'
        ];

        $registration = $this->cmixUser->getRegistration();
        if ($registration == '') {
            $registration = ilCmiXapiUser::generateRegistration($this->object, $this->user);
        }

        $activityId = $this->object->getActivityId();

        // Profile URL
        $profileParams = [
            'agent' => json_encode($this->object->getStatementActor($this->cmixUser)),
            'profileId' => 'cmi5LearnerPreferences'
        ];
        $defaultProfileUrl = $defaultLrs . "/agents/profile?" . ilCmiXapiAbstractRequest::buildQuery($profileParams);

        // LaunchData URL
        $launchDataParams = [
            'agent' => json_encode($this->object->getStatementActor($this->cmixUser)),
            'activityId' => $activityId,
            'activity_id' => $activityId,
            'registration' => $registration,
            'stateId' => 'LMS.LaunchData'
        ];
        $defaultLaunchDataUrl = $defaultLrs . "/activities/state?" . ilCmiXapiAbstractRequest::buildQuery($launchDataParams);

        $cmi5LearnerPreferencesObj = $this->getCmi5LearnerPreferences();
        $cmi5LearnerPreferences = json_encode($cmi5LearnerPreferencesObj);
        $lang = $cmi5LearnerPreferencesObj['languagePreference'];
        $cmi5_session = ilObjCmiXapi::guidv4();

        $tokenObject = ilCmiXapiAuthToken::getInstanceByToken($token);
        $oldSession = $tokenObject->getCmi5Session();
        $oldSessionLaunchedTimestamp = '';
        $abandoned = false;

        if ($oldSession != null && !empty($oldSession)) {
            $oldSessionData = json_decode($tokenObject->getCmi5SessionData());
            $oldSessionLaunchedTimestamp = $oldSessionData->launchedTimestamp;
            $tokenObject->delete();
            $token = $this->getValidToken();
            $tokenObject = ilCmiXapiAuthToken::getInstanceByToken($token);
            $lastStatement = $this->object->getLastStatement($oldSession);
            if (isset($lastStatement[0]['statement']['verb']['id']) &&
                $lastStatement[0]['statement']['verb']['id'] != ilCmiXapiVerbList::getInstance()->getVerbUri('terminated')) {
                $abandoned = true;
                $start = new DateTime($oldSessionLaunchedTimestamp);
                $end = new DateTime($lastStatement[0]['statement']['timestamp']);
                $diff = $end->diff($start);
                $duration = ilCmiXapiDateTime::dateIntervalToISO860Duration($diff);
            }
        }

        $satisfied = false;
        $lpMode = $this->object->getLPMode();
        if ($lpMode === ilLPObjSettings::LP_MODE_DEACTIVATED) {
            $satisfied = true;
        }

        $tokenObject->setCmi5Session($cmi5_session);
        $now = new ilCmiXapiDateTime(time(), IL_CAL_UNIX);
        $sessionData = [
            'cmi5LearnerPreferences' => $cmi5LearnerPreferencesObj,
            'launchedTimestamp' => $now->toXapiTimestamp()
        ];
        $tokenObject->setCmi5SessionData(json_encode($sessionData));
        $tokenObject->update();

        $defaultStatementsUrl = $defaultLrs . "/statements";

        // Statements
        $launchData = json_encode($this->object->getLaunchData($DIC->language()->txt('cmiexit'), $this->cmixUser));
        $launchedStatement = $this->object->getLaunchedStatement($this->cmixUser);
        $launchedStatementUrl = $defaultStatementsUrl . '?statementId=' . urlencode($launchedStatement['id']);

        $requests = [
            ['url' => $defaultProfileUrl, 'method' => 'POST', 'body' => $cmi5LearnerPreferences],
            ['url' => $defaultLaunchDataUrl, 'method' => 'PUT', 'body' => $launchData],
            ['url' => $launchedStatementUrl, 'method' => 'PUT', 'body' => json_encode($launchedStatement)]
        ];

        if ($abandoned) {
            $abandonedStatement = $this->object->getAbandonedStatement($oldSession, $duration, $this->cmixUser);
            $requests[] = [
                'url' => $defaultStatementsUrl . '?statementId=' . urlencode($abandonedStatement['id']),
                'method' => 'PUT',
                'body' => json_encode($abandonedStatement)
            ];
        }

        if ($satisfied) {
            $satisfiedStatement = $this->object->getSatisfiedStatement($this->cmixUser);
            $requests[] = [
                'url' => $defaultStatementsUrl . '?statementId=' . urlencode($satisfiedStatement['id']),
                'method' => 'PUT',
                'body' => json_encode($satisfiedStatement)
            ];
        }

        // --- Native cURL Multi ---
        $mh = curl_multi_init();
        $chs = [];

        foreach ($requests as $req) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $req['url'],
                CURLOPT_CUSTOMREQUEST => $req['method'],
                CURLOPT_HTTPHEADER => $defaultHeaders,
                CURLOPT_POSTFIELDS => $req['body'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            curl_multi_add_handle($mh, $ch);
            $chs[] = $ch;
        }

        // Execute all
        $running = 0;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
            //            usleep(10000); // 10ms Pause, schont CPU
        } while ($running > 0);

        // Collect responses
        foreach ($chs as $ch) {
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $body = curl_multi_getcontent($ch);
            if (!in_array($status, [200, 204])) {
                $this->log()->error("CMI5preLaunch HTTP error $status: $body");
            }
            curl_multi_remove_handle($mh, $ch);
            $ch = null;
        }

        curl_multi_close($mh);

        return [
            'cmi5_session' => $cmi5_session,
            'token' => $token
        ];
    }

    /**
     * @return ilLogger
     */
    private function log(): ilLogger
    {
        return \ilLoggerFactory::getLogger('cmix');
    }
}
