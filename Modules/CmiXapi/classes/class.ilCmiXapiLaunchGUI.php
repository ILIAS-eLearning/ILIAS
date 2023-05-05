<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    const XAPI_PROXY_ENDPOINT = 'Modules/CmiXapi/xapiproxy.php';
    
    /**
     * @var ilObjCmiXapi
     */
    protected $object;
    
    /**
     * @var ilCmiXapiUser
     */
    protected $cmixUser;

    /**
     * @var bool
     */
    protected $plugin = false;
    
    /**
     * @param ilObjCmiXapi $object
     */
    public function __construct(ilObjCmiXapi $object)
    {
        $this->object = $object;
    }
    
    public function executeCommand()
    {
        $this->launchCmd();
    }
    
    protected function launchCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->initCmixUser();
        $token = $this->getValidToken();
        if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
            $ret = $this->CMI5preLaunch($token);
            $token = $ret['token'];
        }
        $launchLink = $this->buildLaunchLink($token);
        $DIC->ctrl()->redirectToURL($launchLink);
    }
    
    protected function buildLaunchLink($token)
    {
        if ($this->object->getSourceType() == ilObjCmiXapi::SRC_TYPE_REMOTE) {
            $launchLink = $this->object->getLaunchUrl();
        } elseif ($this->object->getSourceType() == ilObjCmiXapi::SRC_TYPE_LOCAL) {
            if (preg_match("/^(https?:\/\/)/", $this->object->getLaunchUrl()) == 1) {
                $launchLink = $this->object->getLaunchUrl();
            } else {
                $launchLink = implode('/', [
                    ILIAS_HTTP_PATH, ilUtil::getWebspaceDir(),
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
    
    protected function getLaunchParameters($token)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $params = [];
        
        if ($this->object->isBypassProxyEnabled()) {
            $params['endpoint'] = urlencode(rtrim($this->object->getLrsType()->getLrsEndpoint(), '/') . '/');
        } else {
            $link = ILIAS_HTTP_PATH;
            if (in_array((int) $_SERVER['SERVER_PORT'], [80, 443])) {
                $link = str_replace($_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'], $_SERVER['SERVER_NAME'], $link);
            }
            $params['endpoint'] = urlencode(rtrim($link . '/' . self::XAPI_PROXY_ENDPOINT, '/') . '/');
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
                $registration = ilCmiXapiUser::generateRegistration($this->object, $DIC->user());
            }
            $params['registration'] = $registration;
        } else {
            $params['registration'] = urlencode(ilCmiXapiUser::generateRegistration($this->object, $DIC->user()));
        }
        return $params;
    }
    
    protected function getAuthTokenFetchLink()
    {
        $link = implode('/', [
            ILIAS_HTTP_PATH, 'Modules', 'CmiXapi', 'xapitoken.php'
        ]);
        if (in_array((int) $_SERVER['SERVER_PORT'], [80, 443])) {
            $link = str_replace($_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'], $_SERVER['SERVER_NAME'], $link);
        }

        $param = $this->buildAuthTokenFetchParam();
        $link = iLUtil::appendUrlParameterString($link, "param={$param}");
        
        return $link;
    }
    
    /**
     * @return string
     * @throws ilCmiXapiException
     */
    protected function buildAuthTokenFetchParam()
    {
        $params = [
            session_name() => session_id(),
            'obj_id' => $this->object->getId(),
            'ref_id' => $this->object->getRefId(),
            'ilClientId' => CLIENT_ID
        ];
        
        $encryptionKey = ilCmiXapiAuthToken::getWacSalt();
        
        $param = urlencode(base64_encode(openssl_encrypt(
            json_encode($params),
            ilCmiXapiAuthToken::OPENSSL_ENCRYPTION_METHOD,
            $encryptionKey,
            0,
            ilCmiXapiAuthToken::OPENSSL_IV
        )));
        return $param;
    }
    
    protected function getValidToken()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $token = ilCmiXapiAuthToken::fillToken(
            $DIC->user()->getId(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->object->getLrsType()->getTypeId()
        );
        return $token;
    }
    
    protected function initCmixUser()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $doLpUpdate = false;
        
        // if (!ilCmiXapiUser::exists($this->object->getId(), $DIC->user()->getId())) {
        // $doLpUpdate = true;
        // }
        
        $this->cmixUser = new ilCmiXapiUser($this->object->getId(), $DIC->user()->getId(), $this->object->getPrivacyIdent());
        $user_ident = $this->cmixUser->getUsrIdent();
        if ($user_ident == '' || $user_ident == null) {
            $user_ident = ilCmiXapiUser::getIdent($this->object->getPrivacyIdent(), $DIC->user());
            $this->cmixUser->setUsrIdent($user_ident);

            if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
                $this->cmixUser->setRegistration(ilCmiXapiUser::generateCMI5Registration($this->object->getId(), $DIC->user()->getId()));
            }
            $this->cmixUser->save();
            if (!ilObjUser::_isAnonymous($DIC->user()->getId())) {
                ilLPStatusWrapper::_updateStatus($this->object->getId(), $DIC->user()->getId());
            }
        }
        // if ($doLpUpdate) {
            // ilLPStatusWrapper::_updateStatus($this->object->getId(), $DIC->user()->getId());
        // }
    }

    protected function getCmi5LearnerPreferences()
    {
        global $DIC;
        $language = $DIC->user()->getLanguage();
        $audio = "on";
        $prefs = [
            "languagePreference" => "{$language}",
            "audioPreference" => "{$audio}"
        ];
        return $prefs;
    }

    /**
     * Prelaunch
     * post cmi5LearnerPreference (agent profile)
     * post LMS.LaunchData
     */
    protected function CMI5preLaunch($token)
    {
        global $DIC;
        
        $lrsType = $this->object->getLrsType();
        $defaultLrs = $lrsType->getLrsEndpoint();
        //$fallbackLrs = $lrsType->getLrsFallbackEndpoint();
        $defaultBasicAuth = $lrsType->getBasicAuth();
        //$fallbackBasicAuth = $lrsType->getFallbackBasicAuth();
        $defaultHeaders = [
            'X-Experience-API-Version' => '1.0.3',
            'Authorization' => $defaultBasicAuth,
            'Content-Type' => 'application/json;charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ];
        
        $registration = $this->cmixUser->getRegistration();
        // for old CMI5 Content after switch commit but before cmi5 bugfix
        if ($registration == '') {
            $registration = ilCmiXapiUser::generateRegistration($this->object, $DIC->user());
        }
        
        $activityId = $this->object->getActivityId();
        
        // profile
        $profileParams = [];
        $defaultAgentProfileUrl = $defaultLrs . "/agents/profile";
        $profileParams['agent'] = json_encode($this->object->getStatementActor($this->cmixUser));
        $profileParams['profileId'] = 'cmi5LearnerPreferences';
        $defaultProfileUrl = $defaultAgentProfileUrl . '?' . ilCmiXapiAbstractRequest::buildQuery($profileParams);
        
        // launchData
        $launchDataParams = [];
        $defaultStateUrl = $defaultLrs . "/activities/state";
        //$launchDataParams['agent'] = $this->buildCmi5ActorParameter();
        $launchDataParams['agent'] = json_encode($this->object->getStatementActor($this->cmixUser));
        $launchDataParams['activityId'] = $activityId;
        $launchDataParams['activity_id'] = $activityId;
        $launchDataParams['registration'] = $registration;
        $launchDataParams['stateId'] = 'LMS.LaunchData';
        $defaultLaunchDataUrl = $defaultStateUrl . '?' . ilCmiXapiAbstractRequest::buildQuery($launchDataParams);
        $cmi5LearnerPreferencesObj = $this->getCmi5LearnerPreferences();
        $cmi5LearnerPreferences = json_encode($cmi5LearnerPreferencesObj);
        $lang = $cmi5LearnerPreferencesObj['languagePreference'];
        $cmi5_session = ilObjCmiXapi::guidv4();
        $tokenObject = ilCmiXapiAuthToken::getInstanceByToken($token);
        $oldSession = $tokenObject->getCmi5Session();
        $oldSessionLaunchedTimestamp = '';
        $abandoned = false;
        // cmi5_session already exists?
        if (!empty($oldSession)) {
            $oldSessionData = json_decode($tokenObject->getCmi5SessionData());
            $oldSessionLaunchedTimestamp = $oldSessionData->launchedTimestamp;
            $tokenObject->delete();
            $token = $this->getValidToken();
            $tokenObject = ilCmiXapiAuthToken::getInstanceByToken($token);
            $lastStatement = $this->object->getLastStatement($oldSession);
            // should never be 'terminated', because terminated statement is sniffed from proxy -> token delete
            if ($lastStatement[0]['statement']['verb']['id'] != ilCmiXapiVerbList::getInstance()->getVerbUri('terminated')) {
                $abandoned = true;
                $start = new DateTime($oldSessionLaunchedTimestamp);
                $end = new DateTime($lastStatement[0]['statement']['timestamp']);
                $diff = $end->diff($start);
                $duration = ilCmiXapiDateTime::dateIntervalToISO860Duration($diff);
            }
        }
        // satisfied on launch?
        // see: https://github.com/AICC/CMI-5_Spec_Current/blob/quartz/cmi5_spec.md#moveon
        // https://aicc.github.io/CMI-5_Spec_Current/samples/
        // Session that includes the absolute minimum data, and is associated with a NotApplicable Move On criteria
        // which results in immediate satisfaction of the course upon registration creation. Includes Satisfied Statement.
        $satisfied = false;
        $lpMode = $this->object->getLPMode();
        // only do this, if we decide to map the moveOn NotApplicable to ilLPObjSettings::LP_MODE_DEACTIVATED on import and settings editing
        // and what about user result status?
        if ($lpMode === ilLPObjSettings::LP_MODE_DEACTIVATED) {
            $satisfied = true;
        }

        $tokenObject->setCmi5Session($cmi5_session);
        $sessionData = array();
        $sessionData['cmi5LearnerPreferences'] = $cmi5LearnerPreferencesObj;
        //https://www.php.net/manual/de/class.dateinterval.php
        $now = new ilCmiXapiDateTime(time(), IL_CAL_UNIX);
        $sessionData['launchedTimestamp'] = $now->toXapiTimestamp(); // required for abandoned statement duration, don't want another roundtrip to lrs ...puhhh
        $tokenObject->setCmi5SessionData(json_encode($sessionData));
        $tokenObject->update();
        $defaultStatementsUrl = $defaultLrs . "/statements";
        
        // launchedStatement
        $launchData = json_encode($this->object->getLaunchData($this->cmixUser, $lang));
        $launchedStatement = $this->object->getLaunchedStatement($this->cmixUser);
        $launchedStatementParams = [];
        $launchedStatementParams['statementId'] = $launchedStatement['id'];
        $defaultLaunchedStatementUrl = $defaultStatementsUrl . '?' . ilCmiXapiAbstractRequest::buildQuery($launchedStatementParams);
        
        // abandonedStatement
        if ($abandoned) {
            $abandonedStatement = $this->object->getAbandonedStatement($oldSession, $duration, $this->cmixUser);
            $abandonedStatementParams = [];
            $abandonedStatementParams['statementId'] = $abandonedStatement['id'];
            $defaultAbandonedStatementUrl = $defaultStatementsUrl . '?' . ilCmiXapiAbstractRequest::buildQuery($abandonedStatementParams);
        }
        // abandonedStatement
        if ($satisfied) {
            $satisfiedStatement = $this->object->getSatisfiedStatement($this->cmixUser);
            $satisfiedStatementParams = [];
            $satisfiedStatementParams['statementId'] = $satisfiedStatement['id'];
            $defaultSatisfiedStatementUrl = $defaultStatementsUrl . '?' . ilCmiXapiAbstractRequest::buildQuery($satisfiedStatementParams);
        }
        $client = new GuzzleHttp\Client();
        $req_opts = array(
            GuzzleHttp\RequestOptions::VERIFY => true,
            GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 10,
            GuzzleHttp\RequestOptions::HTTP_ERRORS => false
        );
        $defaultProfileRequest = new GuzzleHttp\Psr7\Request(
            'POST',
            $defaultProfileUrl,
            $defaultHeaders,
            $cmi5LearnerPreferences
        );
        $defaultLaunchDataRequest = new GuzzleHttp\Psr7\Request(
            'PUT',
            $defaultLaunchDataUrl,
            $defaultHeaders,
            $launchData
        );
        $defaultLaunchedStatementRequest = new GuzzleHttp\Psr7\Request(
            'PUT',
            $defaultLaunchedStatementUrl,
            $defaultHeaders,
            json_encode($launchedStatement)
        );
        if ($abandoned) {
            $defaultAbandonedStatementRequest = new GuzzleHttp\Psr7\Request(
                'PUT',
                $defaultAbandonedStatementUrl,
                $defaultHeaders,
                json_encode($abandonedStatement)
            );
        }
        if ($satisfied) {
            $defaultSatisfiedStatementRequest = new GuzzleHttp\Psr7\Request(
                'PUT',
                $defaultSatisfiedStatementUrl,
                $defaultHeaders,
                json_encode($satisfiedStatement)
            );
        }
        $promises = array();
        $promises['defaultProfile'] = $client->sendAsync($defaultProfileRequest, $req_opts);
        $promises['defaultLaunchData'] = $client->sendAsync($defaultLaunchDataRequest, $req_opts);
        $promises['defaultLaunchedStatement'] = $client->sendAsync($defaultLaunchedStatementRequest, $req_opts);
        if ($abandoned) {
            $promises['defaultAbandonedStatement'] = $client->sendAsync($defaultAbandonedStatementRequest, $req_opts);
        }
        if ($satisfied) {
            $promises['defaultSatisfiedStatement'] = $client->sendAsync($defaultSatisfiedStatementRequest, $req_opts);
        }
        try {
            $responses = GuzzleHttp\Promise\Utils::settle($promises)->wait();
            $body = '';
            foreach ($responses as $response) {
                ilCmiXapiAbstractRequest::checkResponse($response, $body, [204]);
            }
        } catch (Exception $e) {
            $this->log()->error('error:' . $e->getMessage());
        }
        return array('cmi5_session' => $cmi5_session, 'token' => $token);
    }
    
    private function log()
    {
        global $log;
        if ($this->plugin) {
            return $log;
        } else {
            return \ilLoggerFactory::getLogger('cmix');
        }
    }
}
