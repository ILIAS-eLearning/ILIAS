<?php

declare(strict_types=1);

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

use ILIAS\Filesystem\Stream\Streams;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;

/**
 * Class ilObjLTIConsumer
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <eqsoft4@gmail.com>
 *
 * @package     Modules/LTIConsumer
 */
class ilObjLTIConsumer extends ilObject2
{
    public const DB_TABLE_NAME = 'lti_consumer_settings';

    /**
     * repository object activation settings (handled by ilObject)
     */
    protected bool $activationLimited = false;
    protected ?int $activationStartingTime = null;
    protected ?int $activationEndingTime = null;
    protected ?bool $activationVisibility = null;

    protected int $providerId = 0;

    protected ?ilLTIConsumeProvider $provider = null;

    public const LAUNCH_METHOD_OWN_WIN = 'ownWin';
    public const LAUNCH_METHOD_NEW_WIN = 'newWin';
    public const LAUNCH_METHOD_EMBEDDED = 'embedded';

    protected bool $use_xapi = false;
    protected string $custom_activity_id = '';
    protected bool $statementsReportEnabled = false;

    protected float $mastery_score = 0.5;

    protected string $launchMethod = self::LAUNCH_METHOD_NEW_WIN;

    protected string $customLaunchKey = '';

    protected string $customLaunchSecret = '';

    protected string $customParams = '';

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

    public const HIGHSCORE_SHOW_ALL_TABLES = 1;
    public const HIGHSCORE_SHOW_TOP_TABLE = 2;
    public const HIGHSCORE_SHOW_OWN_TABLE = 3;

    public const LTI_JWT_CLAIM_PREFIX = 'https://purl.imsglobal.org/spec/lti';
    public const LTI_1_3_KID = 'lti_1_3_kid';
    public const LTI_1_3_PRIVATE_KEY = 'lti_1_3_privatekey';
    public const ERROR_OPEN_SSL_CONF = 'error openssl config invalid';
    public const OPENSSL_KEYTYPE_RSA = '';

    public const REG_TOKEN_OP_NEW_REG = 'reg';
    public const REG_TOKEN_OP_UPDATE_REG = 'reg-update';

    /**
     * ilObjLTIConsumer constructor.
     */
    public function __construct(int $a_id = 0, bool $a_reference = true)
    {
        parent::__construct($a_id, $a_reference);
    }

    protected function initType(): void
    {
        $this->type = "lti";
    }

    public function isActivationLimited(): bool
    {
        return $this->activationLimited;
    }

    public function setActivationLimited(bool $activationLimited): void
    {
        $this->activationLimited = $activationLimited;
    }

    public function getActivationStartingTime(): ?int
    {
        return $this->activationStartingTime;
    }

    public function setActivationStartingTime(int $activationStartingTime): void
    {
        $this->activationStartingTime = $activationStartingTime;
    }

    public function getActivationEndingTime(): ?int
    {
        return $this->activationEndingTime;
    }

    public function setActivationEndingTime(int $activationEndingTime): void
    {
        $this->activationEndingTime = $activationEndingTime;
    }

    public function getActivationVisibility(): ?bool
    {
        return $this->activationVisibility;
    }

    public function setActivationVisibility(bool $activationVisibility): void
    {
        $this->activationVisibility = $activationVisibility;
    }

    public function getMasteryScore(): float
    {
        return $this->mastery_score;
    }

    public function setMasteryScore(float $mastery_score): void
    {
        $this->mastery_score = $mastery_score;
    }

    public function getMasteryScorePercent(): float
    {
        return $this->mastery_score * 100;
    }

    public function setMasteryScorePercent(float $mastery_score_percent): void
    {
        $this->mastery_score = $mastery_score_percent / 100;
    }

    public function getProviderId(): int
    {
        return $this->providerId;
    }

    public function setProviderId(int $providerId): void
    {
        $this->providerId = $providerId;
    }

    public function initProvider(): void
    {
        $this->provider = new ilLTIConsumeProvider($this->getProviderId());
    }

    public function getProvider(): ?\ilLTIConsumeProvider
    {
        return $this->provider;
    }

