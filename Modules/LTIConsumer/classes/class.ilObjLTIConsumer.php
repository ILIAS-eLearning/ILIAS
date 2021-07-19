<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLTIConsumer
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilObjLTIConsumer extends ilObject2
{
    const DB_TABLE_NAME = 'lti_consumer_settings';
    
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
    protected $providerId = 0;
    
    /**
     * @var ilLTIConsumeProvider
     */
    protected $provider = null;
    
    const LAUNCH_METHOD_OWN_WIN = 'ownWin';
    const LAUNCH_METHOD_NEW_WIN = 'newWin';
    const LAUNCH_METHOD_EMBEDDED = 'embedded';
    
    protected $use_xapi = false;
    protected $custom_activity_id = '';
    protected $statementsReportEnabled = false;

    protected $mastery_score = 0.5;

    /**
     * @var string
     */
    protected $launchMethod = self::LAUNCH_METHOD_NEW_WIN;

    /**
     * @var string
     */
    protected $customLaunchKey = '';
    /**
     * @var string
     */
    protected $customLaunchSecret = '';
    
    /**
     * ilObjLTIConsumer constructor.
     * @param int $a_id
     * @param bool $a_reference
     */
    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        parent::__construct($a_id, $a_reference);
    }
    
    protected function initType()
    {
        $this->type = "lti";
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
     * @return float
     */
    public function getMasteryScore() : float
    {
        return $this->mastery_score;
    }

    /**
     * @param float $mastery_score
     */
    public function setMasteryScore(float $mastery_score)
    {
        $this->mastery_score = $mastery_score;
    }

    /**
     * @return float
     */
    public function getMasteryScorePercent() : float
    {
        return $this->mastery_score * 100;
    }

    /**
     * @param float $mastery_score_percent
     */
    public function setMasteryScorePercent(float $mastery_score_percent)
    {
        $this->mastery_score = $mastery_score_percent / 100;
    }

    /**
     * @return int
     */
    public function getProviderId() : int
    {
        return $this->providerId;
    }
    
    /**
     * @param int $providerId
     */
    public function setProviderId(int $providerId)
    {
        $this->providerId = $providerId;
    }
    
    public function initProvider()
    {
        $this->provider = new ilLTIConsumeProvider($this->getProviderId());
    }
    
    /**
     * @return ilLTIConsumeProvider
     */
    public function getProvider() : ilLTIConsumeProvider
    {
        return $this->provider;
    }
    
    /**
     * @param ilLTIConsumeProvider $provider
     */
    public function setProvider(ilLTIConsumeProvider $provider)
    {
        $this->provider = $provider;
    }
    
    /**
     * @return bool
     */
    public function isLaunchMethodOwnWin() : bool
    {
        return $this->launchMethod == self::LAUNCH_METHOD_OWN_WIN;
    }
    
    /**
     * @return bool
     */
    public function isLaunchMethodEmbedded() : bool
    {
        return $this->launchMethod == self::LAUNCH_METHOD_EMBEDDED;
    }
    
    /**
     * @return string
     */
    public function getLaunchMethod() : string
    {
        return $this->launchMethod;
    }
    
    /**
     * @param string $launchMethod
     */
    public function setLaunchMethod(string $launchMethod)
    {
        $this->launchMethod = $launchMethod;
    }

    /**
     * @return string
     */
    public function getCustomLaunchKey() : string
    {
        return $this->customLaunchKey;
    }
    
    /**
     * @param string $customLaunchKey
     */
    public function setCustomLaunchKey(string $customLaunchKey)
    {
        $this->customLaunchKey = $customLaunchKey;
    }
    
    /**
     * @return string
     */
    public function getCustomLaunchSecret() : string
    {
        return $this->customLaunchSecret;
    }
    
    /**
     * @param string $customLaunchSecret
     */
    public function setCustomLaunchSecret(string $customLaunchSecret)
    {
        $this->customLaunchSecret = $customLaunchSecret;
    }
    
    /**
     * @return string
     */
    public function getLaunchKey()
    {
        if ($this->getProvider()->isProviderKeyCustomizable()) {
            return $this->getCustomLaunchKey();
        }
        
        return $this->getProvider()->getProviderKey();
    }
    
    /**
     * @return string
     */
    public function getLaunchSecret()
    {
        if ($this->getProvider()->isProviderKeyCustomizable()) {
            return $this->getCustomLaunchSecret();
        }
        
        return $this->getProvider()->getProviderSecret();
    }
    
    /**
     * @return bool
     */
    public function getUseXapi() : bool
    {
        return $this->use_xapi;
    }
    
    /**
     * @param bool $use_xapi
     */
    public function setUseXapi(bool $use_xapi)
    {
        $this->use_xapi = $use_xapi;
    }

    /**
     * @return string
     */
    public function getCustomActivityId() : string
    {
        return $this->custom_activity_id;
    }
    
    /**
     * @param string $custom_activity_id
     */
    public function setCustomActivityId(string $custom_activity_id)
    {
        $this->custom_activity_id = $custom_activity_id;
    }
    
    /**
     * @return string
     */
    public function getActivityId() : string
    {
        if (strlen($this->getProvider()->getXapiActivityId())) {
            return $this->getProvider()->getXapiActivityId();
        }
        
        return $this->custom_activity_id;
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
     * @return string[]
     */
    private function getCustomParams() : array
    {
        $paramsAsArray = [];

        $params = $this->getProvider()->getCustomParams();
        // allows   foo=bar;foo2=baz2; foo3=baz3
        $params = preg_split('/; ?/', $params);

        foreach ($params as $param) {
            $param = explode('=', $param);
            // empty field, duplicate/leading/trailing semicolon?
            if ($param[0] != '') {
                $value = isset($param[1]) ? $param[1] : '';
                $paramsAsArray[$param[0]] = $value;
            }
        }

        return $paramsAsArray;
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
            // if ($row['provider_id']) { //always set
            $this->setProviderId((int) $row['provider_id']);
            $this->setProvider(new ilLTIConsumeProvider((int) $row['provider_id']));
            // }
            
            $this->setLaunchMethod($row['launch_method']);

            $this->setCustomLaunchKey((string) $row['launch_key']);
            $this->setCustomLaunchSecret((string) $row['launch_secret']);

            $this->setUseXapi((bool) $row['use_xapi']);
            $this->setCustomActivityId((string) $row['activity_id']);
            $this->setStatementsReportEnabled((bool) $row['show_statements']);
            $this->setHighscoreEnabled((bool) $row['highscore_enabled']);
            $this->setHighscoreAchievedTS((bool) $row['highscore_achieved_ts']);
            $this->setHighscorePercentage((bool) $row['highscore_percentage']);
            $this->setHighscoreWTime((bool) $row['highscore_wtime']);
            $this->setHighscoreOwnTable((bool) $row['highscore_own_table']);
            $this->setHighscoreTopTable((bool) $row['highscore_top_table']);
            $this->setHighscoreTopNum((int) $row['highscore_top_num']);

            $this->setMasteryScore((float) $row['mastery_score']);
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
            'provider_id' => ['integer', $this->getProviderId()],
            'launch_method' => ['text', $this->getLaunchMethod()],
            'launch_key' => ['text', $this->getCustomLaunchKey()],
            'launch_secret' => ['text', $this->getCustomLaunchSecret()],
            'use_xapi' => ['integer',$this->getUseXapi()],
            'activity_id' => ['text',$this->getCustomActivityId()],
            'show_statements' => ['integer',$this->isStatementsReportEnabled()],
            'highscore_enabled' => ['integer', (int) $this->getHighscoreEnabled()],
            'highscore_achieved_ts' => ['integer', (int) $this->getHighscoreAchievedTS()],
            'highscore_percentage' => ['integer', (int) $this->getHighscorePercentage()],
            'highscore_wtime' => ['integer', (int) $this->getHighscoreWTime()],
            'highscore_own_table' => ['integer', (int) $this->getHighscoreOwnTable()],
            'highscore_top_table' => ['integer', (int) $this->getHighscoreTopTable()],
            'highscore_top_num' => ['integer', (int) $this->getHighscoreTopNum()],
            'mastery_score' => ['float', (float) $this->getMasteryScore()]
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
    
    protected function dbTableName()
    {
        return self::DB_TABLE_NAME;
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

    public function buildLaunchParameters(ilCmiXapiUser $cmixUser, $token, $contextType, $contextId, $contextTitle, $returnUrl = '')
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $roles = $DIC->access()->checkAccess('write', '', $this->getRefId()) ? "Instructor" : "Learner";
        if ($this->getProvider()->getAlwaysLearner() == true) {
            $roles = "Learner";
        }

        $resource_link_id = $this->getRefId();
        if ($this->getProvider()->getUseProviderId() == true) {
            $resource_link_id = 'p' . $this->getProvider()->getId();
        }
        
        $usrImage = '';
        if ($this->getProvider()->getIncludeUserPicture()) {
            $usrImage = ILIAS_HTTP_PATH . "/" . $DIC->user()->getPersonalPicturePath("small");
        }
        
        $documentTarget = "window";
        if ($this->getLaunchMethod() == self::LAUNCH_METHOD_EMBEDDED) {
            $documentTarget = "iframe";
        }
        
        $nameGiven = '-';
        $nameFamily = '-';
        $nameFull = '-';
        switch ($this->getProvider()->getPrivacyName()) {
            case ilLTIConsumeProvider::PRIVACY_NAME_FIRSTNAME:
                $nameGiven = $DIC->user()->getFirstname();
                $nameFull = $DIC->user()->getFirstname();
                break;
            case ilLTIConsumeProvider::PRIVACY_NAME_LASTNAME:
                $usrName = $DIC->user()->getUTitle() ? $DIC->user()->getUTitle() . ' ' : '';
                $usrName .= $DIC->user()->getLastname();
                $nameFamily = $usrName;
                $nameFull = $usrName;
                break;
            case ilLTIConsumeProvider::PRIVACY_NAME_FULLNAME:
                $nameGiven = $DIC->user()->getFirstname();
                $nameFamily = $DIC->user()->getLastname();
                $nameFull = $DIC->user()->getFullname();
                break;
        }

        $userIdLTI = ilCmiXapiUser::getIdentAsId($this->getProvider()->getPrivacyIdent(), $DIC->user());

        $emailPrimary = $cmixUser->getUsrIdent();

        ilLTIConsumerResult::getByKeys($this->getId(), $DIC->user()->getId(), true);
        
        $custom_params = $this->getCustomParams();

        $launch_vars = [
            "lti_message_type" => "basic-lti-launch-request",
            "lti_version" => "LTI-1p0",
            "resource_link_id" => $resource_link_id,
            "resource_link_title" => $this->getTitle(),
            "resource_link_description" => $this->getDescription(),
            "user_id" => $userIdLTI,
            "user_image" => $usrImage,
            "roles" => $roles,
            "lis_person_name_given" => $nameGiven,
            "lis_person_name_family" => $nameFamily,
            "lis_person_name_full" => $nameFull,
            "lis_person_contact_email_primary" => $emailPrimary,
            "context_id" => $contextId,
            "context_type" => $contextType,
            "context_title" => $contextTitle,
            "context_label" => $contextType . " " . $contextId,
            "launch_presentation_locale" => $this->lng->getLangKey(),
            "launch_presentation_document_target" => $documentTarget,
            "launch_presentation_width" => "",//recommended
            "launch_presentation_height" => "",//recommended
            "launch_presentation_return_url" => $returnUrl,
            "tool_consumer_instance_guid" => $value = CLIENT_ID . "." . implode(".", array_reverse(explode("/", parse_url(ILIAS_HTTP_PATH)["path"]))) . parse_url(ILIAS_HTTP_PATH)["host"],
            "tool_consumer_instance_name" => $DIC->settings()->get("short_inst_name") ? $DIC->settings()->get("short_inst_name") : CLIENT_ID,
            "tool_consumer_instance_description" => ilObjSystemFolder::_getHeaderTitle(),
            "tool_consumer_instance_url" => ilLink::_getLink(ROOT_FOLDER_ID, "root"),//ToDo? "https://vb52p70.example.com/release_5-3/goto.php?target=root_1&client_id=inno",
            "tool_consumer_instance_contact_email" => $DIC->settings()->get("admin_email"),
            "launch_presentation_css_url" => "",
            "tool_consumer_info_product_family_code" => "ilias",
            "tool_consumer_info_version" => $DIC->settings()->get("ilias_version"),
            "lis_result_sourcedid" => $token,
            "lis_outcome_service_url" => ILIAS_HTTP_PATH . "/Modules/LTIConsumer/result.php?client_id=" . CLIENT_ID,
            "role_scope_mentor" => ""
        ];
        
        $OAuthParams = [
            "url" => $this->getProvider()->getProviderUrl(),
            "key" => $this->getLaunchKey(),
            "secret" => $this->getLaunchSecret(),
            "callback" => "about:blank",
            "http_method" => "POST",
            "sign_method" => "HMAC_SHA1",
            "token" => "",
            "data" => ($launch_vars + $custom_params)
        ];
        
        $launchParameters = ilLTIConsumerLaunch::signOAuth($OAuthParams);
        
        return $launchParameters;
    }
}
