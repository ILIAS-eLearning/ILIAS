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
    protected bool $activationLimited = false;
    protected ?int $activationStartingTime = null;
    protected ?int $activationEndingTime = null;
    protected ?bool $activationVisibility = null;

    protected int $providerId = 0;

    protected ?ilLTIConsumeProvider $provider = null;

    const LAUNCH_METHOD_OWN_WIN = 'ownWin';
    const LAUNCH_METHOD_NEW_WIN = 'newWin';
    const LAUNCH_METHOD_EMBEDDED = 'embedded';

    protected bool $use_xapi = false;
    protected string $custom_activity_id = '';
    protected bool $statementsReportEnabled = false;

    protected float $mastery_score = 0.5;

    protected string $launchMethod = self::LAUNCH_METHOD_NEW_WIN;

    protected string $customLaunchKey = '';

    protected string $customLaunchSecret = '';

    protected ?int $ref_id = 0;

    //Highscore
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
     * ilObjLTIConsumer constructor.
     */
    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        parent::__construct($a_id, $a_reference);
    }

    protected function initType() : void
    {
        $this->type = "lti";
    }

    public function isActivationLimited() : bool
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

    public function getMasteryScore() : float
    {
        return $this->mastery_score;
    }

    public function setMasteryScore(float $mastery_score) : void
    {
        $this->mastery_score = $mastery_score;
    }

    public function getMasteryScorePercent() : float
    {
        return $this->mastery_score * 100;
    }

    public function setMasteryScorePercent(float $mastery_score_percent) : void
    {
        $this->mastery_score = $mastery_score_percent / 100;
    }

    public function getProviderId() : int
    {
        return $this->providerId;
    }

    public function setProviderId(int $providerId) : void
    {
        $this->providerId = $providerId;
    }

    public function initProvider() : void
    {
        $this->provider = new ilLTIConsumeProvider($this->getProviderId());
    }

    public function getProvider() : ?\ilLTIConsumeProvider
    {
        return $this->provider;
    }

    public function setProvider(ilLTIConsumeProvider $provider) : void
    {
        $this->provider = $provider;
    }

    public function isLaunchMethodOwnWin() : bool
    {
        return $this->launchMethod == self::LAUNCH_METHOD_OWN_WIN;
    }

    public function isLaunchMethodEmbedded() : bool
    {
        return $this->launchMethod == self::LAUNCH_METHOD_EMBEDDED;
    }

    public function getLaunchMethod() : string
    {
        return $this->launchMethod;
    }

    public function setLaunchMethod(string $launchMethod) : void
    {
        $this->launchMethod = $launchMethod;
    }

    public function getCustomLaunchKey() : string
    {
        return $this->customLaunchKey;
    }

    public function setCustomLaunchKey(string $customLaunchKey) : void
    {
        $this->customLaunchKey = $customLaunchKey;
    }

    public function getCustomLaunchSecret() : string
    {
        return $this->customLaunchSecret;
    }

    public function setCustomLaunchSecret(string $customLaunchSecret) : void
    {
        $this->customLaunchSecret = $customLaunchSecret;
    }

    public function getLaunchKey() : string
    {
        if ($this->getProvider()->isProviderKeyCustomizable()) {
            return $this->getCustomLaunchKey();
        }

        return $this->getProvider()->getProviderKey();
    }

    public function getLaunchSecret() : string
    {
        if ($this->getProvider()->isProviderKeyCustomizable()) {
            return $this->getCustomLaunchSecret();
        }

        return $this->getProvider()->getProviderSecret();
    }

    public function getUseXapi() : bool
    {
        return $this->use_xapi;
    }

    public function setUseXapi(bool $use_xapi) : void
    {
        $this->use_xapi = $use_xapi;
    }

    public function getCustomActivityId() : string
    {
        return $this->custom_activity_id;
    }

    public function setCustomActivityId(string $custom_activity_id) : void
    {
        $this->custom_activity_id = $custom_activity_id;
    }

    public function getActivityId() : string
    {
        if (strlen($this->getProvider()->getXapiActivityId())) {
            return $this->getProvider()->getXapiActivityId();
        }

        return $this->custom_activity_id;
    }

    public function isStatementsReportEnabled() : bool
    {
        return $this->statementsReportEnabled;
    }

    public function setStatementsReportEnabled(bool $statementsReportEnabled) : void
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


    protected function doRead() : void
    {
        $this->load();
    }

    public function load() : void
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

    protected function doUpdate() : void
    {
        $this->save();
    }

    public function save() : void
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
            'highscore_top_num' => ['integer', $this->getHighscoreTopNum()],
            'mastery_score' => ['float', $this->getMasteryScore()]
        ]);

        $this->saveRepositoryActivationSettings();
    }

    protected function loadRepositoryActivationSettings() : void
    {
        if ($this->ref_id > 0) {
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
        if ($this->ref_id > 0) {
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

    protected function dbTableName() : string
    {
        return self::DB_TABLE_NAME;
    }

    /////////////////////////////////////////
    /// HIGHSCORE


    /**
     * Sets if the highscore feature should be enabled.
     */
    public function setHighscoreEnabled(bool $a_enabled) : void
    {
        $this->_highscore_enabled = $a_enabled;
    }

    /**
     * Gets the setting which determines if the highscore feature is enabled.
     *
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
     *
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
     *
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
     *
     * @return bool True, if the workingtime column should be shown.
     */
    public function getHighscoreWTime() : bool
    {
        return $this->_highscore_wtime;
    }

    /**
     * Sets if the table with the own ranking should be shown.
     *
     * @param bool $a_own_table True, if table with own ranking should be shown.
     */
    public function setHighscoreOwnTable(bool $a_own_table) : void
    {
        $this->_highscore_own_table = $a_own_table;
    }

    /**
     * Gets if the own rankings table should be shown.
     *
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
     *
     * @return bool True, if top-rankings table should be shown.
     */
    public function getHighscoreTopTable() : bool
    {
        return $this->_highscore_top_table;
    }

    /**
     * Sets the number of entries which are to be shown in the top-rankings
     * table.
     *
     * @param integer $a_top_num Number of entries in the top-rankings table.
     */
    public function setHighscoreTopNum(int $a_top_num) : void
    {
        $this->_highscore_top_num = $a_top_num;
    }

    /**
     * Gets the number of entries which are to be shown in the top-rankings table.
     * Default: 10 entries
     *
     * @param integer $a_retval Optional return value if nothing is set, defaults to 10.
     *
     * @return integer Number of entries to be shown in the top-rankings table.
     */
    public function getHighscoreTopNum(int $a_retval = 10) : int
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

    /**
     * @param $mode int
     */
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
    /* End GET/SET for highscore feature*/
    /**
     * @throws ilWACException
     */
    public function buildLaunchParameters(ilCmiXapiUser $cmixUser, string $token, string $contextType, string $contextId, string $contextTitle, ?string $returnUrl = '') : array
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
        $toolConsumerInstanceGuid = CLIENT_ID . ".";
        $parseIliasUrl = parse_url(ILIAS_HTTP_PATH);
        if (array_key_exists("path", $parseIliasUrl)) {
            $toolConsumerInstanceGuid .= implode(".", array_reverse(explode("/", $parseIliasUrl["path"])));
        }
        $toolConsumerInstanceGuid .= $parseIliasUrl["host"];
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
            "tool_consumer_instance_guid" => $toolConsumerInstanceGuid,
            "tool_consumer_instance_name" => $DIC->settings()->get("short_inst_name") ? $DIC->settings()->get("short_inst_name") : CLIENT_ID,
            "tool_consumer_instance_description" => ilObjSystemFolder::_getHeaderTitle(),
            "tool_consumer_instance_url" => ilLink::_getLink(ROOT_FOLDER_ID, "root"),//ToDo? "https://vb52p70.example.com/release_5-3/goto.php?target=root_1&client_id=inno",
            "tool_consumer_instance_contact_email" => $DIC->settings()->get("admin_email"),
            "launch_presentation_css_url" => "",
            "tool_consumer_info_product_family_code" => "ilias",
            "tool_consumer_info_version" => ILIAS_VERSION,
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
            "token" => null,
            "data" => ($launch_vars + $custom_params)
        ];

        return ilLTIConsumerLaunch::signOAuth($OAuthParams);
    }

    /**
     * @throws ilWACException
     */

    public function buildLaunchParametersLTI13(ilCmiXapiUser $cmixUser, string $endpoint, string $clientId, int $deploymentId, string $nonce, string $contextType, string $contextId, string $contextTitle, ?string $returnUrl = '') : ?array
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
        $toolConsumerInstanceGuid = CLIENT_ID . ".";
        $parseIliasUrl = parse_url(ILIAS_HTTP_PATH);
        if (array_key_exists("path", $parseIliasUrl)) {
            $toolConsumerInstanceGuid .= implode(".", array_reverse(explode("/", $parseIliasUrl["path"])));
        }
        $toolConsumerInstanceGuid .= $parseIliasUrl["host"];
        $launch_vars = [
            "lti_message_type" => "basic-lti-launch-request",
            "lti_version" => "1.3.0",
            "resource_link_id" => (string) $resource_link_id,
            "resource_link_title" => $this->getTitle(),
            "resource_link_description" => $this->getDescription(),
            "user_id" => (string) $userIdLTI,
            "user_image" => $usrImage,
            "roles" => $roles,
            "lis_person_name_given" => $nameGiven,
            "lis_person_name_family" => $nameFamily,
            "lis_person_name_full" => $nameFull,
            "lis_person_contact_email_primary" => $emailPrimary,
            "context_id" => (string) $contextId,
            "context_type" => $contextType,
            "context_title" => $contextTitle,
            "context_label" => $contextType . " " . $contextId,
            "launch_presentation_locale" => $this->lng->getLangKey(),
            "launch_presentation_document_target" => $documentTarget,
            "launch_presentation_width" => "",//recommended
            "launch_presentation_height" => "",//recommended
            "launch_presentation_return_url" => $returnUrl,
            "tool_consumer_instance_guid" => $toolConsumerInstanceGuid,
            "tool_consumer_instance_name" => $DIC->settings()->get("short_inst_name") ? $DIC->settings()->get("short_inst_name") : CLIENT_ID,
            "tool_consumer_instance_description" => ilObjSystemFolder::_getHeaderTitle(),
            "tool_consumer_instance_url" => ilLink::_getLink(ROOT_FOLDER_ID, "root"),//ToDo? "https://vb52p70.example.com/release_5-3/goto.php?target=root_1&client_id=inno",
            "tool_consumer_instance_contact_email" => $DIC->settings()->get("admin_email"),
            "launch_presentation_css_url" => "",
            "tool_consumer_info_product_family_code" => "ilias",
            "tool_consumer_info_version" => ILIAS_VERSION,
            "lis_result_sourcedid" => "",//$token,
            "lis_outcome_service_url" => ILIAS_HTTP_PATH . "/Modules/LTIConsumer/result.php?client_id=" . CLIENT_ID,
            "role_scope_mentor" => ""
        ];

        $ltilib = new lti13lib();

        if (!empty($ltilib->verifyPrivateKey())) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', 'ERROR_OPEN_SSL_CONF', true);
            return null;
        }

        return $ltilib->LTISignJWT($launch_vars, $endpoint, $clientId, $deploymentId, $nonce);
    }
}