    public function setProvider(ilLTIConsumeProvider $provider): void
    {
        $this->provider = $provider;
    }

    public function isLaunchMethodOwnWin(): bool
    {
        return $this->launchMethod == self::LAUNCH_METHOD_OWN_WIN;
    }

    public function isLaunchMethodEmbedded(): bool
    {
        return $this->launchMethod == self::LAUNCH_METHOD_EMBEDDED;
    }

    public function getLaunchMethod(): string
    {
        return $this->launchMethod;
    }

    public function setLaunchMethod(string $launchMethod): void
    {
        $this->launchMethod = $launchMethod;
    }

    public function getCustomLaunchKey(): string
    {
        return $this->customLaunchKey;
    }

    public function setCustomLaunchKey(string $customLaunchKey): void
    {
        $this->customLaunchKey = $customLaunchKey;
    }

    public function getCustomLaunchSecret(): string
    {
        return $this->customLaunchSecret;
    }

    public function setCustomLaunchSecret(string $customLaunchSecret): void
    {
        $this->customLaunchSecret = $customLaunchSecret;
    }

    public function getCustomParams(): string
    {
        return $this->customParams;
    }

    public function setCustomParams(string $customParams): void
    {
        $this->customParams = $customParams;
    }

    public function getLaunchKey(): string
    {
        if ($this->getProvider()->isProviderKeyCustomizable()) {
            return $this->getCustomLaunchKey();
        }

        return $this->getProvider()->getProviderKey();
    }

    public function getLaunchSecret(): string
    {
        if ($this->getProvider()->isProviderKeyCustomizable()) {
            return $this->getCustomLaunchSecret();
        }

        return $this->getProvider()->getProviderSecret();
    }

    public function getUseXapi(): bool
    {
        return $this->use_xapi;
    }

    public function setUseXapi(bool $use_xapi): void
    {
        $this->use_xapi = $use_xapi;
    }

    public function getCustomActivityId(): string
    {
        return $this->custom_activity_id;
    }

    public function setCustomActivityId(string $custom_activity_id): void
    {
        $this->custom_activity_id = $custom_activity_id;
    }

    public function getActivityId(): string
    {
        if (strlen($this->getProvider()->getXapiActivityId())) {
            return $this->getProvider()->getXapiActivityId();
        }

        return $this->custom_activity_id;
    }

    public function isStatementsReportEnabled(): bool
    {
        return $this->statementsReportEnabled;
    }

    public function setStatementsReportEnabled(bool $statementsReportEnabled): void
    {
        $this->statementsReportEnabled = $statementsReportEnabled;
    }

