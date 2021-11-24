<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjCmiXapi
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilObjCmiXapi extends ilObject2
{
    const PLUGIN = false;

    const DB_TABLE_NAME = 'cmix_settings';
    protected function dbTableName()
    {
        return self::DB_TABLE_NAME;
    }
    
    /**
     * repository object activation settings (handled by ilObject)
     */
    protected $activationLimited;
    protected $activationStartingTime;
    protected $activationEndingTime;
    protected $activationVisibility;
    
    /**
     * @var int
     */
    protected $lrsTypeId;
    
    /**
     * @var ilCmiXapiLrsType
     */
    protected $lrsType;
    
    /**
     * @var string
     */
    protected $contentType;
    const CONT_TYPE_GENERIC = 'generic';
    const CONT_TYPE_CMI5 = 'cmi5';
    
    /**
     * @var string
     */
    protected $sourceType;
    const SRC_TYPE_REMOTE = 'remoteSource';
    const SRC_TYPE_LOCAL = 'localSource';
    const SRC_TYPE_EXTERNAL = 'externalSource';
    
    /**
     * @var string
     */
    protected $activityId;
    
    /**
     * @var string
     */
    protected $publisherId;

    /**
     * @var string
     */
    protected $instructions;
    
    /**
     * @var string
     */
    protected $launchUrl;

    /**
     * @var string
     */
    protected $launchParameters;
    
    /**
     * @var string
     */
    protected $moveOn;

    /**
     * @var string
     */
    protected $entitlementKey;

    /**
     * @var bool
     */
    protected $authFetchUrlEnabled;
    
    /**
     * @var anonymousHomePage;
     */
    protected $anonymousHomePage = false;
    const ANONYMOUS_HOMEPAGE = 'https://example.org';

    /**
     * @var string
     */
    protected $launchMethod;
    const LAUNCH_METHOD_OWN_WIN = 'ownWin';
    const LAUNCH_METHOD_NEW_WIN = 'newWin';
    
    /**
     * @var string
     */
    protected $launchMode;
    const LAUNCH_MODE_NORMAL = 'Normal';
    const LAUNCH_MODE_BROWSE = 'Browse';
    const LAUNCH_MODE_REVIEW = 'Review';
    
    /**
     * @var bool
     */
    protected $switchToReviewEnabled;

    /**
     * @var float
     */
    const LMS_MASTERY_SCORE = 0.7;
    protected $masteryScore;
    
    /**
     * @var bool
     */
    protected $keepLpStatusEnabled;
    
    /**
     * @var string
     */
    protected $userIdent;
    const PRIVACY_IDENT_IL_UUID_USER_ID = 0;
    const PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT = 1;
    const PRIVACY_IDENT_IL_UUID_LOGIN = 2;
    const PRIVACY_IDENT_REAL_EMAIL = 3;
    const PRIVACY_IDENT_IL_UUID_RANDOM = 4;
    
    /**
     * @var string
     */
    protected $userName;
    const PRIVACY_NAME_NONE = 0;
    const PRIVACY_NAME_FIRSTNAME = 1;
    const PRIVACY_NAME_LASTNAME = 2;
    const PRIVACY_NAME_FULLNAME = 3;

    
    /**
     * @var string
     */
    protected $userPrivacyComment;
    
    /**
     * @var bool
     */
    protected $statementsReportEnabled;
    
    /**
     * @var string
     */
    protected $xmlManifest;
    
    /**
     * @var int
     */
    protected $version;
    
    /**
     * @var bool
     */
    protected $bypassProxyEnabled;

    /** @var bool $only_moveon */
    protected $only_moveon = false;

    /** @var bool $achieved */
    protected $achieved = true;

    /** @var bool $answered */
    protected $answered = true;

    /** @var bool $completed */
    protected $completed = true;

    /** @var bool $failed */
    protected $failed = true;

    /** @var bool $initialized */
    protected $initialized = true;

    /** @var bool $passed */
    protected $passed = true;

    /** @var bool $progressed */
    protected $progressed = true;

    /** @var bool $satisfied */
    protected $satisfied = true;

    /** @var bool $terminated */
    protected $terminated = true;

    /** @var bool $hide_data */
    protected $hide_data = false;

    /** @var bool $timestamp */
    protected $timestamp = false;

    /** @var bool $duration */
    protected $duration = true;

    /** @var bool $no_substatements */
    protected $no_substatements = false;

    /** @var ilCmiXapiUser $currentCmixUser */
    protected $currentCmixUser = null;

    /**
     * ilObjCmiXapi constructor.
     * @param int $a_id
     * @param bool $a_reference
     */
    public function __construct($a_id = 0, $a_reference = true)
    {
        //$this->activationLimited = $activationLimited;
        //$this->activationStartingTime = $activationStartingTime;
        //$this->activationEndingTime = $activationEndingTime;
        //$this->activationVisibility = $activationVisibility;
        
        $this->lrsTypeId = 0;
        //$this->lrsType = $lrsType;
        
        $this->contentType = self::CONT_TYPE_GENERIC;
        $this->sourceType = self::SRC_TYPE_REMOTE;
        
        $this->activityId = '';
        
        $this->publisherId = '';

        $this->instructions = '';

        $this->launchUrl = '';
        $this->launchParameters = '';
        $this->moveOn = '';
        $this->entitlementKey = '';
        
        $this->authFetchUrlEnabled = 0;
        
        $this->launchMethod = self::LAUNCH_METHOD_NEW_WIN;
        $this->launchMode = self::LAUNCH_MODE_NORMAL;

        $this->switchToReviewEnabled = 1;
        
        $this->masteryScore = self::LMS_MASTERY_SCORE;
        $this->keepLpStatusEnabled = 1;
        
        $this->userIdent = self::PRIVACY_IDENT_IL_UUID_USER_ID;
        $this->userName = self::PRIVACY_NAME_NONE;
        $this->userPrivacyComment = '';

        $this->currentCmixUser = null;

        $this->statementsReportEnabled = 0;

        $this->xmlManifest = '';
        $this->version = 0;
        
        $this->bypassProxyEnabled = false;

        parent::__construct($a_id, $a_reference);
    }


    public static function getInstance($a_id = 0, $a_reference = true)
    {
        return new self($a_id, $a_reference);
    }
    
    /**
     * @return string
     */
    protected function initType()
    {
        $this->type = "cmix";
    }
    
    /**
     * @return bool
     */
    public function isActivationLimited()
    {
        return $this->activationLimited;
    }
    
    /**
     * @param bool $activationLimited
     */
    public function setActivationLimited($activationLimited)
    {
        $this->activationLimited = $activationLimited;
    }
    
    /**
     * @return int
     */
    public function getActivationStartingTime()
    {
        return $this->activationStartingTime;
    }
    
    /**
     * @param int $activationStartingTime
     */
    public function setActivationStartingTime($activationStartingTime)
    {
        $this->activationStartingTime = $activationStartingTime;
    }
    
    /**
     * @return int
     */
    public function getActivationEndingTime()
    {
        return $this->activationEndingTime;
    }
    
    /**
     * @param int $activationEndingTime
     */
    public function setActivationEndingTime($activationEndingTime)
    {
        $this->activationEndingTime = $activationEndingTime;
    }
    
    /**
     * @return bool
     */
    public function getActivationVisibility()
    {
        return $this->activationVisibility;
    }
    
    /**
     * @param bool $activationVisibility
     */
    public function setActivationVisibility($activationVisibility)
    {
        $this->activationVisibility = $activationVisibility;
    }
    
    /**
     * @return int
     */
    public function getLrsTypeId()
    {
        return $this->lrsTypeId;
    }
    
    /**
     * @param int $lrsTypeId
     */
    public function setLrsTypeId($lrsTypeId)
    {
        $this->lrsTypeId = $lrsTypeId;
    }
    
    /**
     * @return ilCmiXapiLrsType
     */
    public function getLrsType()
    {
        return $this->lrsType;
    }
    
    /**
     * @param ilCmiXapiLrsType $lrsType
     */
    public function setLrsType($lrsType)
    {
        $this->lrsType = $lrsType;
    }
    
    public function initLrsType()
    {
        $this->setLrsType(new ilCmiXapiLrsType($this->getLrsTypeId()));
    }
    
    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }
    
    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        //bug before 21-07-24
        if ($contentType == "learning") {
            $contentType = self::CONT_TYPE_GENERIC;
        }
        $this->contentType = $contentType;
    }
    
    /**
     * @param string $contentType
     */
    public function isMixedContentType() : bool
    {
        // after 21-07-24 and before cmi5 refactoring
        // launched before cmi5 refactoring ident in:    statement.actor.mbox 
        // launched after  cmi5 refactoring ident in:    statement.actor.account.name 
        return (($this->getContentType() == self::CONT_TYPE_CMI5) && empty($this->getPublisherId()));
    }

    /**
     * @return string
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }
    
    /**
     * @return bool
     */
    public function isSourceTypeRemote()
    {
        return $this->sourceType == self::SRC_TYPE_REMOTE;
    }
    
    /**
     * @return bool
     */
    public function isSourceTypeExternal()
    {
        return $this->sourceType == self::SRC_TYPE_EXTERNAL;
    }
    
    /**
     * @param string $sourceType
     */
    public function setSourceType($sourceType)
    {
        $this->sourceType = $sourceType;
    }
    
    /**
     * @return string
     */
    public function getActivityId()
    {
        return $this->activityId;
    }
    
    /**
     * @param string $activityId
     */
    public function setActivityId($activityId)
    {
        $this->activityId = $activityId;
    }
    
    /**
     * @return string
     */
    public function getPublisherId()
    {
        return $this->publisherId;
    }
    
    /**
     * @param string $publisherId
     */
    public function setPublisherId($publisherId)
    {
        $this->publisherId = $publisherId;
    }

    /**
     * @return string
     */
    public function getInstructions()
    {
        return $this->instructions;
    }
    
    /**
     * @param string $instructions
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;
    }

    /**
     * @return string
     */
    public function getLaunchUrl()
    {
        return $this->launchUrl;
    }
    
    /**
     * @param string $launchUrl
     */
    public function setLaunchUrl($launchUrl)
    {
        $this->launchUrl = $launchUrl;
    }
    
    /**
     * @return string
     */
    public function getLaunchParameters()
    {
        return $this->launchParameters;
    }
    
    /**
     * @param string $launchParameters
     */
    public function setLaunchParameters($launchParameters)
    {
        $this->launchParameters = $launchParameters;
    }

    /**
     * @return string
     * Attention: this is the original imported moveOn
     * for using in LaunchData and LaunchStatement use getLMSMoveOn!
     */
    public function getMoveOn()
    {
        return $this->moveOn;
    }

    /**
     * @param string $moveOn
     * Attention: this is the original moveOn from course import
     * should only be set on import!
     */
    public function setMoveOn($moveOn)
    {
        $this->moveOn = $moveOn;
    }

    /**
     * @return int ilLPObjSettings::const
     * only for internal LMS usage
     */
    public function getLPMode()
    {
        // oder besser settings?
        $olp = ilObjectLP::getInstance($this->getId());
        return $olp->getCurrentMode();
    }

    /**
     * @return string ilCmiXapiLP::const
     * for CMI5 statements | state moveOn values
     */
    public function getLMSMoveOn()
    {
        $moveOn = ilCmiXapiLP::MOVEON_NOT_APPLICABLE;
        switch ($this->getLPMode())
        {
            case ilLPObjSettings::LP_MODE_DEACTIVATED :
                $moveOn = ilCmiXapiLP::MOVEON_NOT_APPLICABLE;
            break;
            case ilLPObjSettings::LP_MODE_CMIX_COMPLETED :
            case ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED :
                $moveOn = ilCmiXapiLP::MOVEON_COMPLETED;
            break;
            case ilLPObjSettings::LP_MODE_CMIX_PASSED :
            case ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED :
                $moveOn = ilCmiXapiLP::MOVEON_PASSED;
            break;
                case ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED :
                case ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED :
                $moveOn = ilCmiXapiLP::MOVEON_COMPLETED_OR_PASSED;
            break;
        }
        return $moveOn;
    }

    /**
     * @return string
     */
    public function getEntitlementKey()
    {
        return $this->entitlementKey;
    }

    /**
     * @param string $entitlementKey
     */
    public function setEntitlementKey($entitlementKey)
    {
        $this->entitlementKey = $entitlementKey;
    }

    /**
     * @return bool
     */
    public function isAuthFetchUrlEnabled()
    {
        return $this->authFetchUrlEnabled;
    }
    
    /**
     * @param bool $authFetchUrlEnabled
     */
    public function setAuthFetchUrlEnabled($authFetchUrlEnabled)
    {
        $this->authFetchUrlEnabled = $authFetchUrlEnabled;
    }
    
    /**
     * @return string
     */
    public function getLaunchMethod()
    {
        return $this->launchMethod;
    }
    
    /**
     * @param string $launchMethod
     */
    public function setLaunchMethod($launchMethod)
    {
        $this->launchMethod = $launchMethod;
    }
    
    /**
     * @return string
     */
    public function getLaunchMode()
    {
        return ucfirst($this->launchMode);
    }
    
    /**
     * @param string $launchMode
     */
    public function setLaunchMode($launchMode)
    {
        $this->launchMode = ucfirst($launchMode);
    }
    
    /**
     * @return bool
     */
    public function isSwitchToReviewEnabled()
    {
        return $this->switchToReviewEnabled;
    }
    
    /**
     * @return bool
     */
    public function getSwitchToReviewEnabled()
    {
        return $this->switchToReviewEnabled;
    }
    
    /**
     * @param bool $switchToReviewEnabled
     */
    public function setSwitchToReviewEnabled($switchToReviewEnabled)
    {
        $this->switchToReviewEnabled = $switchToReviewEnabled;
    }

    /**
     * @return float
     */
    public function getMasteryScore()
    {
        return $this->masteryScore;
    }
    
    /**
     * @param float $masteryScore
     */
    public function setMasteryScore($masteryScore)
    {
        $this->masteryScore = $masteryScore;
    }
    
    /**
     * @return float
     */
    public function getMasteryScorePercent()
    {
        return $this->masteryScore * 100;
    }
    
    /**
     * @param float $masteryScorePercent
     */
    public function setMasteryScorePercent($masteryScorePercent)
    {
        $this->masteryScore = $masteryScorePercent / 100;
    }
    
    /**
     * @return bool
     */
    public function isKeepLpStatusEnabled()
    {
        return $this->keepLpStatusEnabled;
    }
    
    /**
     * @param bool $keepLpStatusEnabled
     */
    public function setKeepLpStatusEnabled($keepLpStatusEnabled)
    {
        $this->keepLpStatusEnabled = $keepLpStatusEnabled;
    }
    
    /**
     * @return string
     */
    public function getPrivacyIdent()
    {
        return $this->userIdent;
    }
    
    /**
     * @param string $userIdent
     */
    public function setPrivacyIdent($userIdent)
    {
        $this->userIdent = $userIdent;
    }
    
    /**
     * @return string
     */
    public function getPrivacyName()
    {
        return $this->userName;
    }
    
    /**
     * @param string $userName
     */
    public function setPrivacyName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return bool
     */
    public function getOnlyMoveon(): bool
    {
        return $this->only_moveon;
    }

    /**
     * @param bool $only_moveon
     */
    public function setOnlyMoveon(bool $only_moveon)
    {
        $this->only_moveon = $only_moveon;
    }

    /**
     * @return bool
     */
    public function getAchieved(): bool
    {
        return $this->achieved;
    }

    /**
     * @param bool $achieved
     */
    public function setAchieved(bool $achieved)
    {
        $this->achieved = $achieved;
    }

    /**
     * @return bool
     */
    public function getAnswered(): bool
    {
        return $this->answered;
    }

    /**
     * @param bool $answered
     */
    public function setAnswered(bool $answered)
    {
        $this->answered = $answered;
    }

    /**
     * @return bool
     */
    public function getCompleted(): bool
    {
        return $this->completed;
    }

    /**
     * @param bool $completed
     */
    public function setCompleted(bool $completed)
    {
        $this->completed = $completed;
    }

    /**
     * @return bool
     */
    public function getFailed(): bool
    {
        return $this->failed;
    }

    /**
     * @param bool $failed
     */
    public function setFailed(bool $failed)
    {
        $this->failed = $failed;
    }

    /**
     * @return bool
     */
    public function getInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * @param bool $initialized
     */
    public function setInitialized(bool $initialized)
    {
        $this->initialized = $initialized;
    }

    /**
     * @return bool
     */
    public function getPassed(): bool
    {
        return $this->passed;
    }

    /**
     * @param bool $passed
     */
    public function setPassed(bool $passed)
    {
        $this->passed = $passed;
    }

    /**
     * @return bool
     */
    public function getProgressed(): bool
    {
        return $this->progressed;
    }

    /**
     * @param bool $progressed
     */
    public function setProgressed(bool $progressed)
    {
        $this->progressed = $progressed;
    }

    /**
     * @return bool
     */
    public function getSatisfied(): bool
    {
        return $this->satisfied;
    }

    /**
     * @param bool $satisfied
     */
    public function setSatisfied(bool $satisfied)
    {
        $this->satisfied = $satisfied;
    }

    /**
     * @return bool
     */
    public function getTerminated(): bool
    {
        return $this->terminated;
    }

    /**
     * @param bool $terminated
     */
    public function setTerminated(bool $terminated)
    {
        $this->terminated = $terminated;
    }

    /**
     * @return bool
     */
    public function getHideData(): bool
    {
        return $this->hide_data;
    }

    /**
     * @param bool $hide_data
     */
    public function setHideData(bool $hide_data)
    {
        $this->hide_data = $hide_data;
    }

    /**
     * @return bool
     */
    public function getTimestamp(): bool
    {
        return $this->timestamp;
    }

    /**
     * @param bool $timestamp
     */
    public function setTimestamp(bool $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return bool
     */
    public function getDuration(): bool
    {
        return $this->duration;
    }

    /**
     * @param bool $duration
     */
    public function setDuration(bool $duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return bool
     */
    public function getNoSubstatements(): bool
    {
        return $this->no_substatements;
    }

    /**
     * @param bool $no_substatements
     */
    public function setNoSubstatements(bool $no_substatements)
    {
        $this->no_substatements = $no_substatements;
    }

    /**
     * @return string
     */
    public function getUserPrivacyComment()
    {
        return $this->userPrivacyComment;
    }
    
    /**
     * @param string $userPrivacyComment
     */
    public function setUserPrivacyComment($userPrivacyComment)
    {
        $this->userPrivacyComment = $userPrivacyComment;
    }
    
    /**
     * @return bool
     */
    public function isStatementsReportEnabled()
    {
        return $this->statementsReportEnabled;
    }
    
    /**
     * @param bool $statementsReportEnabled
     */
    public function setStatementsReportEnabled($statementsReportEnabled)
    {
        $this->statementsReportEnabled = $statementsReportEnabled;
    }
    
    /**
     * @return string
     */
    public function getXmlManifest()
    {
        return $this->xmlManifest;
    }
    
    /**
     * @param string $xmlManifest
     */
    public function setXmlManifest($xmlManifest)
    {
        $this->xmlManifest = $xmlManifest;
    }
    
    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }
    
    /**
     * @return bool
     */
    public function isBypassProxyEnabled() : bool
    {
        return $this->bypassProxyEnabled;
    }
    
    /**
     * @param bool $bypassProxyEnabled
     */
    public function setBypassProxyEnabled(bool $bypassProxyEnabled)
    {
        $this->bypassProxyEnabled = $bypassProxyEnabled;
    }
    
    public function doRead()
    {
        $this->load();
    }
    
    public function load()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $query = "SELECT * FROM {$this->dbTableName()} WHERE obj_id = %s";
        $res = $DIC->database()->queryF($query, ['integer'], [$this->getId()]);
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            if ($row['lrs_type_id']) {
                $this->setLrsTypeId((int) $row['lrs_type_id']);
                $this->initLrsType();
            }
            
            $this->setContentType($row['content_type']);
            $this->setSourceType($row['source_type']);
            
            $this->setActivityId($row['activity_id']);
            $this->setPublisherId($row['publisher_id']);
            $this->setInstructions($row['instructions']);
            
            $this->setLaunchUrl($row['launch_url']);
            $this->setLaunchParameters($row['launch_parameters']);
            $this->setMoveOn($row['moveon']);
            $this->setEntitlementKey($row['entitlement_key']);
            $this->setAuthFetchUrlEnabled((bool) $row['auth_fetch_url']);
            
            $this->setLaunchMethod($row['launch_method']);
            
            $this->setLaunchMode($row['launch_mode']);
            $this->setSwitchToReviewEnabled((bool) $row['switch_to_review']);
            $this->setMasteryScore((float) $row['mastery_score']);
            $this->setKeepLpStatusEnabled((bool) $row['keep_lp']);
            
            $this->setPrivacyIdent($row['privacy_ident']);
            $this->setPrivacyName($row['privacy_name']);

            $this->setOnlyMoveon((bool) $row['only_moveon']);
            $this->setAchieved((bool) $row['achieved']);
            $this->setAnswered((bool) $row['answered']);
            $this->setCompleted((bool) $row['completed']);
            $this->setFailed((bool) $row['failed']);
            $this->setInitialized((bool) $row['initialized']);
            $this->setPassed((bool) $row['passed']);
            $this->setProgressed((bool) $row['progressed']);
            $this->setSatisfied((bool) $row['satisfied']);
            $this->setTerminated((bool) $row['c_terminated']);
            $this->setHideData((bool) $row['hide_data']);
            $this->setTimestamp((bool) $row['c_timestamp']);
            $this->setDuration((bool) $row['duration']);
            $this->setNoSubstatements((bool) $row['no_substatements']);

            $this->setUserPrivacyComment($row['usr_privacy_comment']);
            
            $this->setStatementsReportEnabled((bool) $row['show_statements']);
            
            $this->setXmlManifest($row['xml_manifest']);
            $this->setVersion((int) $row['version']);
            
            $this->setBypassProxyEnabled((bool) $row['bypass_proxy']);

            $this->setHighscoreEnabled((bool) $row['highscore_enabled']);
            $this->setHighscoreAchievedTS((bool) $row['highscore_achieved_ts']);
            $this->setHighscorePercentage((bool) $row['highscore_percentage']);
            $this->setHighscoreWTime((bool) $row['highscore_wtime']);
            $this->setHighscoreOwnTable((bool) $row['highscore_own_table']);
            $this->setHighscoreTopTable((bool) $row['highscore_top_table']);
            $this->setHighscoreTopNum((int) $row['highscore_top_num']);
        }
        
        $this->loadRepositoryActivationSettings();
    }
    
    public function doUpdate()
    {
        $this->save();
    }
    
    public function save()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->database()->replace($this->dbTableName(), [
            'obj_id' => ['integer', $this->getId()]
        ], [
            'lrs_type_id' => ['integer', $this->getLrsTypeId()],
            'content_type' => ['text', $this->getContentType()],
            'source_type' => ['text', $this->getSourceType()],
            'activity_id' => ['text', $this->getActivityId()],
            'publisher_id' => ['text', $this->getPublisherId()],
            'instructions' => ['text', $this->getInstructions()],
            'launch_url' => ['text', $this->getLaunchUrl()],
            'launch_parameters' => ['text', $this->getLaunchParameters()],
            'moveon' => ['text', $this->getMoveOn()],
            'entitlement_key' => ['text', $this->getEntitlementKey()],
            'auth_fetch_url' => ['integer', (int) $this->isAuthFetchUrlEnabled()],
            'launch_method' => ['text', $this->getLaunchMethod()],
            'launch_mode' => ['text', $this->getLaunchMode()],
            'switch_to_review' => ['integer', (int) $this->isSwitchToReviewEnabled()],
            'mastery_score' => ['float', $this->getMasteryScore()],
            'keep_lp' => ['integer', (int) $this->isKeepLpStatusEnabled()],
            'privacy_ident' => ['integer', $this->getPrivacyIdent()],
            'privacy_name' => ['integer', $this->getPrivacyName()],
            'usr_privacy_comment' => ['text', $this->getUserPrivacyComment()],
            'show_statements' => ['integer', (int) $this->isStatementsReportEnabled()],
            'xml_manifest' => ['text', $this->getXmlManifest()],
            'version' => ['integer', $this->getVersion()],
            'bypass_proxy' => ['integer', (int) $this->isBypassProxyEnabled()],
            'highscore_enabled' => ['integer', (int) $this->getHighscoreEnabled()],
            'highscore_achieved_ts' => ['integer', (int) $this->getHighscoreAchievedTS()],
            'highscore_percentage' => ['integer', (int) $this->getHighscorePercentage()],
            'highscore_wtime' => ['integer', (int) $this->getHighscoreWTime()],
            'highscore_own_table' => ['integer', (int) $this->getHighscoreOwnTable()],
            'highscore_top_table' => ['integer', (int) $this->getHighscoreTopTable()],
            'highscore_top_num' => ['integer', (int) $this->getHighscoreTopNum()],
            'only_moveon' => ['integer', (int)$this->getOnlyMoveon()],
            'achieved' => ['integer', (int)$this->getAchieved()],
            'answered' => ['integer', (int)$this->getAnswered()],
            'completed' => ['integer', (int)$this->getCompleted()],
            'failed' => ['integer', (int)$this->getFailed()],
            'initialized' => ['integer', (int)$this->getInitialized()],
            'passed' => ['integer', (int)$this->getPassed()],
            'progressed' => ['integer', (int)$this->getProgressed()],
            'satisfied' => ['integer', (int)$this->getSatisfied()],
            'c_terminated' => ['integer', (int)$this->getTerminated()],
            'hide_data' => ['integer', (int)$this->getHideData()],
            'c_timestamp' => ['integer', (int)$this->getTimestamp()],
            'duration' => ['integer', (int)$this->getDuration()],
            'no_substatements' => ['integer', (int)$this->getNoSubstatements()]
        ]);
        
        $this->saveRepositoryActivationSettings();
    }
    
    protected function loadRepositoryActivationSettings()
    {
        if ($this->ref_id) {
            include_once "./Services/Object/classes/class.ilObjectActivation.php";
            $activation = ilObjectActivation::getItem($this->ref_id);
            switch ($activation["timing_type"]) {
                case ilObjectActivation::TIMINGS_ACTIVATION:
                    $this->setActivationLimited(true);
                    $this->setActivationStartingTime($activation["timing_start"]);
                    $this->setActivationEndingTime($activation["timing_end"]);
                    $this->setActivationVisibility($activation["visible"]);
                    break;
                
                default:
                    $this->setActivationLimited(false);
                    break;
            }
        }
    }
    
    protected function saveRepositoryActivationSettings()
    {
        if ($this->ref_id) {
            include_once "./Services/Object/classes/class.ilObjectActivation.php";
            ilObjectActivation::getItem($this->ref_id);
            
            $item = new ilObjectActivation;
            if (!$this->isActivationLimited()) {
                $item->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
            } else {
                $item->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
                $item->setTimingStart($this->getActivationStartingTime());
                $item->setTimingEnd($this->getActivationEndingTime());
                $item->toggleVisible($this->getActivationVisibility());
            }
            
            $item->update($this->ref_id);
        }
    }
    
    public static function updatePrivacySettingsFromLrsType(ilCmiXapiLrsType $lrsType)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $tableName = self::DB_TABLE_NAME;
        
        $query = "
			UPDATE {$tableName}
			SET privacy_ident = %s, 
                privacy_name = %s, 
                only_moveon = %s, 
                achieved = %s, 
                answered = %s, 
                completed = %s, 
                failed = %s, 
                initialized = %s, 
                passed = %s, 
                progressed = %s, 
                satisfied = %s, 
                c_terminated = %s, 
                hide_data = %s, 
                c_timestamp = %s, 
                duration = %s, 
                no_substatements = %s
            WHERE lrs_type_id = %s
		";
        
        $DIC->database()->manipulateF(
            $query,
            ['integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer',
             'integer'
            ],
            [$lrsType->getPrivacyIdent(),
             $lrsType->getPrivacyName(),
             $lrsType->getOnlyMoveon(),
             $lrsType->getAchieved(),
             $lrsType->getAnswered(),
             $lrsType->getCompleted(),
             $lrsType->getFailed(),
             $lrsType->getInitialized(),
             $lrsType->getPassed(),
             $lrsType->getProgressed(),
             $lrsType->getSatisfied(),
             $lrsType->getTerminated(),
             $lrsType->getHideData(),
             $lrsType->getTimestamp(),
             $lrsType->getDuration(),
             $lrsType->getNoSubstatements(),
             $lrsType->getTypeId()
            ]
        );
    }
    
    public static function updateByPassProxyFromLrsType(ilCmiXapiLrsType $lrsType)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $tableName = self::DB_TABLE_NAME;
        
        $query = "
			UPDATE {$tableName}
			SET bypass_proxy = %s
			WHERE lrs_type_id = %s
		";
        
        $DIC->database()->manipulateF(
            $query,
            ['integer', 'integer'],
            [$lrsType->isBypassProxyEnabled(), $lrsType->getTypeId()]
        );
    }

    public static function getObjectsHavingBypassProxyEnabledAndRegisteredUsers()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $query = "
			SELECT DISTINCT s.obj_id FROM cmix_settings s
			INNER JOIN cmix_users u ON u.obj_id = s.obj_id
			WHERE bypass_proxy = %s
		";
        
        $res = $DIC->database()->queryF($query, array('integer'), array(1));
        
        $objects = array();
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $objects[] = $row['obj_id'];
        }
        
        return $objects;
    }



    /////////////////////////////////////////
    /// HIGHSCORE

    /**
     * @var int
     */
    protected $_highscore_enabled = 0;


    /**
     * @var int
     */
    protected $anonymity = 0;

    /**
     * @var int
     */
    protected $_highscore_achieved_ts = 1;

    /**
     * @var int
     */
    protected $_highscore_percentage = 1;

    /**
     * @var int
     */
    protected $_highscore_wtime = 1;

    /**
     * @var int
     */
    protected $_highscore_own_table = 1;

    /**
     * @var int
     */
    protected $_highscore_top_table = 1;

    /**
     * @var int
     */
    protected $_highscore_top_num = 10;
    
    const HIGHSCORE_SHOW_ALL_TABLES = 1;
    const HIGHSCORE_SHOW_TOP_TABLE = 2;
    const HIGHSCORE_SHOW_OWN_TABLE = 3;
    
    
    
    /**
     * Sets if the highscore feature should be enabled.
     *
     * @param bool $a_enabled
     */
    public function setHighscoreEnabled($a_enabled)
    {
        $this->_highscore_enabled = (bool) $a_enabled;
    }

    /**
     * Gets the setting which determines if the highscore feature is enabled.
     *
     * @return bool True, if highscore is enabled.
     */
    public function getHighscoreEnabled()
    {
        return (bool) $this->_highscore_enabled;
    }

    /**
     * Sets if the date and time of the scores achievement should be displayed.
     *
     * @param bool $a_achieved_ts
     */
    public function setHighscoreAchievedTS($a_achieved_ts)
    {
        $this->_highscore_achieved_ts = (bool) $a_achieved_ts;
    }

    /**
     * Returns if date and time of the scores achievement should be displayed.
     *
     * @return bool True, if column should be shown.
     */
    public function getHighscoreAchievedTS()
    {
        return (bool) $this->_highscore_achieved_ts;
    }

    /**
     * Sets if the percentages of the scores pass should be shown.
     *
     * @param bool $a_percentage
     */
    public function setHighscorePercentage($a_percentage)
    {
        $this->_highscore_percentage = (bool) $a_percentage;
    }

    /**
     * Gets if the percentage column should be shown.
     *
     * @return bool True, if percentage column should be shown.
     */
    public function getHighscorePercentage()
    {
        return (bool) $this->_highscore_percentage;
    }

    /**
     * Sets if the workingtime of the scores should be shown.
     *
     * @param bool $a_wtime
     */
    public function setHighscoreWTime($a_wtime)
    {
        $this->_highscore_wtime = (bool) $a_wtime;
    }

    /**
     * Gets if the column with the workingtime should be shown.
     *
     * @return bool True, if the workingtime column should be shown.
     */
    public function getHighscoreWTime()
    {
        return (bool) $this->_highscore_wtime;
    }

    /**
     * Sets if the table with the own ranking should be shown.
     *
     * @param bool $a_own_table True, if table with own ranking should be shown.
     */
    public function setHighscoreOwnTable($a_own_table)
    {
        $this->_highscore_own_table = (bool) $a_own_table;
    }

    /**
     * Gets if the own rankings table should be shown.
     *
     * @return bool True, if the own rankings table should be shown.
     */
    public function getHighscoreOwnTable()
    {
        return (bool) $this->_highscore_own_table;
    }

    /**
     * Sets if the top-rankings table should be shown.
     *
     * @param bool $a_top_table
     */
    public function setHighscoreTopTable($a_top_table)
    {
        $this->_highscore_top_table = (bool) $a_top_table;
    }

    /**
     * Gets, if the top-rankings table should be shown.
     *
     * @return bool True, if top-rankings table should be shown.
     */
    public function getHighscoreTopTable()
    {
        return (bool) $this->_highscore_top_table;
    }

    /**
     * Sets the number of entries which are to be shown in the top-rankings
     * table.
     *
     * @param integer $a_top_num Number of entries in the top-rankings table.
     */
    public function setHighscoreTopNum($a_top_num)
    {
        $this->_highscore_top_num = (int) $a_top_num;
    }

    /**
     * Gets the number of entries which are to be shown in the top-rankings table.
     * Default: 10 entries
     *
     * @param integer $a_retval Optional return value if nothing is set, defaults to 10.
     *
     * @return integer Number of entries to be shown in the top-rankings table.
     */
    public function getHighscoreTopNum($a_retval = 10)
    {
        $retval = $a_retval;
        if ((int) $this->_highscore_top_num != 0) {
            $retval = $this->_highscore_top_num;
        }

        return $retval;
    }

    /**
     * @return int
     */
    public function getHighscoreMode()
    {
        switch (true) {
            case $this->getHighscoreOwnTable() && $this->getHighscoreTopTable():
                return self::HIGHSCORE_SHOW_ALL_TABLES;
                break;

            case $this->getHighscoreTopTable():
                return self::HIGHSCORE_SHOW_TOP_TABLE;
                break;

            case $this->getHighscoreOwnTable():
            default:
                return self::HIGHSCORE_SHOW_OWN_TABLE;
                break;
        }
    }

    /**
     * @param $mode int
     */
    public function setHighscoreMode($mode)
    {
        switch ($mode) {
            case self::HIGHSCORE_SHOW_ALL_TABLES:
                $this->setHighscoreTopTable(1);
                $this->setHighscoreOwnTable(1);
                break;

            case self::HIGHSCORE_SHOW_TOP_TABLE:
                $this->setHighscoreTopTable(1);
                $this->setHighscoreOwnTable(0);
                break;

            case self::HIGHSCORE_SHOW_OWN_TABLE:
            default:
                $this->setHighscoreTopTable(0);
                $this->setHighscoreOwnTable(1);
                break;
        }
    }

    /* End GET/SET for highscore feature*/


    public function getDataSetMapping()
    {
        if (null === ($lrsTypeId = $this->getLrsTypeId())) {
            $this->doRead();
        }
        $mapping = [
            'obj_id' => $this->getId(),
            'lrs_type_id' => $this->getLrsTypeId(),
            'content_type' => $this->getContentType(),
            'source_type' => $this->getSourceType(),
            'activity_id' => $this->getActivityId(),
            'publisher_id' => $this->getPublisherId(),
            'instructions' => $this->getInstructions(),
            'launch_url' => $this->getLaunchUrl(),
            'launch_parameters' => $this->getLaunchParameters(),
            'moveon' => $this->getMoveOn(),
            'entitlement_key' => $this->getEntitlementKey(),
            'auth_fetch_url' => (int) $this->isAuthFetchUrlEnabled(),
            'launch_method' => $this->getLaunchMethod(),
            'launch_mode' => $this->getLaunchMode(),
            'switch_to_review' => (int) $this->isSwitchToReviewEnabled(),
            'mastery_score' => $this->getMasteryScore(),
            'keep_lp' => (int) $this->isKeepLpStatusEnabled(),
            'privacy_ident' => $this->getPrivacyIdent(),
            'privacy_name' => $this->getPrivacyName(),
            'usr_privacy_comment' => $this->getUserPrivacyComment(),
            'show_statements' => (int) $this->isStatementsReportEnabled(),
            'xml_manifest' => $this->getXmlManifest(),
            'version' => $this->getVersion(),
            'highscore_enabled' => (int) $this->getHighscoreEnabled(),
            'highscore_achieved_ts' => (int) $this->getHighscoreAchievedTS(),
            'highscore_percentage' => (int) $this->getHighscorePercentage(),
            'highscore_wtime' => (int) $this->getHighscoreWTime(),
            'highscore_own_table' => (int) $this->getHighscoreOwnTable(),
            'highscore_top_table' => (int) $this->getHighscoreTopTable(),
            'highscore_top_num' => (int) $this->getHighscoreTopNum(),
            'only_moveon' => (int) $this->getOnlyMoveon(),
            'achieved' => (int) $this->getAchieved(),
            'answered' => (int) $this->getAnswered(),
            'completed' => (int) $this->getCompleted(),
            'failed' => (int) $this->getFailed(),
            'initialized' => (int) $this->getInitialized(),
            'passed' => (int) $this->getPassed(),
            'progressed' => (int) $this->getProgressed(),
            'satisfied' => (int) $this->getSatisfied(),
            'c_terminated' => (int) $this->getTerminated(),
            'hide_data' => (int) $this->getHideData(),
            'c_timestamp' => (int) $this->getTimestamp(),
            'duration' => (int) $this->getDuration(),
            'no_substatements' => (int) $this->getNoSubstatements()
			//'bypass_proxy' => (int) $this->isBypassProxyEnabled()
        ];
        return $mapping;
    }
	
	 /**
     * Clone object
     *
     * @access public
     * @param int ref_id of target container
     * @param int copy id
     * @return object new cmix object
     */
	protected function doCloneObject($new_obj, $a_target_id, $a_copy_id = null, $a_omit_tree = false)
    {
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		$this->cloneMetaData($new_obj);

		$new_obj->setLrsTypeId($this->getLrsTypeId());
		$new_obj->setContentType($this->getContentType());
		$new_obj->setSourceType($this->getSourceType());
        $new_obj->setActivityId($this->getActivityId());
        $new_obj->setPublisherId($this->getPublisherId());
		$new_obj->setInstructions($this->getInstructions());
        $new_obj->setLaunchUrl($this->getLaunchUrl());
        $new_obj->setLaunchParameters($this->getLaunchParameters());
        $new_obj->setMoveOn($this->getMoveOn());
        $new_obj->setEntitlementKey($this->getEntitlementKey());
		$new_obj->setAuthFetchUrlEnabled($this->isAuthFetchUrlEnabled());
		$new_obj->setLaunchMethod($this->getLaunchMethod());
        $new_obj->setLaunchMode($this->getLaunchMode());
        $new_obj->setSwitchToReviewEnabled($this->isSwitchToReviewEnabled());
		$new_obj->setMasteryScore($this->getMasteryScore());
		$new_obj->setKeepLpStatusEnabled($this->isKeepLpStatusEnabled());
		$new_obj->setPrivacyIdent($this->getPrivacyIdent());
		$new_obj->setPrivacyName($this->getPrivacyName());
		$new_obj->setUserPrivacyComment($this->getUserPrivacyComment());
		$new_obj->setStatementsReportEnabled($this->isStatementsReportEnabled());
		$new_obj->setXmlManifest($this->getXmlManifest());
		$new_obj->setVersion($this->getVersion());
		$new_obj->setHighscoreEnabled($this->getHighscoreEnabled());
		$new_obj->setHighscoreAchievedTS($this->getHighscoreAchievedTS());
		$new_obj->setHighscorePercentage($this->getHighscorePercentage());
		$new_obj->setHighscoreWTime($this->getHighscoreWTime());
		$new_obj->setHighscoreOwnTable($this->getHighscoreOwnTable());
		$new_obj->setHighscoreTopTable($this->getHighscoreTopTable());
		$new_obj->setHighscoreTopNum($this->getHighscoreTopNum());
		$new_obj->setBypassProxyEnabled($this->isBypassProxyEnabled());
        $new_obj->setOnlyMoveon($this->getOnlyMoveon());
        $new_obj->setAchieved($this->getAchieved());
        $new_obj->setAnswered($this->getAnswered());
        $new_obj->setCompleted($this->getCompleted());
        $new_obj->setFailed($this->getFailed());
        $new_obj->setInitialized($this->getInitialized());
        $new_obj->setPassed($this->getPassed());
        $new_obj->setProgressed($this->getProgressed());
        $new_obj->setSatisfied($this->getSatisfied());
        $new_obj->setTerminated($this->getTerminated());
        $new_obj->setHideData($this->getHideData());
        $new_obj->setTimestamp($this->getTimestamp());
        $new_obj->setDuration($this->getDuration());
        $new_obj->setNoSubstatements($this->getNoSubstatements());
        $new_obj->update();
		
		if ($this->getSourceType() == self::SRC_TYPE_LOCAL) {
		    $dirUtil = new ilCmiXapiContentUploadImporter($new_obj);
			$dirUtil->ensureCreatedObjectDirectory();
			$newDir = implode(DIRECTORY_SEPARATOR, [\ilUtil::getWebspaceDir(), $dirUtil->getWebDataDirRelativeObjectDirectory()]);
			$dirUtil = new ilCmiXapiContentUploadImporter($this);
			$thisDir = implode(DIRECTORY_SEPARATOR, [\ilUtil::getWebspaceDir(), $dirUtil->getWebDataDirRelativeObjectDirectory()]);
			ilUtil::rCopy($thisDir, $newDir);
		}
	}

    protected function doDelete()
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        // delete file data entry
        $q = "DELETE FROM cmix_settings WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer');
        $this->ilias->db->query($q);

        // delete history entries
        require_once("./Services/History/classes/class.ilHistory.php");
        ilHistory::_removeEntriesForObject($this->getId());

        
        // delete entire directory and its content
		$dirUtil = new ilCmiXapiContentUploadImporter($this);
		$thisDir = implode(DIRECTORY_SEPARATOR, [\ilUtil::getWebspaceDir(), $dirUtil->getWebDataDirRelativeObjectDirectory()]);
        if (is_dir($thisDir)) {
            ilUtil::delDir($thisDir);
        }

        // delete meta data
        $this->deleteMetaData();
    }

    public function getRegistrations() {
        global $DIC;
        $res = $DIC->database()->queryF(
            "SELECT DISTINCT registration FROM cmix_users WHERE obj_id = %s",
            array('text'),
            array($this->getId())
        );
        $ret = [];
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $ret[] = $row['registration'];
        }
        return $ret;
    }

    public static function guidv4($data = null) {
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

    public function getCurrentCmixUser()
    {
        global $DIC;
        if (null === $this->currentCmixUser)
        {
            $this->currentCmixUser = new ilCmiXapiUser($this->getId(), $DIC->user()->getId(), $this->getPrivacyIdent());
        }
        return $this->currentCmixUser;
    }

    public function getSessionId($cmixUser = null) 
    {
        if (null === $cmixUser)
        {
            $cmixUser = $this->getCurrentCmixUser();
        }
        return ilCmiXapiAuthToken::getCmi5SessionByUsrIdAndObjIdAndRefId($cmixUser->getUsrId(),$this->getId(), $this->getRefId());
    }

    /**
     * LMS.LaunchData
     */
    public function getLaunchData($cmixUser = null, $lang = 'en')
    {
        if (null === $cmixUser)
        {
            $cmixUser = $this->getCurrentCmixUser();
        }
        // ToDo
        $moveOn = $this->getLMSMoveOn();
        if (!$moveOn || $moveOn == '')
        {
            $moveOn = 'Completed';
        }
        $launchMode = $this->getLaunchMode();
        // only check switch if self::LAUNCH_MODE_NORMAL
        if ($launchMode == self::LAUNCH_MODE_NORMAL) {
            if ($cmixUser->getSatisfied() && $this->isSwitchToReviewEnabled()) {
                $launchMode = self::LAUNCH_MODE_REVIEW;
            }
        }
        $ctxTemplate = [
            "contextTemplate" => $this->getLaunchedContextTemplate($cmixUser),
            "launchMode" => ucfirst($launchMode),
            "launchMethod" => "OwnWindow",
            "moveOn" => $moveOn
        ];
        $lmsLaunchMethod = $this->getLaunchMethod();
        if ($lmsLaunchMethod === "ownWin") {
            include_once('./Services/Link/classes/class.ilLink.php');
            $href = ilLink::_getStaticLink(
                $this->getRefId(),
                $this->getType()
            );
            $ctxTemplate['returnURL'] = $href;
        }
        else {
            $ctxTemplate['returnURL'] = ILIAS_HTTP_PATH."/Modules/CmiXapi/xapiexit.php?lang={$lang}";
        }
        if (!empty($this->getMasteryScore())) {
            $ctxTemplate['masteryScore'] = $this->getMasteryScore();
        }
        if (!empty($this->getLaunchParameters())) {
            $ctxTemplate['launchParameters'] = $this->getLaunchParameters();
        }
        if (!empty($this->getEntitlementKey())) {
            $ctxTemplate['entitlementKey'] = array("courseStructure" => $this->getEntitlementKey());
        }
        return $ctxTemplate;
    }

    public function getLaunchedContextTemplate($cmixUser = null) 
    {
        if (null === $cmixUser)
        {
            $cmixUser = $this->getCurrentCmixUser();
        }
        $launchMode = $this->getLaunchMode();
        // only check switch if self::LAUNCH_MODE_NORMAL
        if ($launchMode == self::LAUNCH_MODE_NORMAL) {
            if ($cmixUser->getSatisfied() && $this->isSwitchToReviewEnabled()) {
                $launchMode = self::LAUNCH_MODE_REVIEW;
            }
        }
        $extensions = $this->getStatementExtensions($cmixUser);
        $extensions['https://w3id.org/xapi/cmi5/context/extensions/launchmode'] =  $launchMode;
        if (!empty($this->getLMSMoveOn())) {
            $extensions['https://w3id.org/xapi/cmi5/context/extensions/moveon'] = $this->getLMSMoveOn();
        }
        if (!empty($this->getLaunchParameters())) {
            $extensions['https://w3id.org/xapi/cmi5/context/extensions/launchparameters'] = $this->getLaunchParameters();
        }
        if (!empty($this->getMasteryScore())) {
            $extensions['https://w3id.org/xapi/cmi5/context/extensions/masteryscore'] = $this->getMasteryScore();
        }
        $contextTemplate = array(
            "contextActivities" => $this->getStatementContextActivities(),
            "extensions" => $extensions
        );
        return $contextTemplate;
    }

    /**
     * blueprint statement
     */
    public function getStatement(string $verb, $cmixUser = null)
    {
        if (null === $cmixUser)
        {
            $cmixUser = $this->getCurrentCmixUser();
        }
        $id = self::guidv4();
        $actor = $this->getStatementActor($cmixUser);
        $verbUri = ilCmiXapiVerbList::getInstance()->getVerbUri($verb);
        $extensions = $this->getStatementExtensions($cmixUser);
        $registration = $cmixUser->getRegistration();
        $contextActivities = $this->getStatementContextActivities();
        $object = $this->getStatementObject();
        $statement = array (
            'id' => $id,
            'actor' => $actor,
            'verb' => 
            array (
                'id' => $verbUri
            ),
            'context' => 
            array (
                'extensions' => $extensions,
                'registration' => $registration,
                'contextActivities' => $contextActivities
            ),
            'object' => $object
        );
        return $statement;
    }

    /**
     * statement actor
     */
    public function getStatementActor($cmixUser = null)
    {
        global $DIC;
        if (null === $cmixUser)
        {
            $cmixUser = $this->getCurrentCmixUser();
        }
        $user = new ilObjUser($cmixUser->getUsrId()); // ToDo: Caching Names
        $name = ilCmiXapiUser::getName($this->getPrivacyName(), $user);
        if ($name == '') {
            $this->log()->error('error: no name in cmixuser');
            $name = 'UNDEFINED';
        }
        $homePage = ($this->anonymousHomePage == true) ? self::ANONYMOUS_HOMEPAGE : self::iliasUrl();
        if ($this->getContentType() == self::CONT_TYPE_CMI5)
        {
            $actor = [
                'objectType' => 'Agent',
                'account' => [
                    'homePage' => $homePage,
                    'name' => $cmixUser->getUsrIdent()
                ]
            ];
            if ($name !== '')
            {
                $actor['name'] = $name;
            }
        }
        else
        {
            $actor = [
                'objectType' => 'Agent',
                'mbox' => $cmixUser->getUsrIdent()
            ];
            if ($name !== '')
            {
                $actor['name'] = $name;
            }
        }
        return $actor;     
    }

    /**
     * Minimal extensions
     */
    public function getStatementExtensions($cmixUser = null)
    {
        if (null === $cmixUser)
        {
            $cmixUser = $this->getCurrentCmixUser();
        }
        $extensions = array (
            'https://w3id.org/xapi/cmi5/context/extensions/sessionid' => $this->getSessionId($cmixUser),
            'https://ilias.de/cmi5/activityid' => $this->getActivityId()
        );
        return $extensions;
    }

    /**
     * Minimal statementActivities
     */
    public function getStatementContextActivities()
    {
        $publisherId = $this->getPublisherId();
        $activityId = $this->getActivityId();
        if (empty($publisherId)) 
        {
            $publisherId = $activityId;
        }
        $ctxActivities = array(
            "grouping" => [
                [
                "objectType" => "Activity",
                "id" => "{$publisherId}",
                'definition' => 
                array (
                    'name' => 
                    array (
                        'de-DE' => $this->getTitle(),
                        'en-US' => $this->getTitle()
                    ),
                    'description' => 
                    array (
                        'de-DE' => $this->getDescription(),
                        'en-US' => $this->getDescription()
                    )
                )]
            ],
            "category" => [
                [
                    "id" => "https://w3id.org/xapi/cmi5/context/categories/cmi5",
                    "objectType" => "Activity"
                ]
            ]
        );
        return $ctxActivities;
    }

    public function getStatementObject()
    {
        $object = array (
                'id' => $this->getActivityId(),
                'definition' => 
                array (
                    'name' => 
                    array (
                        'de-DE' => $this->getTitle(),
                        'en-US' => $this->getTitle()
                    ),
                    'description' => 
                    array (
                        'de-DE' => $this->getDescription(),
                        'en-US' => $this->getDescription()
                    )
                )
            );
        return $object;
    }

    public function getLaunchedStatement($cmixUser = null)
    {
        if (null === $cmixUser) {
            $cmixUser = $this->getCurrentCmixUser();
        }
        $launchMode = $this->getLaunchMode();
        // only check switch if self::LAUNCH_MODE_NORMAL
        if ($launchMode == self::LAUNCH_MODE_NORMAL) {
            if ($cmixUser->getSatisfied() && $this->isSwitchToReviewEnabled()) {
                $launchMode = self::LAUNCH_MODE_REVIEW;
            }
        }
        
        $statement = $this->getStatement('launched', $cmixUser);
        $statement['context']['extensions']['https://w3id.org/xapi/cmi5/context/extensions/launchmode'] = $launchMode;
        if (!empty($this->getLMSMoveOn())) {
            $statement['context']['extensions']['https://w3id.org/xapi/cmi5/context/extensions/moveon'] = $this->getLMSMoveOn();
        }
        if (!empty($this->getLaunchParameters())) {
            $statement['context']['extensions']['https://w3id.org/xapi/cmi5/context/extensions/launchparameters'] = $this->getLaunchParameters();
        }
        if (!empty($this->getMasteryScore())) {
            $statement['context']['extensions']['https://w3id.org/xapi/cmi5/context/extensions/masteryscore'] = $this->getMasteryScore();
        }
        return $statement;
    }

    public function getAbandonedStatement($sessionId, $duration, $cmixUser = null)
    {
        if (null === $cmixUser)
        {
            $cmixUser = $this->getCurrentCmixUser();
        }
        $statement = $this->getStatement('abandoned',$cmixUser);
        // overwrite session with abandoned oldSession
        $statement['context']['extensions']['https://w3id.org/xapi/cmi5/context/extensions/sessionid'] = $sessionId;
        $statement['result'] = array(
            'duration' => $duration
        );
        return $statement;
    }

    public function getSatisfiedStatement($cmixUser = null)
    {
        if (null === $cmixUser)
        {
            $cmixUser = $this->getCurrentCmixUser();
        }
        $statement = $this->getStatement('satisfied', $cmixUser);
        // add type, see https://aicc.github.io/CMI-5_Spec_Current/samples/scenarios/16-not_applicable-no_launch/#satisfied-statement
        // see also: https://github.com/AICC/CMI-5_Spec_Current/blob/quartz/cmi5_spec.md#verbs_satisfied
        $type = "https://w3id.org/xapi/cmi5/activitytype/course";
        $statement['object']['definition']['type'] = $type;
        $statement['context']['contextActivities']['grouping'][0]['definition']['type'] = $type;
        return $statement;
    }

    /**
     * get latest statement from session
     */
    public function getLastStatement($sess)
    {
        global $DIC;
        $lrsType = $this->getLrsType();

        //$this->getLrsEndpoint())) . '/api/' . self::ENDPOINT_AGGREGATE_SUFFIX;
        $defaultLrs = $lrsType->getLrsEndpointStatementsAggregationLink();
        //$fallbackLrs = $lrsType->getLrsFallbackEndpoint();
        $defaultBasicAuth = $lrsType->getBasicAuth();
        //$fallbackBasicAuth = $lrsType->getFallbackBasicAuth();
        $defaultHeaders = [
            'X-Experience-API-Version' => '1.0.3',
            'Authorization' => $defaultBasicAuth,
            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ];
        /*
        $fallbackHeaders = [
            'X-Experience-API-Version' => '1.0.3',
            'Authorization' => $fallbackBasicAuth,
            'Content-Type' => 'application/json;charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate'
        ];
        */
        $pipeline = json_encode($this->getLastStatementPipline($sess));
        $defaultLastStatementUrl = $defaultLrs . "?pipeline=" . urlencode($pipeline);
        $client = new GuzzleHttp\Client();
        $req_opts = array(
            GuzzleHttp\RequestOptions::VERIFY => true,
            GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 10,
            GuzzleHttp\RequestOptions::HTTP_ERRORS => false
        );
        $defaultLastStatementRequest = new GuzzleHttp\Psr7\Request(
            'GET',
            $defaultLastStatementUrl,
            $defaultHeaders
        );
        $promises = array();
        $promises['defaultLastStatement'] = $client->sendAsync($defaultLastStatementRequest, $req_opts);
        try
        {
            $responses = GuzzleHttp\Promise\settle($promises)->wait();
            $body = '';
            ilCmiXapiAbstractRequest::checkResponse($responses['defaultLastStatement'],$body,[200]);
            return json_decode($body,JSON_OBJECT_AS_ARRAY);
        }
        catch(Exception $e)
        {
            $this->log()->error('error:' . $e->getMessage());
            return null;
        }
        return null;
    }

    public function getLastStatementPipline($sess)
    {
        global $DIC;
        $pipeline = array();
        
        // filter activityId
        $match = array();
        $match['statement.object.objectType'] = 'Activity';
        $match['statement.actor.objectType'] = 'Agent';
        
        $activityId = array();

        if ($this->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5 && !$this->isMixedContentType())
        {
            // https://github.com/AICC/CMI-5_Spec_Current/blob/quartz/cmi5_spec.md#963-extensions
            $activityId['statement.context.extensions.https://ilias&46;de/cmi5/activityid'] = $this->getActivityId();
        }
        else
        {
            $activityQuery = [
                '$regex' => '^' . preg_quote($this->getActivityId()) . ''
            ];
            $activityId['$or'] = [];
            $activityId['$or'][] = ['statement.object.id' => $activityQuery];
            $activityId['$or'][] = ['statement.context.contextActivities.parent.id' => $activityQuery];
        }

        $sessionId = array();
        $sessionId['statement.context.extensions.https://w3id&46;org/xapi/cmi5/context/extensions/sessionid'] = $sess;
        $match['$and'] = array();
        $match['$and'][] = $activityId;
        $match['$and'][] = $sessionId;
        $sort = array('statement.timestamp' => -1);
        $project = array('statement.timestamp' => 1, 'statement.verb.id' => 1);
        $pipeline[] = array('$match' => $match);
        $pipeline[] = array('$sort' => $sort);
        $pipeline[] = array('$limit' => 1);
        $pipeline[] = array('$project' => $project);

        return $pipeline;
    }

    public static function iliasUrl() {
        $regex = '/^(https?\:\/\/[^\/]+).*/';
        preg_match($regex, $GLOBALS['DIC']->http()->request()->getUri(), $request_parts);
        return $request_parts[1];
    }

    public static function log() {
        global $log;
        if (self::PLUGIN) {
            return $log;
        }
        else {
            return \ilLoggerFactory::getLogger('cmix');
        }
    }
}
