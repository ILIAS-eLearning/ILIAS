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
    const CONT_TYPE_LEARNING = 'learning';
    
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
    protected $instructions;
    
    /**
     * @var string
     */
    protected $launchUrl;
    
    /**
     * @var bool
     */
    protected $authFetchUrlEnabled;
    
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
    const LAUNCH_MODE_NORMAL = 'normal';
    const LAUNCH_MODE_BROWSE = 'browse';
    const LAUNCH_MODE_REVIEW = 'review';
    
    /**
     * @var float
     */
    protected $masteryScore;
    
    /**
     * @var bool
     */
    protected $keepLpStatusEnabled;
    
    /**
     * @var string
     */
    protected $userIdent;
    const USER_IDENT_REAL_EMAIL = 'real_email';
    const USER_IDENT_IL_UUID_USER_ID = 'il_uuid_user_id';
    const USER_IDENT_IL_UUID_LOGIN = 'il_uuid_login';
    const USER_IDENT_IL_UUID_EXT_ACCOUNT = 'il_uuid_ext_account';
    const USER_IDENT_IL_UUID_RANDOM = 'il_uuid_random';
    
    /**
     * @var string
     */
    protected $userName;
    const USER_NAME_NONE = 'none';
    const USER_NAME_FIRSTNAME = 'firstname';
    const USER_NAME_LASTNAME = 'lastname';
    const USER_NAME_FULLNAME = 'fullname';

    
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
        
        $this->instructions = '';

        $this->launchUrl = '';
        $this->authFetchUrlEnabled = 0;
        
        $this->launchMethod = self::LAUNCH_METHOD_NEW_WIN;
        $this->launchMode = self::LAUNCH_MODE_NORMAL;
        
        $this->masteryScore = 0;
        $this->keepLpStatusEnabled = 1;
        
        $this->userIdent = self::USER_IDENT_IL_UUID_USER_ID;
        $this->userName = self::USER_NAME_NONE;
        $this->userPrivacyComment = '';

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
        $this->contentType = $contentType;
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
        return $this->launchMode;
    }
    
    /**
     * @param string $launchMode
     */
    public function setLaunchMode($launchMode)
    {
        $this->launchMode = $launchMode;
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
    public function getUserIdent()
    {
        return $this->userIdent;
    }
    
    /**
     * @param string $userIdent
     */
    public function setUserIdent($userIdent)
    {
        $this->userIdent = $userIdent;
    }
    
    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }
    
    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
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
            $this->setInstructions($row['instructions']);
            
            $this->setLaunchUrl($row['launch_url']);
            $this->setAuthFetchUrlEnabled((bool) $row['auth_fetch_url']);
            
            $this->setLaunchMethod($row['launch_method']);
            
            $this->setLaunchMode($row['launch_mode']);
            $this->setMasteryScore((float) $row['mastery_score']);
            $this->setKeepLpStatusEnabled((bool) $row['keep_lp']);
            
            $this->setUserIdent($row['user_ident']);
            $this->setUserName($row['user_name']);
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
            'activity_id' => ['text', $this->getActivityId()], // TODO: needs unique constraint, right?
            'instructions' => ['text', $this->getInstructions()],
            'launch_url' => ['text', $this->getLaunchUrl()],
            'auth_fetch_url' => ['integer', (int) $this->isAuthFetchUrlEnabled()],
            'launch_method' => ['text', $this->getLaunchMethod()],
            'launch_mode' => ['text', $this->getLaunchMode()],
            'mastery_score' => ['float', $this->getMasteryScore()],
            'keep_lp' => ['integer', (int) $this->isKeepLpStatusEnabled()],
            'user_ident' => ['text', $this->getUserIdent()],
            'user_name' => ['text', $this->getUserName()],
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
            'highscore_top_num' => ['integer', (int) $this->getHighscoreTopNum()]
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
			SET user_ident = %s, user_name = %s
			WHERE lrs_type_id = %s
		";
        
        $DIC->database()->manipulateF(
            $query,
            ['text', 'text', 'integer'],
            [$lrsType->getUserIdent(), $lrsType->getUserName(), $lrsType->getTypeId()]
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
            'instructions' => $this->getInstructions(),
            'launch_url' => $this->getLaunchUrl(),
            'auth_fetch_url' => (int) $this->isAuthFetchUrlEnabled(),
            'launch_method' => $this->getLaunchMethod(),
            'launch_mode' => $this->getLaunchMode(),
            'mastery_score' => $this->getMasteryScore(),
            'keep_lp' => (int) $this->isKeepLpStatusEnabled(),
            'usr_ident' => $this->getUserIdent(),
            'usr_name' => $this->getUserName(),
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
            'highscore_top_num' => (int) $this->getHighscoreTopNum()
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
		$new_obj->setInstructions($this->getInstructions());
		$new_obj->setLaunchUrl($this->getLaunchUrl());
		$new_obj->setAuthFetchUrlEnabled($this->isAuthFetchUrlEnabled());
		$new_obj->setLaunchMethod($this->getLaunchMethod());
		$new_obj->setLaunchMode($this->getLaunchMode());
		$new_obj->setMasteryScore($this->getMasteryScore());
		$new_obj->setKeepLpStatusEnabled($this->isKeepLpStatusEnabled());
		$new_obj->setUserIdent($this->getUserIdent());
		$new_obj->setUserName($this->getUserName());
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


}
