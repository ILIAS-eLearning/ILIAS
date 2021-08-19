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
     * @var plugin
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
        if ($this->object->getContentType(ilObjCmiXapi::CONT_TYPE_CMI5)) {
            $this->CMI5preLaunch();
        }
        $launchLink = $this->buildLaunchLink();
        $DIC->ctrl()->redirectToURL($launchLink);
    }
    
    protected function buildLaunchLink()
    {
        if ($this->object->getSourceType() == ilObjCmiXapi::SRC_TYPE_REMOTE) {
            $launchLink = $this->object->getLaunchUrl();
        } elseif ($this->object->getSourceType() == ilObjCmiXapi::SRC_TYPE_LOCAL) {
            if (preg_match("/^(https?:\/\/)/",$this->object->getLaunchUrl()) == 1) {
                $launchLink = $this->object->getLaunchUrl();
            } else {
                $launchLink = implode('/', [
                    ILIAS_HTTP_PATH, ilUtil::getWebspaceDir(),
                    ilCmiXapiContentUploadImporter::RELATIVE_CONTENT_DIRECTORY_NAMEBASE . $this->object->getId()
                ]);

                $launchLink .= DIRECTORY_SEPARATOR . $this->object->getLaunchUrl();
            }
        }
        
        foreach ($this->getLaunchParameters() as $paramName => $paramValue) {
            $launchLink = ilUtil::appendUrlParameterString($launchLink, "{$paramName}={$paramValue}");
        }
        
        return $launchLink;
    }
    
    protected function getLaunchParameters()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $params = [];
        
        if ($this->object->isBypassProxyEnabled()) {
            $params['endpoint'] = urlencode($this->object->getLrsType()->getLrsEndpoint());
        } else {
            $params['endpoint'] = urlencode(ILIAS_HTTP_PATH . '/' . self::XAPI_PROXY_ENDPOINT);
        }
        
        if ($this->object->isAuthFetchUrlEnabled()) {
            $this->getValidToken();
            $params['fetch'] = urlencode($this->getAuthTokenFetchLink());
        } else {
            if ($this->object->isBypassProxyEnabled()) {
                $params['auth'] = urlencode($this->object->getLrsType()->getBasicAuth());
                $this->getValidToken();
            } else {
                $params['auth'] = urlencode('Basic ' . base64_encode(
                    CLIENT_ID . ':' . $this->getValidToken()
                ));
            }
        }
        
        $params['activity_id'] = urlencode($this->object->getActivityId());
        $params['activityId'] = urlencode($this->object->getActivityId());
        
        $params['actor'] = urlencode($this->buildActorParameter());

        $params['registration'] = urlencode(ilCmiXapiUser::getRegistration($this->object, $DIC->user()));

        return $params;
    }
    
    protected function getAuthTokenFetchLink()
    {
        $link = implode('/', [
            ILIAS_HTTP_PATH, 'Modules', 'CmiXapi', 'xapitoken.php'
        ]);
        
        $link = iLUtil::appendUrlParameterString($link, "param={$this->buildAuthTokenFetchParam()}");
        
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
    
    protected function buildActorParameter()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $name = ilCmiXapiUser::getName($this->object->getUserName(), $DIC->user());
        return json_encode([
            'mbox' => $this->cmixUser->getUsrIdent(),
            'name' => $name,
            'objectType' => 'Agent',
            'account' => [
                'homePage' => 'NO_PAGE',
                'name' => $name
            ]
        ]);
    }
    
    protected function buildAgentParameter()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $name = ilCmiXapiUser::getName($this->object->getUserName(), $DIC->user());

        return json_encode([
            'objectType' => 'Agent',
            'mbox' => 'mailto:'.$this->cmixUser->getUsrIdent(),
            'name' => $name
        ]);
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
        
        if (!ilCmiXapiUser::exists($this->object->getId(), $DIC->user()->getId())) {
            $doLpUpdate = true;
        }
        
        $this->cmixUser = new ilCmiXapiUser($this->object->getId(), $DIC->user()->getId());
        $this->cmixUser->setUsrIdent(ilCmiXapiUser::getIdent($this->object->getUserIdent(), $DIC->user()));
        $this->cmixUser->save();
        
        if ($doLpUpdate) {
            ilLPStatusWrapper::_updateStatus($this->object->getId(), $DIC->user()->getId());
        }
    }

    protected function getLaunchData()
    {
        /**
         * launchMethod:
         * OwnWindow | AnyWindow: see https://github.com/andyjohnson/CMI-5_Spec_Current/blob/master/cmi5_spec.md#launchmethod
         */
        // $launchMethod = "OwnWindow";
        
        /**
         * moveOn: https://github.com/AICC/CMI-5_Spec_Current/blob/quartz/cmi5_spec.md#moveon
         */
        $moveOn = "Completed"; // needs to be saved on import and get with $this->object->getMoveOn()

        /**
         * sessionid: https://github.com/AICC/CMI-5_Spec_Current/blob/quartz/cmi5_spec.md#9631-session-id
         */
        $sessionId = $this->guidv4(); // vielleicht brauchen wir noch replizierbar die gleiche session-id
        $launchMode = $this->object->getLaunchMode();
        $activityId = $this->object->getActivityId();
        $ctxTemplate = [
            "contextTemplate" => [
                "contextActivities" => [
                    "grouping" => [
                        "objectType" => "Activity",
                        "id" => "{$activityId}"
                    ]
                ],
                "extensions" => [
                    "https://w3id.org/xapi/cmi5/context/extensions/sessionid" => "{$sessionId}"
                ]
            ],
            "launchMode" => ucfirst($launchMode),
            "launchMethod" => "OwnWindow",
            "moveOn" => $moveOn
        ];
        $lmsLaunchMethod = $this->object->getLaunchMethod();
        if ($lmsLaunchMethod === "ownWin") {
            include_once('./Services/Link/classes/class.ilLink.php');
            $href = ilLink::_getStaticLink(
                $this->object->getRefId(),
                $this->object->getType()
            );
            $this->log()->log($href);
            $ctxTemplate['returnURL'] = $href;
        }
        return json_encode($ctxTemplate);
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
        return json_encode($prefs);
    }

    /**
     * Prelaunch
     * post cmi5LearnerPreference (agent profile)
     * post LMS.LaunchData
     */
    protected function CMI5preLaunch()
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
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Content-Type' => 'application/json; charset=utf-8'
        ];
        /*
        $fallbackHeaders = [
            'X-Experience-API-Version' => '1.0.3',
            'Authorization' => $fallbackBasicAuth,
            'Content-Type' => 'application/json; charset=utf-8'
        ];
        */
        
        $registration = ilCmiXapiUser::getRegistration($this->object, $DIC->user());
        $activityId = $this->object->getActivityId();
        
        // profile
        $profileParams = [];
        $defaultAgentProfileUrl = $defaultLrs . "/agents/profile";
        $profileParams['agent'] = $this->buildAgentParameter();
        $profileParams['profileId'] = 'cmi5LearnerPreferences';
        $defaultProfileUrl = $defaultAgentProfileUrl . '?' . $this->buildQuery($profileParams);
        
        // launchData
        $launchDataParams = [];
        $defaultStateUrl = $defaultLrs . "/activities/state";
        $launchDataParams['agent'] = $this->buildAgentParameter();
        $launchDataParams['activityId'] = $activityId;
        $launchDataParams['activity_id'] = $activityId;
        $launchDataParams['registration'] = $registration;
        $launchDataParams['agent'] = $this->buildAgentParameter();
        $launchDataParams['stateId'] = 'LMS.LaunchData';
        $defaultLaunchDataUrl = $defaultStateUrl . '?' . $this->buildQuery($launchDataParams);
        
        $this->log()->log($defaultLaunchDataUrl); 
        
        $cmi5LearnerPreferences = $this->getCmi5LearnerPreferences();
        $launchData = $this->getLaunchData();

        $client = new GuzzleHttp\Client();
        //new GuzzleHttp\Psr7\Request('POST', $defaultUrl, $this->defaultHeaders, $body);
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
        $promises = array();
        $promises['defaultProfile'] = $client->sendAsync($defaultProfileRequest);
        $promises['defaultLaunchData'] = $client->sendAsync($defaultLaunchDataRequest);

        try 
        {
            $responses = GuzzleHttp\Promise\settle($promises)->wait();
        }
        catch(Exception $e) 
        {
            $this->log()->error('error:' . $e->getMessage());
        }
    }

    private function checkResponse($response) 
    {
        global $DIC;
        if ($response['state'] === 'fulfilled') {
            $status = $response['value']->getStatusCode();
            if ($status === 204) {
                return true;
            }
            else {
                $this->log()->error($this->msg("Could not get valid response status_code: " . $status .  " from "));
                return false;
            }
        }
        else {
            $this->log()->error($this->msg("Could not fulfill request to "));
            return false;
        }
        return false;
    }

    private function guidv4($data = null) {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);
    
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    private function buildQuery(array $params, $encoding = PHP_QUERY_RFC3986)
    {
        if (!$params) {
            return '';
        }

        if ($encoding === false) {
            $encoder = function ($str) {
                return $str;
            };
        } elseif ($encoding === PHP_QUERY_RFC3986) {
            $encoder = 'rawurlencode';
        } elseif ($encoding === PHP_QUERY_RFC1738) {
            $encoder = 'urlencode';
        } else {
            throw new \InvalidArgumentException('Invalid type');
        }

        $qs = '';
        foreach ($params as $k => $v) {
            $k = $encoder($k);
            if (!is_array($v)) {
                $qs .= $k;
                if ($v !== null) {
                    $qs .= '=' . $encoder($v);
                }
                $qs .= '&';
            } else {
                foreach ($v as $vv) {
                    $qs .= $k;
                    if ($vv !== null) {
                        $qs .= '=' . $encoder($vv);
                    }
                    $qs .= '&';
                }
            }
        }

        return $qs ? (string) substr($qs, 0, -1) : '';
    }

    private function log() {
        global $log;
        if ($this->plugin) {
            return $log;
        }
        else {
            return \ilLoggerFactory::getLogger('cmix');
        }
    }
}