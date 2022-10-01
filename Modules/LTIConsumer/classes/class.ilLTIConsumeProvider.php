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

use ILIAS\Filesystem\Exception\IOException;

/**
 * Class ilLTIConsumeProvider
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumeProvider
{
    protected int $id = 0;

    protected string $title = '';

    protected string $description = '';

    protected int $availability = self::AVAILABILITY_NONE;
    public const AVAILABILITY_NONE = 0;  // Provider is not longer available (error message)
    public const AVAILABILITY_EXISTING = 1; // Existing objects can still use the provider, new objects not
    public const AVAILABILITY_CREATE = 2;  // New objects can use this provider

    protected string $remarks = '';

    protected int $time_to_delete = 0;

    protected int $log_level = 0;

    protected string $provider_url = '';

    protected string $provider_key = '';

    protected string $provider_secret = '';

    protected bool $provider_key_customizable = true;

    protected string $provider_icon_filename = '';

    /**
     * @var ilLTIConsumeProviderIcon|null
     */
    protected ?ilLTIConsumeProviderIcon $providerIcon = null;

    /**
     * @var ilImageFileInputGUI|null
     */
    protected ?ilImageFileInputGUI $providerIconUploadInput = null;

    public const CATEGORY_ASSESSMENT = 'assessment';
    public const CATEGORY_FEEDBACK = 'feedback';
    public const CATEGORY_CONTENT = 'content';
    public const CATEGORY_COMMUNICATION = 'communication';
    public const CATEGORY_ORGANISATION = 'organisation';
    /**
     * @var string
     */
    protected string $category = self::CATEGORY_CONTENT;

    protected string $provider_xml = '';

    protected bool $is_external_provider = false;

    //ToDo : necessary?
    public const LAUNCH_METHOD_OWN = 'ownWin';
    public const LAUNCH_METHOD_NEW = 'newWin';
    protected string $launch_method = self::LAUNCH_METHOD_NEW;

    protected bool $has_outcome = false;

    protected float $mastery_score = 0.8;

    protected bool $keep_lp = false;

    public const PRIVACY_IDENT_IL_UUID_USER_ID = 0;
    public const PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT = 1;
    public const PRIVACY_IDENT_IL_UUID_LOGIN = 2;
    public const PRIVACY_IDENT_REAL_EMAIL = 3;
    protected int $privacy_ident = self::PRIVACY_IDENT_IL_UUID_USER_ID;

    public const PRIVACY_NAME_NONE = 0;
    public const PRIVACY_NAME_FIRSTNAME = 1;
    public const PRIVACY_NAME_LASTNAME = 2;
    public const PRIVACY_NAME_FULLNAME = 3;
    protected int $privacy_name = self::PRIVACY_NAME_NONE;

    protected bool $include_user_picture = false;

    protected string $privacy_comment_default = '';

    protected bool $always_learner = false;

    protected bool $use_provider_id = false;

    protected bool $use_xapi = false;

    protected string $xapi_launch_url = '';

    protected string $xapi_launch_key = '';

    protected string $xapi_launch_secret = '';

    protected string $xapi_activity_id = '';

    protected string $custom_params = '';

    protected string $keywords = '';

    protected int $creator = 0;

    protected int $accepted_by = 0;

    protected bool $is_global = false;

    protected bool $instructor_send_name = false;

    protected bool $instructor_send_email = false;

    protected string $client_id = '';

    protected string $enabled_capability = '';

    protected string $key_type = '';

    protected string $public_key = '';

    protected string $public_keyset = '';

    protected string $initiate_login = '';

    protected string $redirection_uris = '';

    protected bool $content_item = false;

    protected string $content_item_url = '';

    protected bool $grade_synchronization = false;

    protected string $lti_version = 'LTI-1p0';


    /**
     * ilLTIConsumeProvider constructor.
     * @throws IOException
     */
    public function __construct(?int $providerId = null)
    {
        if ($providerId) {
            $this->setId($providerId);
            $this->load();
        }
    }

    /**
     * Inits class static
     * @throws IOException
     */
    public static function getInstance(?int $providerId = null): ilLTIConsumeProvider
    {
        return new self($providerId);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getAvailability(): int
    {
        return $this->availability;
    }

    public function setAvailability(int $availability): void
    {
        $this->availability = $availability;
    }

    public function getRemarks(): string
    {
        return $this->remarks;
    }

    public function setRemarks(string $remarks): void
    {
        $this->remarks = $remarks;
    }

    public function getTimeToDelete(): int
    {
        return $this->time_to_delete;
    }

    public function setTimeToDelete(int $time_to_delete): void
    {
        $this->time_to_delete = $time_to_delete;
    }

    public function getLogLevel(): int
    {
        return $this->log_level;
    }

    public function setLogLevel(int $log_level): void
    {
        $this->log_level = $log_level;
    }

    public function getProviderUrl(): string
    {
        return $this->provider_url;
    }

    public function setProviderUrl(string $provider_url): void
    {
        $this->provider_url = $provider_url;
    }

    public function getProviderKey(): string
    {
        return $this->provider_key;
    }

    public function setProviderKey(string $provider_key): void
    {
        $this->provider_key = $provider_key;
    }

    public function getProviderSecret(): string
    {
        return $this->provider_secret;
    }

    public function setProviderSecret(string $provider_secret): void
    {
        $this->provider_secret = $provider_secret;
    }

    public function isProviderKeyCustomizable(): bool
    {
        return $this->provider_key_customizable;
    }

    public function setProviderKeyCustomizable(bool $provider_key_customizable): void
    {
        $this->provider_key_customizable = $provider_key_customizable;
    }

    public function getProviderIconFilename(): string
    {
        return $this->provider_icon_filename;
    }

    public function setProviderIconFilename(string $provider_icon_filename): void
    {
        $this->provider_icon_filename = $provider_icon_filename;
    }

    public function getProviderIcon(): ?\ilLTIConsumeProviderIcon
    {
        return $this->providerIcon;
    }

    public function hasProviderIcon(): bool
    {
        if (!($this->providerIcon instanceof ilLTIConsumeProviderIcon)) {
            return false;
        }

        return (bool) strlen($this->providerIcon->getFilename());
    }

    public function setProviderIcon(ilLTIConsumeProviderIcon $providerIcon): void
    {
        $this->providerIcon = $providerIcon;
    }

    public function hasProviderIconUploadInput(): bool
    {
        return $this->providerIconUploadInput instanceof ilImageFileInputGUI;
    }

    public function getProviderIconUploadInput(): ?\ilImageFileInputGUI
    {
        return $this->providerIconUploadInput;
    }

    /**
     * @param ilImageFileInputGUI|ilFormPropertyGUI $providerIconUploadInput
     */
    public function setProviderIconUploadInput(ilFormPropertyGUI $providerIconUploadInput): void
    {
        $this->providerIconUploadInput = $providerIconUploadInput;
    }

    /**
     * @return array<int|string, mixed>
     */
    public static function getCategoriesSelectOptions(): array
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $categories = [];
        $translation = '';

        foreach (self::getValidCategories() as $category) {
            switch ($category) {
                case self::CATEGORY_ORGANISATION:

                    $translation = $DIC->language()->txt('rep_add_new_def_grp_organisation');
                    break;
                case self::CATEGORY_COMMUNICATION:

                    $translation = $DIC->language()->txt('rep_add_new_def_grp_communication');
                    break;
                case self::CATEGORY_CONTENT:

                    $translation = $DIC->language()->txt('rep_add_new_def_grp_content');
                    break;
                case self::CATEGORY_ASSESSMENT:

                    $translation = $DIC->language()->txt('rep_add_new_def_grp_assessment');
                    break;
                case self::CATEGORY_FEEDBACK:

                    $translation = $DIC->language()->txt('rep_add_new_def_grp_feedback');
                    break;
            }

            $categories[$category] = $translation;
        }

        return $categories;
    }

    public static function getValidCategories(): array
    {
        return [
            self::CATEGORY_ORGANISATION,
            self::CATEGORY_COMMUNICATION,
            self::CATEGORY_CONTENT,
            self::CATEGORY_ASSESSMENT,
            self::CATEGORY_FEEDBACK
        ];
    }

    public function isValidCategory(string $category): bool
    {
        return in_array($category, self::getValidCategories());
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    public function getProviderXml(): string
    {
        return $this->provider_xml;
    }

    public function setProviderXml(string $provider_xml): void
    {
        $this->provider_xml = $provider_xml;
    }

    public function isExternalProvider(): bool
    {
        return $this->is_external_provider;
    }

    public function setIsExternalProvider(bool $is_external_provider): void
    {
        $this->is_external_provider = $is_external_provider;
    }

    public function getLaunchMethod(): string
    {
        return $this->launch_method;
    }

    public function setLaunchMethod(string $launch_method): void
    {
        $this->launch_method = $launch_method;
    }

    public function getHasOutcome(): bool
    {
        return $this->has_outcome;
    }

    public function setHasOutcome(bool $has_outcome): void
    {
        $this->has_outcome = $has_outcome;
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

    public function isKeepLp(): bool
    {
        return $this->keep_lp;
    }

    public function setKeepLp(bool $keep_lp): void
    {
        $this->keep_lp = $keep_lp;
    }


    public function getPrivacyIdent(): int
    {
        return $this->privacy_ident;
    }

    public function setPrivacyIdent(int $privacy_ident): void
    {
        $this->privacy_ident = $privacy_ident;
    }

    public function getPrivacyName(): int
    {
        return $this->privacy_name;
    }

    public function setPrivacyName(int $privacy_name): void
    {
        $this->privacy_name = $privacy_name;
    }

    public function getIncludeUserPicture(): bool
    {
        return $this->include_user_picture;
    }

    public function setIncludeUserPicture(bool $include_user_picture): void
    {
        $this->include_user_picture = $include_user_picture;
    }

    public function getPrivacyCommentDefault(): string
    {
        return $this->privacy_comment_default;
    }

    public function setPrivacyCommentDefault(string $privacy_comment_default): void
    {
        $this->privacy_comment_default = $privacy_comment_default;
    }

    public function getAlwaysLearner(): bool
    {
        return $this->always_learner;
    }

    public function setAlwaysLearner(bool $always_learner): void
    {
        $this->always_learner = $always_learner;
    }

    public function getUseProviderId(): bool
    {
        return $this->use_provider_id;
    }

    public function setUseProviderId(bool $use_provider_id): void
    {
        $this->use_provider_id = $use_provider_id;
    }

    public function getUseXapi(): bool
    {
        return $this->use_xapi;
    }

    public function setUseXapi(bool $use_xapi): void
    {
        $this->use_xapi = $use_xapi;
    }

    public function getXapiLaunchUrl(): string
    {
        return $this->xapi_launch_url;
    }

    public function setXapiLaunchUrl(string $xapi_launch_url): void
    {
        $this->xapi_launch_url = $xapi_launch_url;
    }

    public function getXapiLaunchKey(): string
    {
        return $this->xapi_launch_key;
    }

    public function setXapiLaunchKey(string $xapi_launch_key): void
    {
        $this->xapi_launch_key = $xapi_launch_key;
    }

    public function getXapiLaunchSecret(): string
    {
        return $this->xapi_launch_secret;
    }

    public function setXapiLaunchSecret(string $xapi_launch_secret): void
    {
        $this->xapi_launch_secret = $xapi_launch_secret;
    }

    public function getXapiActivityId(): string
    {
        return $this->xapi_activity_id;
    }

    public function setXapiActivityId(string $xapi_activity_id): void
    {
        $this->xapi_activity_id = $xapi_activity_id;
    }

    public function getCustomParams(): string
    {
        return $this->custom_params;
    }

    public function setCustomParams(string $custom_params): void
    {
        $this->custom_params = $custom_params;
    }

    /**
     * @return string[]
     */
    public function getKeywordsArray(): array
    {
        $keywords = [];

        foreach (explode(';', $this->getKeywords()) as $keyword) {
            $keywords[] = trim($keyword);
        }

        return $keywords;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords): void
    {
        $this->keywords = $keywords;
    }

    public function getCreator(): int
    {
        return $this->creator;
    }

    public function setCreator(int $creator): void
    {
        $this->creator = $creator;
    }

    public function getAcceptedBy(): int
    {
        return $this->accepted_by;
    }

    public function setAcceptedBy(int $accepted_by): void
    {
        $this->accepted_by = $accepted_by;
    }

    public function isGlobal(): bool
    {
        return $this->is_global;
    }

    public function setIsGlobal(bool $is_global): void
    {
        $this->is_global = $is_global;
    }

    public function isInstructorSendName(): bool
    {
        return $this->instructor_send_name;
    }

    public function setInstructorSendName(bool $instructor_send_name): void
    {
        $this->instructor_send_name = $instructor_send_name;
    }

    public function isInstructorSendEmail(): bool
    {
        return $this->instructor_send_email;
    }

    public function setInstructorSendEmail(bool $instructor_send_email): void
    {
        $this->instructor_send_email = $instructor_send_email;
    }

    public function getClientId(): string
    {
        if ($this->client_id == '') {
            //ohne Sonderzeichen
            $this->client_id = ILIAS\LTI\ToolProvider\Util::getRandomString(15);
        }
        return $this->client_id;
    }

    public function setClientId(string $client_id): void
    {
        $this->client_id = $client_id;
    }

    public function getEnabledCapability(): string
    {
        return $this->enabled_capability;
    }

    public function setEnabledCapability(string $enabled_capability): void
    {
        $this->enabled_capability = $enabled_capability;
    }

    public function getKeyType(): string
    {
        return $this->key_type;
    }

    public function setKeyType(string $key_type): void
    {
        $this->key_type = $key_type;
    }

    public function getPublicKey(): string
    {
        return $this->public_key;
    }

    public function setPublicKey(string $public_key): void
    {
        $this->public_key = $public_key;
    }

    public function getPublicKeyset(): string
    {
        return $this->public_keyset;
    }

    public function setPublicKeyset(string $public_keyset): void
    {
        $this->public_keyset = $public_keyset;
    }

    public function getInitiateLogin(): string
    {
        return $this->initiate_login;
    }

    public function setInitiateLogin(string $initiate_login): void
    {
        $this->initiate_login = $initiate_login;
    }

    public function getRedirectionUris(): string
    {
        return $this->redirection_uris;
    }

    public function setRedirectionUris(string $redirection_uris): void
    {
        $this->redirection_uris = $redirection_uris;
    }

    public function isContentItem(): bool
    {
        return $this->content_item;
    }

    public function setContentItem(bool $content_item): void
    {
        $this->content_item = $content_item;
    }

    public function getContentItemUrl(): string
    {
        return $this->content_item_url;
    }

    public function setContentItemUrl(string $content_item_url): void
    {
        $this->content_item_url = $content_item_url;
    }

    public function isGradeSynchronization(): bool
    {
        return $this->grade_synchronization;
    }

    public function setGradeSynchronization(bool $grade_synchronization): void
    {
        $this->grade_synchronization = $grade_synchronization;
    }

    public function getLtiVersion(): string
    {
        return $this->lti_version;
    }

    public function setLtiVersion(string $lti_version): void
    {
        $this->lti_version = $lti_version;
    }




    /**
     * @throws IOException
     */
    public function assignFromDbRow(array $dbRow): void
    {
        foreach ($dbRow as $field => $value) {
            switch ($field) {
                case 'id': $this->setId((int) $value);
                    break;
                case 'title': $this->setTitle($value);
                    break;
                case 'description': $this->setDescription($value);
                    break;
                case 'availability': $this->setAvailability((int) $value);
                    break;
                case 'remarks': $this->setRemarks($value);
                    break;
                case 'time_to_delete': $this->setTimeToDelete((int) $value);
                    break;
                case 'log_level': $this->setLogLevel((int) $value);
                    break;
                case 'provider_url': $this->setProviderUrl($value);
                    break;
                case 'provider_key': $this->setProviderKey($value);
                    break;
                case 'provider_secret': $this->setProviderSecret($value);
                    break;
                case 'provider_key_customizable': $this->setProviderKeyCustomizable((bool) $value);
                    break;
                case 'provider_icon': $this->setProviderIconFilename($value);
                    break;
                case 'category': $this->setCategory($value);
                    break;
                case 'provider_xml': $this->setProviderXml($value);
                    break;
                case 'external_provider': $this->setIsExternalProvider((bool) $value);
                    break;
                case 'launch_method': $this->setLaunchMethod($value);
                    break;
                case 'has_outcome': $this->setHasOutcome((bool) $value);
                    break;
                case 'mastery_score': $this->setMasteryScore((float) $value);
                    break;
                case 'keep_lp': $this->setKeepLp((bool) $value);
                    break;
                case 'privacy_ident': $this->setPrivacyIdent((int) $value);
                    break;
                case 'privacy_name': $this->setPrivacyName((int) $value);
                    break;
                case 'inc_usr_pic': $this->setIncludeUserPicture((bool) $value);
                    break;
                case 'privacy_comment_default': $this->setPrivacyCommentDefault($value);
                    break;
                case 'always_learner': $this->setAlwaysLearner((bool) $value);
                    break;
                case 'use_provider_id': $this->setUseProviderId((bool) $value);
                    break;
                case 'use_xapi': $this->setUseXapi((bool) $value);
                    break;
                case 'xapi_launch_url': $this->setXapiLaunchUrl((string) $value);
                    break;
                case 'xapi_launch_key': $this->setXapiLaunchKey((string) $value);
                    break;
                case 'xapi_launch_secret': $this->setXapiLaunchSecret((string) $value);
                    break;
                case 'xapi_activity_id': $this->setXapiActivityId((string) $value);
                    break;
                case 'custom_params': $this->setCustomParams((string) $value);
                    break;
                case 'keywords': $this->setKeywords((string) $value);
                    break;
                case 'creator': $this->setCreator((int) $value);
                    break;
                case 'accepted_by': $this->setAcceptedBy((int) $value);
                    break;
                case 'global': $this->setIsGlobal((bool) $value);
                    break;
                case 'instructor_send_name': $this->setInstructorSendName((bool) $value);
                    break;
                case 'instructor_send_email': $this->setInstructorSendEmail((bool) $value);
                    break;
                case 'client_id': $this->setClientId((string) $value);
                    break;
                case 'enabled_capability': $this->setEnabledCapability((string) $value);
                    break;
                case 'key_type': $this->setKeyType((string) $value);
                    break;
                case 'public_key': $this->setPublicKey((string) $value);
                    break;
                case 'public_keyset': $this->setPublicKeyset((string) $value);
                    break;
                case 'initiate_login': $this->setInitiateLogin((string) $value);
                    break;
                case 'redirection_uris': $this->setRedirectionUris((string) $value);
                    break;
                case 'content_item': $this->setContentItem((bool) $value);
                    break;
                case 'content_item_url': $this->setContentItemUrl((string) $value);
                    break;
                case 'grade_synchronization': $this->setGradeSynchronization((bool) $value);
                    break;
                case 'lti_version': $this->setLtiVersion((string) $value);
                    break;
            }
        }

        $this->setProviderIcon(new ilLTIConsumeProviderIcon($this->getId()));
        $this->getProviderIcon()->setFilename($this->getProviderIconFilename());
    }

    /**
     * @throws IOException
     */
    public function load(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $query = "SELECT * FROM lti_ext_provider WHERE id = %s";
        $res = $DIC->database()->queryF($query, array('integer'), array($this->getId()));

        while ($row = $DIC->database()->fetchAssoc($res)) {
            $this->assignFromDbRow($row);
        }
    }

    /**
     * @throws \ILIAS\FileUpload\Exception\IllegalStateException
     * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
     * @throws IOException
     */
    public function save(): void
    {
        if ($this->getId() !== 0) {
            if ($this->hasProviderIconUploadInput()) {
                $this->getProviderIcon()->handleUploadInputSubission($this->getProviderIconUploadInput());
                $this->setProviderIconFilename($this->getProviderIcon()->getFilename());
            }

            $this->update();
        } else {
            $this->insert();

            if ($this->hasProviderIconUploadInput()) {
                $this->setProviderIcon(new ilLTIConsumeProviderIcon($this->getId()));

                $this->getProviderIcon()->handleUploadInputSubission($this->getProviderIconUploadInput());
                $this->setProviderIconFilename($this->getProviderIcon()->getFilename());

                $this->update();
            }
        }
    }

    public function update(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->database()->update('lti_ext_provider', $this->getInsertUpdateFields(), array(
                'id' => array('integer', $this->getId()),
        ));
    }

    public function insert(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $this->setId($DIC->database()->nextId('lti_ext_provider'));

        $DIC->database()->insert('lti_ext_provider', $this->getInsertUpdateFields());
    }

    /**
     * @return array<string, array<bool|float|int|string>>
     */
    protected function getInsertUpdateFields(): array
    {
        return array(
            'id' => array('integer', $this->getId()),
            'title' => array('text', $this->getTitle()),
            'description' => array('text', $this->getDescription()),
            'availability' => array('integer', $this->getAvailability()),
            'remarks' => array('text', $this->getRemarks()),
            'time_to_delete' => array('integer', $this->getTimeToDelete()),
            'provider_url' => array('text', $this->getProviderUrl()),
            'provider_key' => array('text', $this->getProviderKey()),
            'provider_secret' => array('text', $this->getProviderSecret()),
            'provider_key_customizable' => array('integer', $this->isProviderKeyCustomizable()),
            'provider_icon' => array('text', $this->getProviderIconFilename()),
            'category' => array('text', $this->getCategory()),
            'provider_xml' => array('text', $this->getProviderXml()),
            'external_provider' => array('integer', $this->isExternalProvider()),
            'launch_method' => array('text', $this->getLaunchMethod()),
            'has_outcome' => array('integer', $this->getHasOutcome()),
            'mastery_score' => array('float', $this->getMasteryScore()),
            'keep_lp' => array('integer', $this->isKeepLp()),
            'privacy_ident' => array('integer', $this->getPrivacyIdent()),
            'privacy_name' => array('integer', $this->getPrivacyName()),
            'inc_usr_pic' => array('integer', $this->getIncludeUserPicture()),
            'privacy_comment_default' => array('text', $this->getPrivacyCommentDefault()),
            'always_learner' => array('integer', $this->getAlwaysLearner()),
            'use_provider_id' => array('integer', $this->getUseProviderId()),
            'use_xapi' => array('integer', $this->getUseXapi()),
            'xapi_launch_url' => array('text', $this->getXapiLaunchUrl()),
            'xapi_launch_key' => array('text', $this->getXapiLaunchKey()),
            'xapi_launch_secret' => array('text', $this->getXapiLaunchSecret()),
            'xapi_activity_id' => array('text', $this->getXapiActivityId()),
            'custom_params' => array('text', $this->getCustomParams()),
            'keywords' => array('text', $this->getKeywords()),
            'creator' => array('integer', $this->getCreator()),
            'accepted_by' => array('integer', $this->getAcceptedBy()),
            'global' => array('integer', (int) $this->isGlobal()),
            'instructor_send_name' => array('integer', (int) $this->isInstructorSendName()),
            'instructor_send_email' => array('integer', (int) $this->isInstructorSendEmail()),
            'client_id' => array('text', $this->getClientId()),
            'enabled_capability' => array('text', $this->getEnabledCapability()),
            'key_type' => array('text', $this->getKeyType()),
            'public_key' => array('text', $this->getPublicKey()),
            'public_keyset' => array('text', $this->getPublicKeyset()),
            'initiate_login' => array('text', $this->getInitiateLogin()),
            'redirection_uris' => array('text', $this->getRedirectionUris()),
            'content_item' => array('integer', (int) $this->isContentItem()),
            'content_item_url' => array('text', $this->getContentItemUrl()),
            'grade_synchronization' => array('integer', (int) $this->isGradeSynchronization()),
            'lti_version' => array('text', $this->getLtiVersion())
        );
    }

    public function delete(): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->database()->manipulateF(
            "DELETE FROM lti_ext_provider WHERE id = %s",
            ['integer'],
            [$this->getId()]
        );
    }

    public function isAcceptableAsGlobal(): bool
    {
        return !$this->isGlobal() && (bool) $this->getCreator();
    }

    public function isResetableToUserDefined(): bool
    {
        return $this->isGlobal() && (bool) $this->getCreator();
    }

    public function getPlattformId(): string {
        return ILIAS_HTTP_PATH;
    }

    public function getAuthenticationRequestUrl(): string {
        return ILIAS_HTTP_PATH ."/Modules/LTIConsumer/ltiauth.php";
    }

    public function getAccessTokenUrl(): string {
        return ILIAS_HTTP_PATH ."/Modules/LTIConsumer/ltitoken.php";
    }

    public function getPublicKeysetUrl(): string {
        return ILIAS_HTTP_PATH ."/Modules/LTIConsumer/lticerts.php";
    }
}