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
        
        $launchLink = $this->buildLaunchLink();
        $DIC->ctrl()->redirectToURL($launchLink);
    }
    
    protected function buildLaunchLink()
    {
        if ($this->object->getSourceType() == ilObjCmiXapi::SRC_TYPE_REMOTE) {
            $launchLink = $this->object->getLaunchUrl();
        } elseif ($this->object->getSourceType() == ilObjCmiXapi::SRC_TYPE_LOCAL) {
            $launchLink = implode('/', [
                ILIAS_HTTP_PATH, ilUtil::getWebspaceDir(),
                ilCmiXapiContentUploadImporter::RELATIVE_CONTENT_DIRECTORY_NAMEBASE . $this->object->getId()
            ]);

            $launchLink .= DIRECTORY_SEPARATOR . $this->object->getLaunchUrl();
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
//        $name = ($name === '') ? 'NO_NAME' : $name;
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
        $launchMethod = "AnyWindow"; // $this->object->getLaunchMethod(),
        $moveOn = "Completed";
        return json_encode([
            "contextTemplate" => [
                "contextActivities" => [
                    "grouping" => [
                        "objectType" => "Activity",
                        "id" => "http://course-repository.example.edu/identifiers/courses/02baafcf/aus/4c07"
                    ]
                ],
                "extensions" => [
                    "https://w3id.org/xapi/cmi5/context/extensions/sessionid" => "32e96d95-8e9c-4162-b3ac-66df22d171c5"
                ]
            ],
            "launchMode" => $this->object->getLaunchMode(),
//            "returnURL":
            "launchMethod" => $launchMethod,
            "moveOn" => $moveOn
        ]);
    }
}