    /**
     * @return string[]
     */
    public function getCustomParamsArray(): array
    {
        $paramsAsArray = [];

        $params = $this->getCustomParams();
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

    /**
     * @return string[]
     */
    public static function getProviderCustomParamsArray(ilLTIConsumeProvider $provider): array
    {
        $paramsAsArray = [];

        $params = $provider->getCustomParams();
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

    protected function doRead(): void
    {
        $this->load();
    }

    public function load(): void
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
            $this->setCustomParams((string) $row['custom_params']);

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

    protected function doUpdate(): void
    {
        $this->save();
    }

    public function save(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->database()->replace($this->dbTableName(), [
            'obj_id' => ['integer', $this->getId()]
        ], [
            'provider_id' => ['integer', $this->getProviderId()],
            'launch_method' => ['text', $this->getLaunchMethod()],
            'launch_key' => ['text', $this->getCustomLaunchKey()],
            'launch_secret' => ['text', $this->getCustomLaunchSecret()],
            'custom_params' => ['text', $this->getCustomParams()],
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

    protected function loadRepositoryActivationSettings(): void
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

    protected function saveRepositoryActivationSettings(): void
    {
        if ($this->ref_id > 0) {
            ilObjectActivation::getItem($this->ref_id);

            $item = new ilObjectActivation();
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

    protected function dbTableName(): string
    {
        return self::DB_TABLE_NAME;
    }

    /////////////////////////////////////////
    /// HIGHSCORE


    /**
     * Sets if the highscore feature should be enabled.
     */
    public function setHighscoreEnabled(bool $a_enabled): void
    {
        $this->_highscore_enabled = $a_enabled;
    }

    /**
     * Gets the setting which determines if the highscore feature is enabled.
     *
     * @return bool True, if highscore is enabled.
     */
    public function getHighscoreEnabled(): bool
    {
        return $this->_highscore_enabled;
    }


    /**
     * Sets if the date and time of the scores achievement should be displayed.
     */
    public function setHighscoreAchievedTS(bool $a_achieved_ts): void
    {
        $this->_highscore_achieved_ts = $a_achieved_ts;
    }

    /**
     * Returns if date and time of the scores achievement should be displayed.
     *
     * @return bool True, if column should be shown.
     */
    public function getHighscoreAchievedTS(): bool
    {
        return $this->_highscore_achieved_ts;
    }

    /**
     * Sets if the percentages of the scores pass should be shown.
     */
    public function setHighscorePercentage(bool $a_percentage): void
    {
        $this->_highscore_percentage = $a_percentage;
    }

    /**
     * Gets if the percentage column should be shown.
     *
     * @return bool True, if percentage column should be shown.
     */
    public function getHighscorePercentage(): bool
    {
        return $this->_highscore_percentage;
    }

    /**
     * Sets if the workingtime of the scores should be shown.
     */
    public function setHighscoreWTime(bool $a_wtime): void
    {
        $this->_highscore_wtime = $a_wtime;
    }

    /**
     * Gets if the column with the workingtime should be shown.
     *
     * @return bool True, if the workingtime column should be shown.
     */
    public function getHighscoreWTime(): bool
    {
        return $this->_highscore_wtime;
    }

    /**
     * Sets if the table with the own ranking should be shown.
     *
     * @param bool $a_own_table True, if table with own ranking should be shown.
     */
    public function setHighscoreOwnTable(bool $a_own_table): void
    {
        $this->_highscore_own_table = $a_own_table;
    }

    /**
     * Gets if the own rankings table should be shown.
     *
     * @return bool True, if the own rankings table should be shown.
     */
    public function getHighscoreOwnTable(): bool
    {
        return $this->_highscore_own_table;
    }

    /**
     * Sets if the top-rankings table should be shown.
     */
    public function setHighscoreTopTable(bool $a_top_table): void
    {
        $this->_highscore_top_table = $a_top_table;
    }

    /**
     * Gets, if the top-rankings table should be shown.
     *
     * @return bool True, if top-rankings table should be shown.
     */
    public function getHighscoreTopTable(): bool
    {
        return $this->_highscore_top_table;
    }

    /**
     * Sets the number of entries which are to be shown in the top-rankings
     * table.
     *
     * @param integer $a_top_num Number of entries in the top-rankings table.
     */
    public function setHighscoreTopNum(int $a_top_num): void
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
    public function getHighscoreTopNum(int $a_retval = 10): int
    {
        $retval = $a_retval;
        if ($this->_highscore_top_num != 0) {
            $retval = $this->_highscore_top_num;
        }

        return $retval;
    }

    public function getHighscoreMode(): int
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
    public function setHighscoreMode(int $mode): void
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
    public function buildLaunchParameters(ilCmiXapiUser $cmixUser, string $token, string $contextType, string $contextId, string $contextTitle, ?string $returnUrl = ''): array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $roles = $DIC->access()->checkAccess('write', '', $this->getRefId()) ? "Instructor" : "Learner";
        //todo if object is in course or group, roles would have to be taken from there s. Mantis 35435 - if necessary Jour Fixe topic
        //$roles = "Administrator";

        if ($this->getProvider()->getAlwaysLearner() == true) {
            $roles = "Learner";
        }

        $resource_link_id = $this->getRefId();
        if ($this->getProvider()->getUseProviderId() == true) {
            $resource_link_id = 'p' . $this->getProvider()->getId();
        }

        $usrImage = '';
        if ($this->getProvider()->getIncludeUserPicture()) {
            $usrImage = self::getIliasHttpPath() . "/" . $DIC->user()->getPersonalPicturePath("small");
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
        if ($this->getProvider()->getPrivacyIdent() == ilObjCmiXapi::PRIVACY_IDENT_IL_UUID_RANDOM) {
            $userIdLTI = strstr($emailPrimary, '@' . ilCmiXapiUser::getIliasUuid(), true);
        }

        ilLTIConsumerResult::getByKeys($this->getId(), $DIC->user()->getId(), true);

        //ToDo: Check!
        $provider_custom_params = self::getProviderCustomParamsArray($this->getProvider());
        $custom_params = $this->getCustomParamsArray();
        $merged_params = array_merge($provider_custom_params, $custom_params);

        $toolConsumerInstanceGuid = CLIENT_ID . ".";
        $parseIliasUrl = parse_url(self::getIliasHttpPath());
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
            "lis_outcome_service_url" => self::getIliasHttpPath() . "/Modules/LTIConsumer/result.php?client_id=" . CLIENT_ID,
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
            "data" => ($launch_vars + $merged_params)
        ];

        return ilLTIConsumerLaunch::signOAuth($OAuthParams);
    }

    /**
     * @throws ilWACException
     */

    public function buildLaunchParametersLTI13(ilCmiXapiUser $cmixUser, string $endpoint, string $clientId, int $deploymentId, string $nonce, string $contextType, string $contextId, string $contextTitle, ?string $returnUrl = ''): ?array
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
            $usrImage = self::getIliasHttpPath() . "/" . $DIC->user()->getPersonalPicturePath("small");
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

        $toolConsumerInstanceGuid = CLIENT_ID . ".";
        $parseIliasUrl = parse_url(self::getIliasHttpPath());
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
            "lis_result_sourcedid" => "",//$token,
            "lis_outcome_service_url" => self::getIliasHttpPath() . "/Modules/LTIConsumer/result.php?client_id=" . CLIENT_ID,
            "role_scope_mentor" => ""
        ];

        $provider_custom_params = self::getProviderCustomParamsArray($this->getProvider());
        $custom_params = $this->getCustomParamsArray();
        $merged_params = array_merge($provider_custom_params, $custom_params);
        foreach ($merged_params as $key => $value) {
            $launch_vars['custom_' . $key] = $value;
        }

        if ($this->getProvider()->isGradeSynchronization()) {
            include_once("Modules/LTIConsumer/classes/class.ilLTIConsumerGradeService.php");
            $gradeservice = new ilLTIConsumerGradeService();
            $launch_vars['custom_lineitem_url'] = self::getIliasHttpPath() . "/Modules/LTIConsumer/ltiservices.php/gradeservice/" . $contextId . "/lineitems/" . $this->id . "/lineitem";

            // ! Moodle as tool provider requires a custom_lineitems_url even though this should be optional in launch request, especially if only posting score scope is permitted by platform
            // http://www.imsglobal.org/spec/lti-ags/v2p0#example-link-has-a-single-line-item-tool-can-only-post-score
            $launch_vars['custom_lineitems_url'] = self::getIliasHttpPath() . "/Modules/LTIConsumer/ltiservices.php/gradeservice/" . $contextId . "/linetitems/";

            $launch_vars['custom_ags_scopes'] = implode(",", $gradeservice->getPermittedScopes());
        }

        if (!empty(self::verifyPrivateKey())) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', 'ERROR_OPEN_SSL_CONF', true);
            return null;
        }
        return self::LTISignJWT($launch_vars, $endpoint, $clientId, $deploymentId, $nonce);
    }

    /**
     * @throws ilWACException
     */

    // ToDo:

    public static function buildContentSelectionParameters(ilLTIConsumeProvider $provider, int $refId, string $returnUrl, string $nonce): ?array
    {
        global $DIC;

        $clientId = $provider->getClientId();
        $deploymentId = $provider->getId();
        $ilLTIConsumerLaunch = new ilLTIConsumerLaunch($refId);
        $context = $ilLTIConsumerLaunch->getContext();
        $contextType = $ilLTIConsumerLaunch::getLTIContextType($context["type"]);
        $contextId = $context["id"];
        $contextTitle = $context["title"];

        $roles = "Instructor";
        $usrImage = '';
        if ($provider->getIncludeUserPicture()) {
            $usrImage = self::getIliasHttpPath() . "/" . $DIC->user()->getPersonalPicturePath("small");
        }
        $documentTarget = "window";
        if ($provider->getLaunchMethod() == self::LAUNCH_METHOD_EMBEDDED) {
            $documentTarget = "iframe";
        }
        $nameGiven = '-';
        $nameFamily = '-';
        $nameFull = '-';
        switch ($provider->getPrivacyName()) {
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

        $userIdLTI = ilCmiXapiUser::getIdentAsId($provider->getPrivacyIdent(), $DIC->user());
        $emailPrimary = ilCmiXapiUser::getIdent($provider->getPrivacyIdent(), $DIC->user());
        $toolConsumerInstanceGuid = CLIENT_ID . ".";
        $parseIliasUrl = parse_url(self::getIliasHttpPath());
        if (array_key_exists("path", $parseIliasUrl)) {
            $toolConsumerInstanceGuid .= implode(".", array_reverse(explode("/", $parseIliasUrl["path"])));
        }
        $toolConsumerInstanceGuid .= $parseIliasUrl["host"];

        $content_select_vars = [
            "lti_message_type" => "ContentItemSelectionRequest",
            "lti_version" => "1.3.0",
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
            "launch_presentation_locale" => $DIC->language()->getLangKey(),
            "launch_presentation_document_target" => $documentTarget,
            "launch_presentation_width" => "",//recommended
            "launch_presentation_height" => "",//recommended
            "tool_consumer_instance_guid" => $toolConsumerInstanceGuid,
            "tool_consumer_instance_name" => $DIC->settings()->get("short_inst_name") ? $DIC->settings()->get("short_inst_name") : CLIENT_ID,
            "tool_consumer_instance_description" => ilObjSystemFolder::_getHeaderTitle(),
            "tool_consumer_instance_url" => ilLink::_getLink(ROOT_FOLDER_ID, "root"),//ToDo? "https://vb52p70.example.com/release_5-3/goto.php?target=root_1&client_id=inno",
            "tool_consumer_instance_contact_email" => $DIC->settings()->get("admin_email"),
            "tool_consumer_info_product_family_code" => "ilias",
            "tool_consumer_info_version" => ILIAS_VERSION,
            "content_item_return_url" => $returnUrl,
            "accept_types" => "ltiResourceLink",
            "accept_presentation_document_targets" => "iframe,window,embed",
            "accept_multiple" => true,
            "auto_create" => true,
        ];
        $provider_custom_params = self::getProviderCustomParamsArray($provider);
        foreach ($provider_custom_params as $key => $value) {
            $content_select_vars['custom_' . $key] = $value;
        }

        if (!empty(self::verifyPrivateKey())) {
            $DIC->ui()->mainTemplate()->setOnScreenMessage('failure', 'ERROR_OPEN_SSL_CONF', true);
            return null;
        }
        return self::LTISignJWT($content_select_vars, '', $clientId, $deploymentId, $nonce);
    }

    public static function LTISignJWT(array $parms, string $endpoint, string $oAuthConsumerKey, $typeId = 0, string $nonce = ''): array
    {
        if (empty($typeId)) {
            $typeId = 0;
        }
        $messageTypeMapping = ILIAS\LTI\ToolProvider\Util::MESSAGE_TYPE_MAPPING;
        if (isset($parms['lti_message_type']) && array_key_exists($parms['lti_message_type'], $messageTypeMapping)) {
            $parms['lti_message_type'] = $messageTypeMapping[$parms['lti_message_type']];
        }
        if (isset($parms['roles'])) {
            $roles = explode(',', $parms['roles']);
            $newRoles = array();
            foreach ($roles as $role) {
                if (strpos($role, 'urn:lti:role:ims/lis/') === 0) {
                    $role = 'http://purl.imsglobal.org/vocab/lis/v2/membership#' . substr($role, 21);
                } elseif (strpos($role, 'urn:lti:instrole:ims/lis/') === 0) {
                    $role = 'http://purl.imsglobal.org/vocab/lis/v2/institution/person#' . substr($role, 25);
                } elseif (strpos($role, 'urn:lti:sysrole:ims/lis/') === 0) {
                    $role = 'http://purl.imsglobal.org/vocab/lis/v2/system/person#' . substr($role, 24);
                } elseif ((strpos($role, '://') === false) && (strpos($role, 'urn:') !== 0)) {
                    $role = "http://purl.imsglobal.org/vocab/lis/v2/membership#{$role}";
                }
                $newRoles[] = $role;
            }
            $parms['roles'] = implode(',', $newRoles);
        }
        $now = time();
        if (empty($nonce)) {
            $nonce = bin2hex(openssl_random_pseudo_bytes(10));
        }
        $claimMapping = ILIAS\LTI\ToolProvider\Util::JWT_CLAIM_MAPPING;
        $payLoad = array(
            'nonce' => $nonce,
            'iat' => $now,
            'exp' => $now + 60,
        );
        $payLoad['iss'] = self::getIliasHttpPath();
        $payLoad['aud'] = $oAuthConsumerKey;
        $payLoad[self::LTI_JWT_CLAIM_PREFIX . '/claim/deployment_id'] = strval($typeId);
        if (!empty($endpoint)) {  // only for launch request
            $payLoad[self::LTI_JWT_CLAIM_PREFIX . '/claim/target_link_uri'] = $endpoint;
        }

        foreach ($parms as $key => $value) {
            $claim = self::LTI_JWT_CLAIM_PREFIX;
            if (array_key_exists($key, $claimMapping)) {
                $mapping = $claimMapping[$key];

                if (isset($mapping['isArray']) && $mapping['isArray']) {
                    $value = explode(',', $value);
                    sort($value);
                } elseif (isset($mapping['isBoolean'])) {
                    $value = $mapping['isBoolean'];
                }
                if (!empty($mapping['suffix'])) {
                    $claim .= "-{$mapping['suffix']}";
                }
                $claim .= '/claim/';
                if (is_null($mapping['group'])) {
                    $payLoad[$mapping['claim']] = $value;
                } elseif (empty($mapping['group'])) {
                    $payLoad["{$claim}{$mapping['claim']}"] = $value;
                } else {
                    $claim .= $mapping['group'];
                    $payLoad[$claim][$mapping['claim']] = $value;
                }
            } elseif (strpos($key, 'custom_') === 0) {
                $payLoad["{$claim}/claim/custom"][substr($key, 7)] = $value;
            } elseif (strpos($key, 'ext_') === 0) {
                $payLoad["{$claim}/claim/ext"][substr($key, 4)] = $value;
            }
        }
        //self::getLogger()->debug(json_encode($payLoad,JSON_PRETTY_PRINT));
        if (!empty(self::verifyPrivateKey())) {
            throw new DomainException(self::ERROR_OPEN_SSL_CONF);
        }
        $privateKey = self::getPrivateKey();
        $jwt = Firebase\JWT\JWT::encode($payLoad, $privateKey['key'], 'RS256', $privateKey['kid']);
        $newParms = array();
        $newParms['id_token'] = $jwt;
        return $newParms;
    }

    public static function getPrivateKey(): array
    {
        global $ilSetting;
        $err = self::verifyPrivateKey();
        if (!empty($err)) {
            return [];
        }
        $privatekey = $ilSetting->get(self::LTI_1_3_PRIVATE_KEY);
        $kid = $ilSetting->get(self::LTI_1_3_KID);
        return [
            "key" => $privatekey,
            "kid" => $kid
        ];
    }

    public static function verifyPrivateKey(): string
    {
        global $ilSetting;
        $key = $ilSetting->get(self::LTI_1_3_PRIVATE_KEY);

        if (empty($key)) {
            $kid = bin2hex(openssl_random_pseudo_bytes(10));
            $ilSetting->set(self::LTI_1_3_KID, $kid);
            $config = array(
                "digest_alg" => "sha256",
                "private_key_bits" => 2048,
                "private_key_type" => self::OPENSSL_KEYTYPE_RSA
            );
            $res = openssl_pkey_new($config);
            openssl_pkey_export($res, $privatekey);
            if (!empty($privatekey)) {
                $ilSetting->set(self::LTI_1_3_PRIVATE_KEY, $privatekey);
            } else {
                return self::ERROR_OPEN_SSL_CONF;
            }
        }
        return '';
    }

    public static function getPublicKey(): string
    {
        $publicKey = null;
        $privateKey = self::getPrivateKey();
        $res = openssl_pkey_get_private($privateKey['key']);
        if ($res !== false) {
            $details = openssl_pkey_get_details($res);
            $publicKey = $details['key'];
        }
        return $publicKey;
    }

    public static function getJwks(): array
    {
        $jwks = ['keys' => []];

        $privatekey = self::getPrivateKey();
        $res = openssl_pkey_get_private($privatekey['key']);
        $details = openssl_pkey_get_details($res);

        $jwk = [];
        $jwk['kty'] = 'RSA';
        $jwk['alg'] = 'RS256';
        $jwk['kid'] = $privatekey['kid'];
        $jwk['e'] = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');
        $jwk['n'] = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
        $jwk['use'] = 'sig';

        $jwks['keys'][] = $jwk;
        return $jwks;
    }

    public static function getIliasHttpPath(): string
    {
        global $DIC;

        if ($DIC['https']->isDetected()) {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        $host = $_SERVER['HTTP_HOST'];

        $rq_uri = strip_tags($_SERVER['REQUEST_URI']);

        // security fix: this failed, if the URI contained "?" and following "/"
        // -> we remove everything after "?"
        if (is_int($pos = strpos($rq_uri, "?"))) {
            $rq_uri = substr($rq_uri, 0, $pos);
        }

        $path = pathinfo($rq_uri);
        if (isset($path['extension']) && $path['extension'] !== '') {
            $uri = dirname($rq_uri);
        } else {
            $uri = $rq_uri;
        }
        $uri = str_replace("Modules/LTIConsumer", "", $uri);
        $iliasHttpPath = ilContext::modifyHttpPath(implode('', [$protocol, $host, $uri]));
        $f = new \ILIAS\Data\Factory();
        $uri = $f->uri(rtrim($iliasHttpPath, "/"));
        return $uri->getBaseURI();
    }

    public static function getPlattformId(): string
    {
        return self::getIliasHttpPath();
    }

    public static function getAuthenticationRequestUrl(): string
    {
        return self::getIliasHttpPath() . "/Modules/LTIConsumer/ltiauth.php";
    }

    public static function getAccessTokenUrl(): string
    {
        return self::getIliasHttpPath() . "/Modules/LTIConsumer/ltitoken.php";
    }

    public static function getPublicKeysetUrl(): string
    {
        return self::getIliasHttpPath() . "/Modules/LTIConsumer/lticerts.php";
    }

    public static function getRegistrationUrl(): string
    {
        return self::getIliasHttpPath() . "/Modules/LTIConsumer/ltiregistration.php";
    }

    public static function getRegistrationStartUrl(): string
    {
        return self::getIliasHttpPath() . "/Modules/LTIConsumer/ltiregstart.php";
    }

    public static function getRegistrationEndUrl(): string
    {
        return self::getIliasHttpPath() . "/Modules/LTIConsumer/ltiregend.php";
    }

    public static function getOpenidConfigUrl(): string
    {
        return self::getIliasHttpPath() . "/Modules/LTIConsumer/lticonfig.php";
    }

    public static function getOpenidConfig(): array
    {
        $scopesSupported = array('openid');
        $gradeservice = new ilLTIConsumerGradeService();
        $scopesSupported = array_merge($scopesSupported, $gradeservice->getPermittedScopes());
        return [
            "issuer" => self::getPlattformId(),
            "authorization_endpoint" => self::getAuthenticationRequestUrl(),
            "token_endpoint" => self::getAccessTokenUrl(),
            "token_endpoint_auth_methods_supported" => ["private_key_jwt"],
            "token_endpoint_auth_signing_alg_values_supported" => ["RS256"],
            "jwks_uri" => self::getPublicKeysetUrl(),
            "registration_endpoint" => self::getRegistrationUrl(),
            "scopes_supported" => $scopesSupported,
            "response_types_supported" => ["id_token"],
            "subject_types_supported" => ["public", "pairwise"],
            "id_token_signing_alg_values_supported" => ["RS256"],
            "claims_supported" => ["iss", "aud"],
            "https://purl.imsglobal.org/spec/lti-platform-configuration" => [
                "product_family_code" => "ilias.de",
                "version" => ILIAS_VERSION,
                "messages_supported" => [
                    [
                        "type" => "LtiResourceLinkRequest",
                        "placements" => [
                        ]
                    ],
                    [
                        "type" => "LtiDeepLinkingRequest",
                        "placements" => [
                        ]
                    ]
                ]
            ]
        ];
    }

    public static function registerClient(array $data, object $tokenObj): array
    {
        // first analyse tool_config and filter only accepted params
        // append client_id (required) and deployment_id(=provider_id in ILIAS) (optional) to tool_config response
        global $DIC;
        $reponseData = $data;
        $provider = new ilLTIConsumeProvider();
        $toolConfig = $data['https://purl.imsglobal.org/spec/lti-tool-configuration'];
        $provider->setTitle($data['client_name']);
        $provider->setProviderUrl($toolConfig['target_link_uri']);
        $provider->setInitiateLogin($data['initiate_login_uri']);
        $provider->setRedirectionUris(implode(",", $data['redirect_uris']));
        if (isset($data['jwks_uri'])) {
            $provider->setPublicKeyset($data['jwks_uri']);
        }
        foreach ($toolConfig['messages'] as $message) {
            if (isset($message['type']) && $message['type'] === 'LtiDeepLinkingRequest') {
                $provider->setContentItemUrl($message['target_link_uri']);
            }
        }
        /*
        if (isset($data['logo_uri'])) { // needs to be uploaded and then assign filepath
            $provider->setProviderIconFilename($data['logo_uri']);
        }
        */
        $provider->setKeyType('JWK_KEYSET');
        $provider->setLtiVersion('1.3.0');
        $provider->setClientId((string)$tokenObj->aud); //client_id
        $provider->setCreator((int)$tokenObj->sub); // user_id
        $provider->setAvailability(ilLTIConsumeProvider::AVAILABILITY_CREATE);
        $provider->setIsGlobal(false);
        $provider->insert();
        $reponseData['client_id'] = $tokenObj->aud;
        $reponseData['https://purl.imsglobal.org/spec/lti-tool-configuration']['deployment_id'] = $provider->getId();
        return $reponseData;
    }

    public static function getNewClientId(): string
    {
        return ILIAS\LTI\ToolProvider\Util::getRandomString(15);
    }

    public static function sendResponseError(int $code, string $message, $log = true): void
    {
        global $DIC;
        try {
            if ($log) {
                self::getLogger()->error("$code $message");
            }
            $DIC->http()->saveResponse(
                $DIC->http()->response()
                    ->withStatus($code)
                    ->withBody(Streams::ofString($message))
            );
            $DIC->http()->sendResponse();
            $DIC->http()->close();
        } catch (Exception $e) {
            $DIC->http()->close();
        }
    }

    public static function sendResponseJson(array $obj): void
    {
        global $DIC;
        try {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
            header('Pragma: no-cache');
            echo json_encode($obj, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            self::sendResponseError(500, "error in sendResponseJson");
            $DIC->http()->close();
        }
    }

    public static function getInstance(int $a_id = 0, bool $a_reference = true): \ilObjLTIConsumer
    {
        return new self($a_id, $a_reference);
    }

    public function isMixedContentType(): bool
    {
        return true;
    }

    public static function getRawData(): ?string
    {
        return file_get_contents('php://input');
    }

    public static function getTokenObject(string $token): ?object
    {
        try {
            $keys = JWK::parseKeySet(self::getJwks());
            return JWT::decode($token, $keys);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function verifyToken(): ?object
    {
        global $DIC;
        $auth = $DIC->http()->request()->getHeader("Authorization");
        if (count($auth) < 1) {
            self::sendResponseError(405, "missing Authorization header");
        }
        preg_match('/Bearer\s+(.+)$/i', $auth[0], $matches);
        if (count($matches) != 2) {
            self::sendResponseError(405, "missing required Authorization Baerer token");
        }
        $token = $matches[1];
        return self::getTokenObject($token);
    }

    public static function getLogger(): ilLogger
    {
        return ilLoggerFactory::getLogger('lti');
    }
}
