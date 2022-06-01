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
    const DB_USERS_TABLE_NAME = 'cmix_users';
    const DB_RESULTS_TABLE_NAME = 'cmix_results';
    
    /**
     * repository object activation settings (handled by ilObject)
     */
    protected ?bool $activationLimited = null;
    protected ?int $activationStartingTime = null;
    protected ?int $activationEndingTime = null;
    protected ?bool $activationVisibility = null;

    protected ?int $lrsTypeId;
 
    protected ilCmiXapiLrsType $lrsType;

    protected string $contentType;
    const CONT_TYPE_GENERIC = 'generic';
    const CONT_TYPE_CMI5 = 'cmi5';
 
    protected string $sourceType;
    const SRC_TYPE_REMOTE = 'remoteSource';
    const SRC_TYPE_LOCAL = 'localSource';
    const SRC_TYPE_EXTERNAL = 'externalSource';

    protected string $activityId;

    protected string $publisherId;

    protected string $instructions;
    
    protected string $launchUrl;

    protected string $launchParameters;
    
    protected string $moveOn;

    protected string $entitlementKey;

    protected bool $authFetchUrlEnabled = false;

    protected bool $anonymousHomePage = false;
    const ANONYMOUS_HOMEPAGE = 'https://example.org';

    protected string $launchMethod;
    const LAUNCH_METHOD_OWN_WIN = 'ownWin';
    const LAUNCH_METHOD_NEW_WIN = 'newWin';
    const LAUNCH_METHOD_IFRAME = 'iframe';

    protected string $launchMode;
    const LAUNCH_MODE_NORMAL = 'Normal';
    const LAUNCH_MODE_BROWSE = 'Browse';
    const LAUNCH_MODE_REVIEW = 'Review';

    protected bool $switchToReviewEnabled = false;

    protected float $masteryScore;
    const LMS_MASTERY_SCORE = 0.7;

    protected bool $keepLpStatusEnabled = false;

    protected int $userIdent;
    const PRIVACY_IDENT_IL_UUID_USER_ID = 0;
    const PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT = 1;
    const PRIVACY_IDENT_IL_UUID_LOGIN = 2;
    const PRIVACY_IDENT_REAL_EMAIL = 3;
    const PRIVACY_IDENT_IL_UUID_RANDOM = 4;

    protected int $userName;
    const PRIVACY_NAME_NONE = 0;
    const PRIVACY_NAME_FIRSTNAME = 1;
    const PRIVACY_NAME_LASTNAME = 2;
    const PRIVACY_NAME_FULLNAME = 3;

    protected string $userPrivacyComment = "";

    protected bool $statementsReportEnabled = false;

    protected string $xmlManifest = "";

    protected int $version;
 
    protected bool $bypassProxyEnabled = false;

    protected bool $only_moveon = false;

    protected bool $achieved = true;

    protected bool $answered = true;

    protected bool $completed = true;

    protected bool $failed = true;

    protected bool $initialized = true;

    protected bool $passed = true;

    protected bool $progressed = true;

    protected bool $satisfied = true;

    protected bool $terminated = true;

    protected bool $hide_data = false;

    protected bool $timestamp = false;

    protected bool $duration = true;

    protected bool $no_substatements = false;

    protected ?ilCmiXapiUser $currentCmixUser = null;

    private ilDBInterface $database;

    /**
     * ilObjCmiXapi constructor.
     */
    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        global $DIC;
        $this->database = $DIC->database();

        $this->lrsTypeId = 0;
        
        $this->contentType = self::CONT_TYPE_GENERIC;
        $this->sourceType = self::SRC_TYPE_REMOTE;
        
        $this->activityId = '';
        
        $this->publisherId = '';

        $this->instructions = '';

        $this->launchUrl = '';
        $this->launchParameters = '';
        $this->moveOn = '';
        $this->entitlementKey = '';
        
        $this->authFetchUrlEnabled = false;
        
        $this->launchMethod = self::LAUNCH_METHOD_NEW_WIN;
        $this->launchMode = self::LAUNCH_MODE_NORMAL;

        $this->switchToReviewEnabled = true;
        
        $this->masteryScore = self::LMS_MASTERY_SCORE;
        $this->keepLpStatusEnabled = true;
        
        $this->userIdent = self::PRIVACY_IDENT_IL_UUID_USER_ID;
        $this->userName = self::PRIVACY_NAME_NONE;
        $this->userPrivacyComment = '';

        $this->currentCmixUser = null;

        $this->statementsReportEnabled = false;

        $this->xmlManifest = '';
        $this->version = 0;
        
        $this->bypassProxyEnabled = false;

        parent::__construct($a_id, $a_reference);
    }


    public static function getInstance(int $a_id = 0, bool $a_reference = true) : \ilObjCmiXapi
    {
        return new self($a_id, $a_reference);
    }
    
    protected function initType() : void
    {
        $this->type = "cmix";
    }
    
    public function getLrsTypeId() : ?int
    {
        return $this->lrsTypeId;
    }
    
    public function setLrsTypeId(int $lrsTypeId) : void
    {
        $this->lrsTypeId = $lrsTypeId;
    }
    
    public function getLrsType() : \ilCmiXapiLrsType
    {
        return $this->lrsType;
    }
    
    public function setLrsType(\ilCmiXapiLrsType $lrsType) : void
    {
        $this->lrsType = $lrsType;
    }
    
    public function initLrsType() : void
    {
        $this->setLrsType(new ilCmiXapiLrsType($this->getLrsTypeId()));
    }
    
    public function getContentType() : string
    {
        return $this->contentType;
    }
    
    public function setContentType(string $contentType) : void
    {
        //bug before 21-07-24
        if ($contentType == "learning") {
            $contentType = self::CONT_TYPE_GENERIC;
        }
        $this->contentType = $contentType;
    }

    public function isMixedContentType() : bool
    {
        // after 21-07-24 and before cmi5 refactoring
        // launched before cmi5 refactoring ident in:    statement.actor.mbox
        // launched after  cmi5 refactoring ident in:    statement.actor.account.name
        return (($this->getContentType() == self::CONT_TYPE_CMI5) && empty($this->getPublisherId()));
    }

    public function getSourceType() : string
    {
        return $this->sourceType;
    }
    
    public function isSourceTypeRemote() : bool
    {
        return $this->sourceType == self::SRC_TYPE_REMOTE;
    }
    
    public function isSourceTypeExternal() : bool
    {
        return $this->sourceType == self::SRC_TYPE_EXTERNAL;
    }
    
    public function setSourceType(string $sourceType) : void
    {
        $this->sourceType = $sourceType;
    }
    
    public function getActivityId() : string
    {
        return $this->activityId;
    }
    
    public function setActivityId(string $activityId) : void
    {
        $this->activityId = $activityId;
    }
    
    public function getPublisherId() : string
    {
        return $this->publisherId;
    }
    
    public function setPublisherId(string $publisherId) : void
    {
        $this->publisherId = $publisherId;
    }

    public function getInstructions() : string
    {
        return $this->instructions;
    }
    
    public function setInstructions(string $instructions) : void
    {
        $this->instructions = $instructions;
    }

    public function getLaunchUrl() : string
    {
        return $this->launchUrl;
    }
    
    public function setLaunchUrl(string $launchUrl) : void
    {
        $this->launchUrl = $launchUrl;
    }
    
    public function getLaunchParameters() : string
    {
        return $this->launchParameters;
    }
    
    public function setLaunchParameters(string $launchParameters) : void
    {
        $this->launchParameters = $launchParameters;
    }

    /**
     * Attention: this is the original imported moveOn
     * for using in LaunchData and LaunchStatement use getLMSMoveOn!
     */
    public function getMoveOn() : string
    {
        return $this->moveOn;
    }

    /**
     * Attention: this is the original moveOn from course import
     * should only be set on import!
     */
    public function setMoveOn(string $moveOn) : void
    {
        $this->moveOn = $moveOn;
    }

    /**
     * only for internal LMS usage
     * @return int ilLPObjSettings::const
     */
    public function getLPMode() : int
    {
        $olp = ilObjectLP::getInstance($this->getId());
        return $olp->getCurrentMode();
    }

    /**
     * for CMI5 statements | state moveOn values
     * @return string ilCmiXapiLP::const
     */
    public function getLMSMoveOn() : string
    {
        $moveOn = ilCmiXapiLP::MOVEON_NOT_APPLICABLE;
        switch ($this->getLPMode()) {
            case ilLPObjSettings::LP_MODE_DEACTIVATED:
                $moveOn = ilCmiXapiLP::MOVEON_NOT_APPLICABLE;
            break;
            case ilLPObjSettings::LP_MODE_CMIX_COMPLETED:
            case ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED:
                $moveOn = ilCmiXapiLP::MOVEON_COMPLETED;
            break;
            case ilLPObjSettings::LP_MODE_CMIX_PASSED:
            case ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED:
                $moveOn = ilCmiXapiLP::MOVEON_PASSED;
            break;
                case ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED:
                case ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED:
                $moveOn = ilCmiXapiLP::MOVEON_COMPLETED_OR_PASSED;
            break;
        }
        return $moveOn;
    }

    public function getEntitlementKey() : string
    {
        return $this->entitlementKey;
    }

    public function setEntitlementKey(string $entitlementKey) : void
    {
        $this->entitlementKey = $entitlementKey;
    }

    public function isAuthFetchUrlEnabled() : bool
    {
        return $this->authFetchUrlEnabled;
    }
    
    public function setAuthFetchUrlEnabled(bool $authFetchUrlEnabled) : void
    {
        $this->authFetchUrlEnabled = $authFetchUrlEnabled;
    }
    
    public function getLaunchMethod() : string
    {
        return $this->launchMethod;
    }
    
    public function setLaunchMethod(string $launchMethod) : void
    {
        $this->launchMethod = $launchMethod;
    }
    
    public function getLaunchMode() : string
    {
        return ucfirst($this->launchMode);
    }
    
    public function setLaunchMode(string $launchMode) : void
    {
        $this->launchMode = ucfirst($launchMode);
    }
    
    public function isSwitchToReviewEnabled() : bool
    {
        return $this->switchToReviewEnabled;
    }
    
    public function getSwitchToReviewEnabled() : bool
    {
        return $this->switchToReviewEnabled;
    }
    
    public function setSwitchToReviewEnabled(bool $switchToReviewEnabled) : void
    {
        $this->switchToReviewEnabled = $switchToReviewEnabled;
    }

    public function getMasteryScore() : float
    {
        return $this->masteryScore;
    }
    
    public function setMasteryScore(float $masteryScore) : void
    {
        $this->masteryScore = $masteryScore;
    }
    
    public function getMasteryScorePercent() : float
    {
        return $this->masteryScore * 100;
    }
    
    public function setMasteryScorePercent(float $masteryScorePercent) : void
    {
        $this->masteryScore = $masteryScorePercent / 100;
    }
    
    public function isKeepLpStatusEnabled() : bool
    {
        return $this->keepLpStatusEnabled;
    }
    
    public function setKeepLpStatusEnabled(bool $keepLpStatusEnabled) : void
    {
        $this->keepLpStatusEnabled = $keepLpStatusEnabled;
    }
    
    public function getPrivacyIdent() : int
    {
        return $this->userIdent;
    }
    
    public function setPrivacyIdent(int $userIdent) : void
    {
        $this->userIdent = $userIdent;
    }
    
    public function getPrivacyName() : int
    {
        return $this->userName;
    }
    
    public function setPrivacyName(int $userName) : void
    {
        $this->userName = $userName;
    }

    public function getOnlyMoveon() : bool
    {
        return $this->only_moveon;
    }

    public function setOnlyMoveon(bool $only_moveon) : void
    {
        $this->only_moveon = $only_moveon;
    }

    public function getAchieved() : bool
    {
        return $this->achieved;
    }

    public function setAchieved(bool $achieved) : void
    {
        $this->achieved = $achieved;
    }

    public function getAnswered() : bool
    {
        return $this->answered;
    }

    public function setAnswered(bool $answered) : void
    {
        $this->answered = $answered;
    }

    public function getCompleted() : bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed) : void
    {
        $this->completed = $completed;
    }

    public function getFailed() : bool
    {
        return $this->failed;
    }

    public function setFailed(bool $failed) : void
    {
        $this->failed = $failed;
    }

    public function getInitialized() : bool
    {
        return $this->initialized;
    }

    public function setInitialized(bool $initialized) : void
    {
        $this->initialized = $initialized;
    }

    public function getPassed() : bool
    {
        return $this->passed;
    }

    public function setPassed(bool $passed) : void
    {
        $this->passed = $passed;
    }

    public function getProgressed() : bool
    {
        return $this->progressed;
    }

    public function setProgressed(bool $progressed) : void
    {
        $this->progressed = $progressed;
    }

    public function getSatisfied() : bool
    {
        return $this->satisfied;
    }

    public function setSatisfied(bool $satisfied) : void
    {
        $this->satisfied = $satisfied;
    }

    public function getTerminated() : bool
    {
        return $this->terminated;
    }

    public function setTerminated(bool $terminated) : void
    {
        $this->terminated = $terminated;
    }

    public function getHideData() : bool
    {
        return $this->hide_data;
    }

    public function setHideData(bool $hide_data) : void
    {
        $this->hide_data = $hide_data;
    }

    public function getTimestamp() : bool
    {
        return $this->timestamp;
    }

    public function setTimestamp(bool $timestamp) : void
    {
        $this->timestamp = $timestamp;
    }

    public function getDuration() : bool
    {
        return $this->duration;
    }

    public function setDuration(bool $duration) : void
    {
        $this->duration = $duration;
    }

    public function getNoSubstatements() : bool
    {
        return $this->no_substatements;
    }

    public function setNoSubstatements(bool $no_substatements) : void
    {
        $this->no_substatements = $no_substatements;
    }

    public function getUserPrivacyComment() : string
    {
        return $this->userPrivacyComment;
    }
    
    public function setUserPrivacyComment(string $userPrivacyComment) : void
    {
        $this->userPrivacyComment = $userPrivacyComment;
    }
    
    public function isStatementsReportEnabled() : bool
    {
        return $this->statementsReportEnabled;
    }
    
    public function setStatementsReportEnabled(bool $statementsReportEnabled) : void
    {
        $this->statementsReportEnabled = $statementsReportEnabled;
    }
    
    public function getXmlManifest() : string
    {
        return $this->xmlManifest;
    }
    
    public function setXmlManifest(string $xmlManifest) : void
    {
        $this->xmlManifest = $xmlManifest;
    }
    
    public function getVersion() : int
    {
        return $this->version;
    }
    
    public function setVersion(int $version) : void
    {
        $this->version = $version;
    }
    
    public function isBypassProxyEnabled() : bool
    {
        return $this->bypassProxyEnabled;
    }
    
    public function setBypassProxyEnabled(bool $bypassProxyEnabled) : void
    {
        $this->bypassProxyEnabled = $bypassProxyEnabled;
    }

    //todo?
    protected function doRead() : void
    {
        $this->load();
    }
    
    protected function load() : void
    {
        $query = "SELECT * FROM " . self::DB_TABLE_NAME . " WHERE obj_id = %s";
        $res = $this->database->queryF($query, ['integer'], [$this->getId()]);
        
        while ($row = $this->database->fetchAssoc($res)) {
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
            
            $this->setPrivacyIdent((int) $row['privacy_ident']);
            $this->setPrivacyName((int) $row['privacy_name']);

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

    //todo?
    protected function doUpdate() : void
    {
        $this->save();
    }
    
    public function save() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        // not possible: Move Global Access to Constructor
        $DIC->database()->replace(self::DB_TABLE_NAME, [
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
            'highscore_top_num' => ['integer', $this->getHighscoreTopNum()],
            'only_moveon' => ['integer', (int) $this->getOnlyMoveon()],
            'achieved' => ['integer', (int) $this->getAchieved()],
            'answered' => ['integer', (int) $this->getAnswered()],
            'completed' => ['integer', (int) $this->getCompleted()],
            'failed' => ['integer', (int) $this->getFailed()],
            'initialized' => ['integer', (int) $this->getInitialized()],
            'passed' => ['integer', (int) $this->getPassed()],
            'progressed' => ['integer', (int) $this->getProgressed()],
            'satisfied' => ['integer', (int) $this->getSatisfied()],
            'c_terminated' => ['integer', (int) $this->getTerminated()],
            'hide_data' => ['integer', (int) $this->getHideData()],
            'c_timestamp' => ['integer', (int) $this->getTimestamp()],
            'duration' => ['integer', (int) $this->getDuration()],
            'no_substatements' => ['integer', (int) $this->getNoSubstatements()]
        ]);
        
        $this->saveRepositoryActivationSettings();
    }
    
    protected function loadRepositoryActivationSettings() : void
    {
        if ($this->ref_id) {
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
    
    protected function saveRepositoryActivationSettings() : void
    {
        if ($this->ref_id) {
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
    
    public static function updatePrivacySettingsFromLrsType(ilCmiXapiLrsType $lrsType) : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        //not possible: Move Global Access to Constructor
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
    
    public static function updateByPassProxyFromLrsType(ilCmiXapiLrsType $lrsType) : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        // not possible: Move Global Access to Constructor
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

    /**
     * @return mixed[]
     */
    public static function getObjectsHavingBypassProxyEnabledAndRegisteredUsers() : array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        // not possible: Move Global Access to Constructor
        $query = "
			SELECT DISTINCT s.obj_id FROM " . self::DB_TABLE_NAME . " s
			INNER JOIN " . self::DB_USERS_TABLE_NAME . " u ON u.obj_id = s.obj_id
			WHERE bypass_proxy = %s
		";
        
        $res = $DIC->database()->queryF($query, array('integer'), array(1));
        
        $objects = array();
        
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $objects[] = (int) $row['obj_id'];
        }
        
        return $objects;
    }



    /////////////////////////////////////////
    /// HIGHSCORE
    
    protected bool $_highscore_enabled = false;
    
    protected int $anonymity = 0;
    
    protected bool $_highscore_achieved_ts = true;

    protected bool $_highscore_percentage = true;

    protected bool $_highscore_wtime = true;

    protected bool $_highscore_own_table = true;

    protected bool $_highscore_top_table = true;

    protected int $_highscore_top_num = 10;
    
    const HIGHSCORE_SHOW_ALL_TABLES = 1;
    const HIGHSCORE_SHOW_TOP_TABLE = 2;
    const HIGHSCORE_SHOW_OWN_TABLE = 3;

    /**
     * Sets if the highscore feature should be enabled.
     */
    public function setHighscoreEnabled(bool $a_enabled) : void
    {
        $this->_highscore_enabled = $a_enabled;
    }

    /**
     * Gets the setting which determines if the highscore feature is enabled.
     * @return bool True, if highscore is enabled.
     */
    public function getHighscoreEnabled() : bool
    {
        return $this->_highscore_enabled;
    }

    /**
     * Sets if the date and time of the scores achievement should be displayed.
     */
    public function setHighscoreAchievedTS(bool $a_achieved_ts) : void
    {
        $this->_highscore_achieved_ts = $a_achieved_ts;
    }

    /**
     * Returns if date and time of the scores achievement should be displayed.
     * @return bool True, if column should be shown.
     */
    public function getHighscoreAchievedTS() : bool
    {
        return $this->_highscore_achieved_ts;
    }

    /**
     * Sets if the percentages of the scores pass should be shown.
     */
    public function setHighscorePercentage(bool $a_percentage) : void
    {
        $this->_highscore_percentage = $a_percentage;
    }

    /**
     * Gets if the percentage column should be shown.
     * @return bool True, if percentage column should be shown.
     */
    public function getHighscorePercentage() : bool
    {
        return $this->_highscore_percentage;
    }

    /**
     * Sets if the workingtime of the scores should be shown.
     */
    public function setHighscoreWTime(bool $a_wtime) : void
    {
        $this->_highscore_wtime = $a_wtime;
    }

    /**
     * Gets if the column with the workingtime should be shown.
     * @return bool True, if the workingtime column should be shown.
     */
    public function getHighscoreWTime() : bool
    {
        return $this->_highscore_wtime;
    }

    /**
     * Sets if the table with the own ranking should be shown.
     * @param bool $a_own_table True, if table with own ranking should be shown.
     */
    public function setHighscoreOwnTable(bool $a_own_table) : void
    {
        $this->_highscore_own_table = $a_own_table;
    }

    /**
     * Gets if the own rankings table should be shown.
     * @return bool True, if the own rankings table should be shown.
     */
    public function getHighscoreOwnTable() : bool
    {
        return $this->_highscore_own_table;
    }

    /**
     * Sets if the top-rankings table should be shown.
     */
    public function setHighscoreTopTable(bool $a_top_table) : void
    {
        $this->_highscore_top_table = $a_top_table;
    }

    /**
     * Gets, if the top-rankings table should be shown.
     * @return bool True, if top-rankings table should be shown.
     */
    public function getHighscoreTopTable() : bool
    {
        return $this->_highscore_top_table;
    }

    /**
     * Sets the number of entries which are to be shown in the top-rankings
     * table.
     * @param integer $a_top_num Number of entries in the top-rankings table.
     */
    public function setHighscoreTopNum(int $a_top_num) : void
    {
        $this->_highscore_top_num = $a_top_num;
    }

    /**
     * Gets the number of entries which are to be shown in the top-rankings table.
     * Default: 10 entries
     * @param int|null $a_retval Optional return value if nothing is set, defaults to 10.
     * @return integer Number of entries to be shown in the top-rankings table.
     */
    public function getHighscoreTopNum(?int $a_retval = 10) : int
    {
        $retval = $a_retval;
        if ($this->_highscore_top_num != 0) {
            $retval = $this->_highscore_top_num;
        }

        return $retval;
    }

    public function getHighscoreMode() : int
    {
        switch (true) {
            case $this->getHighscoreOwnTable() && $this->getHighscoreTopTable():
                return self::HIGHSCORE_SHOW_ALL_TABLES;

            case $this->getHighscoreTopTable():
                return self::HIGHSCORE_SHOW_TOP_TABLE;

            case $this->getHighscoreOwnTable():
            default:
                return self::HIGHSCORE_SHOW_OWN_TABLE;
        }
    }


    public function setHighscoreMode(int $mode) : void
    {
        switch ($mode) {
            case self::HIGHSCORE_SHOW_ALL_TABLES:
                $this->setHighscoreTopTable(true);
                $this->setHighscoreOwnTable(true);
                break;

            case self::HIGHSCORE_SHOW_TOP_TABLE:
                $this->setHighscoreTopTable(true);
                $this->setHighscoreOwnTable(false);
                break;

            case self::HIGHSCORE_SHOW_OWN_TABLE:
            default:
                $this->setHighscoreTopTable(false);
                $this->setHighscoreOwnTable(true);
                break;
        }
    }

    /* End GET/SET for highscore feature */

    /**
     * @return array<string, float>|array<string, int>|array<string, string>
     */
    public function getDataSetMapping() : array
    {
        if (null === ($lrsTypeId = $this->getLrsTypeId())) {
            $this->doRead();
        }
        return [
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
            'highscore_top_num' => $this->getHighscoreTopNum(),
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
    }

    /**
     * @throws \ILIAS\Filesystem\Exception\DirectoryNotFoundException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws \ILIAS\Filesystem\Exception\IOException
     */
    protected function doCloneObject(ilObject2 $new_obj, int $a_target_id, ?int $a_copy_id = null) : void
    {
        assert($new_obj instanceof ilObjCmiXapi);
        
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
            $newDir = implode(DIRECTORY_SEPARATOR, [ilFileUtils::getWebspaceDir(), $dirUtil->getWebDataDirRelativeObjectDirectory()]);
            $dirUtil = new ilCmiXapiContentUploadImporter($this);
            $thisDir = implode(DIRECTORY_SEPARATOR, [ilFileUtils::getWebspaceDir(), $dirUtil->getWebDataDirRelativeObjectDirectory()]);
            ilFileUtils::rCopy($thisDir, $newDir);
        }
    }

    protected function doDelete() : void
    {
        // delete file data entry
        $query = "DELETE FROM " . self::DB_TABLE_NAME . " WHERE obj_id = " . $this->database->quote($this->getId(), 'integer');
        $this->database->manipulate($query);
        ilHistory::_removeEntriesForObject($this->getId());

        // delete entire directory and its content
        $dirUtil = new ilCmiXapiContentUploadImporter($this);
        $thisDir = implode(DIRECTORY_SEPARATOR, [ilFileUtils::getWebspaceDir(), $dirUtil->getWebDataDirRelativeObjectDirectory()]);
        if (is_dir($thisDir)) {
            ilFileUtils::delDir($thisDir);
        }

        // delete meta data
        $this->deleteMetaData();

        //delete results
        $query = "DELETE FROM " . self::DB_RESULTS_TABLE_NAME .
                "WHERE obj_id = " . $this->database->quote($this->getId(), 'integer') . " ";
        $this->database->manipulate($query);

        // TODO check xapidel
    }

//    /**
//     * @return string[]
//     */
//    public function getRegistrations() : array
//    {
//        global $DIC;
//        $res = $DIC->database()->queryF(
//            "SELECT DISTINCT registration FROM " . self::DB_USERS_TABLE_NAME . " WHERE obj_id = %s",
//            array('text'),
//            array($this->getId())
//        );
//        $ret = [];
//        while ($row = $DIC->database()->fetchAssoc($res)) {
//            $ret[] = (string) $row['registration'];
//        }
//        return $ret;
//    }

    /**
     * @throws Exception
     */
    public static function guidv4(?string $data = null) : string
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data ??= random_bytes(16);
        assert(strlen($data) == 16);
    
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function getCurrentCmixUser() : \ilCmiXapiUser
    {
        global $DIC;
        if (null === $this->currentCmixUser) {
            $this->currentCmixUser = new ilCmiXapiUser($this->getId(), $DIC->user()->getId(), $this->getPrivacyIdent());
        }
        return $this->currentCmixUser;
    }

    /**
     * @throws ilCmiXapiException
     */
    public function getSessionId(?ilCmiXapiUser $cmixUser = null) : string
    {
        if (null === $cmixUser) {
            $cmixUser = $this->getCurrentCmixUser();
        }
        return ilCmiXapiAuthToken::getCmi5SessionByUsrIdAndObjIdAndRefId($cmixUser->getUsrId(), $this->getId(), $this->getRefId());
    }

    /**
     * LMS.LaunchData
     * @return array<string, mixed>
     */
    public function getLaunchData(?ilCmiXapiUser $cmixUser = null, string $lang = 'en') : array
    {
        if (null === $cmixUser) {
            $cmixUser = $this->getCurrentCmixUser();
        }
        // ToDo
        $moveOn = $this->getLMSMoveOn();
        if (!$moveOn || $moveOn == '') {
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
            $href = ilLink::_getStaticLink(
                $this->getRefId(),
                $this->getType()
            );
            $ctxTemplate['returnURL'] = $href;
        } else {
            $ctxTemplate['returnURL'] = ILIAS_HTTP_PATH . "/Modules/CmiXapi/xapiexit.php?lang={$lang}";
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

    /**
     * @return array<string, mixed>
     */
    public function getLaunchedContextTemplate(?ilCmiXapiUser $cmixUser = null) : array
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
        $extensions = $this->getStatementExtensions($cmixUser);
        $extensions['https://w3id.org/xapi/cmi5/context/extensions/launchmode'] = $launchMode;
        if (!empty($this->getLMSMoveOn())) {
            $extensions['https://w3id.org/xapi/cmi5/context/extensions/moveon'] = $this->getLMSMoveOn();
        }
        if (!empty($this->getLaunchParameters())) {
            $extensions['https://w3id.org/xapi/cmi5/context/extensions/launchparameters'] = $this->getLaunchParameters();
        }
        if (!empty($this->getMasteryScore())) {
            $extensions['https://w3id.org/xapi/cmi5/context/extensions/masteryscore'] = $this->getMasteryScore();
        }
        return array(
            "contextActivities" => $this->getStatementContextActivities(),
            "extensions" => $extensions
        );
    }

    /**
     * blueprint statement
     * @return array<string, mixed>
     * @throws Exception
     */
    public function getStatement(string $verb, ?ilCmiXapiUser $cmixUser = null) : array
    {
        if (null === $cmixUser) {
            $cmixUser = $this->getCurrentCmixUser();
        }
        $id = self::guidv4();
        $actor = $this->getStatementActor($cmixUser);
        $verbUri = ilCmiXapiVerbList::getInstance()->getVerbUri($verb);
        $extensions = $this->getStatementExtensions($cmixUser);
        $registration = $cmixUser->getRegistration();
        $contextActivities = $this->getStatementContextActivities();
        $object = $this->getStatementObject();
        return array(
            'id' => $id,
            'actor' => $actor,
            'verb' =>
            array(
                'id' => $verbUri
            ),
            'context' =>
            array(
                'extensions' => $extensions,
                'registration' => $registration,
                'contextActivities' => $contextActivities
            ),
            'object' => $object
        );
    }

    /**
     * statement actor
     * @return array<string, mixed[]>
     */
    public function getStatementActor(?ilCmiXapiUser $cmixUser = null) : array
    {
        global $DIC;
        if (null === $cmixUser) {
            $cmixUser = $this->getCurrentCmixUser();
        }
        $user = new ilObjUser($cmixUser->getUsrId()); // ToDo: Caching Names
        $name = ilCmiXapiUser::getName($this->getPrivacyName(), $user);
        if ($name == '') {
            $this->log()->error('error: no name in cmixuser');
            $name = 'UNDEFINED';
        }
        $homePage = ($this->anonymousHomePage == true) ? self::ANONYMOUS_HOMEPAGE : self::iliasUrl();
        if ($this->getContentType() == self::CONT_TYPE_CMI5) {
            $actor = [
                'objectType' => 'Agent',
                'account' => [
                    'homePage' => $homePage,
                    'name' => $cmixUser->getUsrIdent()
                ]
            ];
            if ($name !== '') {
                $actor['name'] = $name;
            }
        } else {
            $actor = [
                'objectType' => 'Agent',
                'mbox' => 'mailto:' . $cmixUser->getUsrIdent()
            ];
            if ($name !== '') {
                $actor['name'] = $name;
            }
        }
        return $actor;
    }

    /**
     * Minimal extensions
     * @return array<string, mixed>
     * @throws ilCmiXapiException
     */
    public function getStatementExtensions(?ilCmiXapiUser $cmixUser = null) : array
    {
        if (null === $cmixUser) {
            $cmixUser = $this->getCurrentCmixUser();
        }
        return array(
            'https://w3id.org/xapi/cmi5/context/extensions/sessionid' => $this->getSessionId($cmixUser),
            'https://ilias.de/cmi5/activityid' => $this->getActivityId()
        );
    }

    /**
     * Minimal statementActivities
     * @return array<string, array<int, array<string, array<string, array<string, string>>|string>>>
     */
    public function getStatementContextActivities() : array
    {
        $publisherId = $this->getPublisherId();
        $activityId = $this->getActivityId();
        if (empty($publisherId)) {
            $publisherId = $activityId;
        }
        return array(
            "grouping" => [
                [
                "objectType" => "Activity",
                "id" => "{$publisherId}",
                'definition' =>
                array(
                    'name' =>
                    array(
                        'de-DE' => $this->getTitle(),
                        'en-US' => $this->getTitle()
                    ),
                    'description' =>
                    array(
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
    }

    /**
     * @return array<string, mixed[]>
     */
    public function getStatementObject() : array
    {
        return array(
                'id' => $this->getActivityId(),
                'definition' =>
                array(
                    'name' =>
                    array(
                        'de-DE' => $this->getTitle(),
                        'en-US' => $this->getTitle()
                    ),
                    'description' =>
                    array(
                        'de-DE' => $this->getDescription(),
                        'en-US' => $this->getDescription()
                    )
                )
            );
    }

    /**
     * @return array<string, mixed>
     * @throws Exception
     */
    public function getLaunchedStatement(?ilCmiXapiUser $cmixUser = null) : array
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

    /**
     * @return array<string, mixed>
     * @throws Exception
     */
    public function getAbandonedStatement(?string $sessionId, ?string $duration, ?ilCmiXapiUser $cmixUser = null) : array
    {
        if (null === $cmixUser) {
            $cmixUser = $this->getCurrentCmixUser();
        }
        $statement = $this->getStatement('abandoned', $cmixUser);
        // overwrite session with abandoned oldSession
        $statement['context']['extensions']['https://w3id.org/xapi/cmi5/context/extensions/sessionid'] = $sessionId;
        $statement['result'] = array(
            'duration' => $duration
        );
        return $statement;
    }

    /**
     * @return array<string, mixed>
     * @throws Exception
     */
    public function getSatisfiedStatement(?ilCmiXapiUser $cmixUser = null) : array
    {
        if (null === $cmixUser) {
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
     * @return mixed|null
     */
    public function getLastStatement(string $sess)
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
        try {
            $responses = GuzzleHttp\Promise\settle($promises)->wait();
            $body = '';
            ilCmiXapiAbstractRequest::checkResponse($responses['defaultLastStatement'], $body, [200]);
            return json_decode($body, (bool) JSON_OBJECT_AS_ARRAY);
        } catch (Exception $e) {
            $this->log()->error('error:' . $e->getMessage());
            return null;
        }
    }

    /**
     * @return array<int, mixed[]>
     */
    public function getLastStatementPipline(string $sess) : array
    {
        global $DIC;
        $pipeline = array();
        
        // filter activityId
        $match = array();
        $match['statement.object.objectType'] = 'Activity';
        $match['statement.actor.objectType'] = 'Agent';
        
        $activityId = array();

        if ($this->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5 && !$this->isMixedContentType()) {
            // https://github.com/AICC/CMI-5_Spec_Current/blob/quartz/cmi5_spec.md#963-extensions
            $activityId['statement.context.extensions.https://ilias&46;de/cmi5/activityid'] = $this->getActivityId();
        } else {
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

    public static function iliasUrl() : string
    {
        global $DIC;
        $regex = '/^(https?:\/\/[^\/]+).*/';
        preg_match($regex, (string) $DIC->http()->request()->getUri(), $request_parts);
        return $request_parts[1];
    }

    public static function log() : ilLogger
    {
        if (self::PLUGIN) {
            global $log;
            return $log;
        } else {
            return \ilLoggerFactory::getLogger('cmix');
        }
    }
    
    
    public function isActivationLimited() : ?bool
    {
        return $this->activationLimited;
    }

   
    public function setActivationLimited(bool $activationLimited) : void
    {
        $this->activationLimited = $activationLimited;
    }
    
    public function getActivationStartingTime() : ?int
    {
        return $this->activationStartingTime;
    }
    
    public function setActivationStartingTime(int $activationStartingTime) : void
    {
        $this->activationStartingTime = $activationStartingTime;
    }
    
    public function getActivationEndingTime() : ?int
    {
        return $this->activationEndingTime;
    }
    
    public function setActivationEndingTime(int $activationEndingTime) : void
    {
        $this->activationEndingTime = $activationEndingTime;
    }
    
    public function getActivationVisibility() : ?bool
    {
        return $this->activationVisibility;
    }
    
    public function setActivationVisibility(bool $activationVisibility) : void
    {
        $this->activationVisibility = $activationVisibility;
    }
}
