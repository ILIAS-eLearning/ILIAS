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

/**
 * Class ilLTIConsumeProviderFormGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumeProviderFormGUI extends ilPropertyFormGUI
{
    /**
     * @var ilLTIConsumeProvider
     */
    protected ilLTIConsumeProvider $provider;

    /**
     * @var bool
     */
    protected bool $adminContext = false;

    /**
     * ilLTIConsumeProviderFormGUI constructor.
     */
    public function __construct(ilLTIConsumeProvider $provider)
    {
        parent::__construct();

        $this->provider = $provider;
    }

    public function isAdminContext(): bool
    {
        return $this->adminContext;
    }

    public function setAdminContext(bool $adminContext): void
    {
        $this->adminContext = $adminContext;
    }

    public function initForm(string $formaction, string $saveCmd, string $cancelCmd): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $lng = $DIC->language();

        $this->setFormAction($formaction);
        $this->addCommandButton($saveCmd, $lng->txt('save'));
        $this->addCommandButton($cancelCmd, $lng->txt('cancel'));

        if ($this->provider->getId() !== 0) {
            $this->setTitle($lng->txt('lti_form_provider_edit'));
        } else {
            $this->setTitle($lng->txt('lti_form_provider_create'));
        }

        $titleInp = new ilTextInputGUI($lng->txt('lti_con_prov_title'), 'title');
        $titleInp->setValue($this->provider->getTitle());
        $titleInp->setRequired(true);
        $this->addItem($titleInp);

        $descInp = new ilTextInputGUI($lng->txt('lti_con_prov_description'), 'description');
        $descInp->setValue($this->provider->getDescription());
        $this->addItem($descInp);

        $iconInp = new ilImageFileInputGUI($lng->txt('lti_con_prov_icon'), 'icon');
        $iconInp->setInfo($lng->txt('obj_tile_image_info'));
        $iconInp->setSuffixes(ilLTIConsumeProviderIcon::getSupportedFileExtensions());
        $iconInp->setUseCache(false);
        if ($this->provider->hasProviderIcon() && $this->provider->getProviderIcon()->exists()) {
            $iconInp->setImage($this->provider->getProviderIcon()->getAbsoluteFilePath());
        } else {
            $iconInp->setImage('');//todo default image?
        }
        $this->addItem($iconInp);

        if ($this->isAdminContext()) {
            $availabilityInp = new ilRadioGroupInputGUI($lng->txt('lti_con_prov_availability'), 'availability');
            $availabilityInp->setValue((string) $this->provider->getAvailability());
            $availabilityInp->setRequired(true);
            $optionCreate = new ilRadioOption(
                $lng->txt('lti_con_prov_availability_create'),
                (string) ilLTIConsumeProvider::AVAILABILITY_CREATE
            );
            $availabilityInp->addOption($optionCreate);
            $optionCreate = new ilRadioOption(
                $lng->txt('lti_con_prov_availability_existing'),
                (string) ilLTIConsumeProvider::AVAILABILITY_EXISTING
            );
            $availabilityInp->addOption($optionCreate);
            $optionCreate = new ilRadioOption(
                $lng->txt('lti_con_prov_availability_non'),
                (string) ilLTIConsumeProvider::AVAILABILITY_NONE
            );
            $availabilityInp->addOption($optionCreate);
            $this->addItem($availabilityInp);
        }


        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle($lng->txt('lti_con_prov_authentication'));
        $this->addItem($sectionHeader);

        $versionInp = new ilRadioGroupInputGUI($lng->txt('lti_con_version'), 'lti_version');

        //1.3
        $lti13 = new ilRadioOption($lng->txt('lti_con_version_1.3'), '1.3.0');
        if ($this->provider->getId() == 0) {
            $lti13->setInfo($lng->txt('lti_con_version_1.3_before_id'));
        }
        $versionInp->addOption($lti13);
        $providerUrlInp = new ilTextInputGUI($lng->txt('lti_con_tool_url'), 'provider_url13');
        $providerUrlInp->setValue($this->provider->getProviderUrl());
        $providerUrlInp->setRequired(true);
        $lti13->addSubItem($providerUrlInp);

        $initiateLogin = new ilTextInputGUI($lng->txt('lti_con_initiate_login_url'), 'initiate_login');
        $initiateLogin->setValue($this->provider->getInitiateLogin());
        $initiateLogin->setRequired(true);
        $lti13->addSubItem($initiateLogin);

        $redirectionUris = new ilTextAreaInputGUI($lng->txt('lti_con_redirection_uris'), 'redirection_uris');
        $redirectionUris->setRows(4);
        $redirectionUris->setValue(implode("\n", explode(",", $this->provider->getRedirectionUris())));
        $redirectionUris->setRequired(true);
        $lti13->addSubItem($redirectionUris);

        //key_type
        $keyType = new ilRadioGroupInputGUI($lng->txt('lti_con_key_type'), 'key_type');
        $keyType->setRequired(true);
        //RSA
        $keyRsa = new ilRadioOption($lng->txt('lti_con_key_type_rsa'), 'RSA_KEY');
        $keyType->addOption($keyRsa);
        $publicKey = new ilTextAreaInputGUI($lng->txt('lti_con_key_type_rsa_public_key'), 'public_key');
        $publicKey->setRows(6);
        $publicKey->setRequired(true);
        $publicKey->setInfo($lng->txt('lti_con_key_type_rsa_public_key_info'));
        $keyRsa->addSubItem($publicKey);
        //JWK
        $keyJwk = new ilRadioOption($lng->txt('lti_con_key_type_jwk'), 'JWK_KEYSET');
        $keyType->addOption($keyJwk);
        $keyset = new ilTextInputGUI($lng->txt('lti_con_key_type_jwk_url'), 'public_keyset');
        $keyset->setValue($this->provider->getPublicKeyset());
        $keyset->setRequired(true);
        $keyJwk->addSubItem($keyset);

        $keyType->setValue($this->provider->getKeyType());
        $lti13->addSubItem($keyType);

        $contentItem = new ilCheckboxInputGUI($lng->txt('lti_con_content_item'), 'content_item');
        $contentItem->setValue('1');
        $contentItem->setChecked($this->provider->isContentItem());
        $contentItemUrl = new ilTextInputGUI($lng->txt('lti_con_content_item_url'), 'content_item_url');
        $contentItemUrl->setValue($this->provider->getContentItemUrl());
        $contentItem->addSubItem($contentItemUrl);
        $lti13->addSubItem($contentItem);

        //grade sync

        if ($this->provider->getId() > 0) {
            $Lti13Info = new ilTextAreaInputGUI($lng->txt('lti13_hints'), 'lti13_hints');
            $Lti13Info->setRows(6);
            $Lti13Info->setValue(
                "Platform ID: \t\t\t\t\t" . $this->provider->getPlattformId()
                . "\nClient ID: \t\t\t\t\t" . $this->provider->getClientId()
                . "\nDeployment ID: \t\t\t\t" . (string) $this->provider->getId()
                . "\nPublic keyset URL: \t\t\t" . $this->provider->getPublicKeysetUrl()
                . "\nAccess token URL: \t\t\t" . $this->provider->getAccessTokenUrl()
                . "\nAuthentication request URL: \t" . $this->provider->getAuthenticationRequestUrl()
            );
            $Lti13Info->setDisabled(true);
            $lti13->addSubItem($Lti13Info);
        }


        $versionInp->setValue($this->provider->getLtiVersion());
        $this->addItem($versionInp);

        $lti11 = new ilRadioOption($lng->txt('lti_con_version_1.1'), 'LTI-1p0');
        $versionInp->addOption($lti11);

        $providerUrlInp = new ilTextInputGUI($lng->txt('lti_con_prov_url'), 'provider_url');
        $providerUrlInp->setValue($this->provider->getProviderUrl());
        $providerUrlInp->setRequired(true);
        $lti11->addSubItem($providerUrlInp);
//        Abfrage ob Key und secret von Objekterstellern eingegeben werden soll
        $keyGlobal = new ilCheckboxInputGUI($lng->txt('lti_con_prov_provider_key_global'), 'provider_key_global');
        $keyGlobal->setValue("1");
        if (!$this->provider->isProviderKeyCustomizable()) {
            $keyGlobal->setChecked(true);
        }
        $keyGlobal->setInfo($lng->txt('lti_con_prov_provider_key_global_info'));

        $providerKeyInp = new ilTextInputGUI($lng->txt('lti_con_prov_key'), 'provider_key');
        $providerKeyInp->setValue($this->provider->getProviderKey());
        $providerKeyInp->setRequired(true);
        $keyGlobal->addSubItem($providerKeyInp);

        $providerSecretInp = new ilTextInputGUI($lng->txt('lti_con_prov_secret'), 'provider_secret');
        $providerSecretInp->setValue($this->provider->getProviderSecret());
        $providerSecretInp->setRequired(true);
        $keyGlobal->addSubItem($providerSecretInp);
        $lti11->addSubItem($keyGlobal);


        //privacy-settings

        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle($lng->txt('lti_con_prov_privacy_settings'));
        $this->addItem($sectionHeader);

        $item = new ilRadioGroupInputGUI($DIC->language()->txt('conf_privacy_ident'), 'privacy_ident');
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_user_id'),
            (string) ilLTIConsumeProvider::PRIVACY_IDENT_IL_UUID_USER_ID
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_user_id_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_login'),
            (string) ilLTIConsumeProvider::PRIVACY_IDENT_IL_UUID_LOGIN
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_login_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_ext_account'),
            (string) ilLTIConsumeProvider::PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_ext_account_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_real_email'),
            (string) ilLTIConsumeProvider::PRIVACY_IDENT_REAL_EMAIL
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_real_email_info'));
        $item->addOption($op);
        $item->setValue((string) $this->provider->getPrivacyIdent());
        $item->setInfo(
            $DIC->language()->txt('conf_privacy_ident_info') . ' ' . ilCmiXapiUser::getIliasUuid()
        );
        $item->setRequired(false);
        $this->addItem($item);

        $item = new ilCheckboxInputGUI($lng->txt('lti_con_prov_instructor_email'), 'instructor_email');
        $item->setValue("1");
        if ($this->provider->isInstructorSendEmail()) {
            $item->setChecked(true);
        }
        $item->setInfo($lng->txt('lti_con_prov_instructor_email_info'));
        $this->addItem($item);

        $item = new ilRadioGroupInputGUI($DIC->language()->txt('conf_privacy_name'), 'privacy_name');
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_none'), (string) ilLTIConsumeProvider::PRIVACY_NAME_NONE);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_none_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_firstname'), (string) ilLTIConsumeProvider::PRIVACY_NAME_FIRSTNAME);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_firstname_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_lastname'), (string) ilLTIConsumeProvider::PRIVACY_NAME_LASTNAME);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_lastname_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_fullname'), (string) ilLTIConsumeProvider::PRIVACY_NAME_FULLNAME);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_fullname_info'));
        $item->addOption($op);
        $item->setValue((string) $this->provider->getPrivacyName());
        $item->setInfo($DIC->language()->txt('conf_privacy_name_info'));
        $item->setRequired(false);
        $this->addItem($item);

        $item = new ilCheckboxInputGUI($lng->txt('lti_con_prov_instructor_name'), 'instructor_name');
        $item->setValue("1");
        if ($this->provider->isInstructorSendName()) {
            $item->setChecked(true);
        }
        $item->setInfo($lng->txt('lti_con_prov_instructor_name_info'));
        $this->addItem($item);

        $includeUserImage = new ilCheckboxInputGUI($lng->txt('lti_con_prov_inc_usr_pic'), 'inc_usr_pic');
        $includeUserImage->setInfo($lng->txt('lti_con_prov_inc_usr_pic_info'));
        $includeUserImage->setChecked($this->provider->getIncludeUserPicture());
        $this->addItem($includeUserImage);

        $item = new ilCheckboxInputGUI($lng->txt('lti_con_prov_external_provider'), 'is_external_provider');
        $item->setValue("1");
        if ($this->provider->IsExternalProvider()) {
            $item->setChecked(true);
        }
        $item->setInfo($lng->txt('lti_con_prov_external_provider_info'));
        $this->addItem($item);


        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle($lng->txt('lti_con_prov_learning_progress_options'));
        $this->addItem($sectionHeader);
        $item = new ilCheckboxInputGUI($lng->txt('lti_con_prov_has_outcome_service'), 'has_outcome_service');
        $item->setValue("1");
        if ($this->provider->getHasOutcome()) {
            $item->setChecked(true);
        }
        $item->setInfo($lng->txt('lti_con_prov_has_outcome_service_info'));
        $masteryScore = new ilNumberInputGUI($lng->txt('lti_con_prov_mastery_score_default'), 'mastery_score');
        $masteryScore->setInfo($lng->txt('lti_con_prov_mastery_score_default_info'));
        $masteryScore->setSuffix('%');
        $masteryScore->allowDecimals(true);
        $masteryScore->setDecimals(2);
        $masteryScore->setMinvalueShouldBeGreater(false);
        $masteryScore->setMinValue(0);
        $masteryScore->setMaxvalueShouldBeLess(false);
        $masteryScore->setMaxValue(100);
        $masteryScore->setSize(4);
        $masteryScore->setValue((string) $this->provider->getMasteryScorePercent());
        $item->addSubItem($masteryScore);
        $this->addItem($item);


        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle($lng->txt('lti_con_prov_launch_options'));
        $this->addItem($sectionHeader);

        $item = new ilCheckboxInputGUI($lng->txt('lti_con_prov_use_provider_id'), 'use_provider_id');
        $item->setValue("1");
        if ($this->provider->getUseProviderId()) {
            $item->setChecked(true);
        }
        $item->setInfo($lng->txt('lti_con_prov_use_provider_id_info'));

        $this->addItem($item);

        $item = new ilCheckboxInputGUI($lng->txt('lti_con_prov_always_learner'), 'always_learner');
        $item->setValue("1");
        if ($this->provider->getAlwaysLearner()) {
            $item->setChecked(true);
        }
        $item->setInfo($lng->txt('lti_con_prov_always_learner_info'));
        $this->addItem($item);

        $item = new ilCheckboxInputGUI($lng->txt('lti_con_prov_use_xapi'), 'use_xapi');
        $item->setValue("1");
        if ($this->provider->getUseXapi()) {
            $item->setChecked(true);
        }
        $item->setInfo($lng->txt('lti_con_prov_use_xapi_info'));

        $subitem = new ilTextInputGUI($lng->txt('lti_con_prov_xapi_launch_url'), 'xapi_launch_url');
        $subitem->setValue($this->provider->getXapiLaunchUrl());
        $subitem->setInfo($lng->txt('lti_con_prov_xapi_launch_url_info'));
        $subitem->setRequired(true);
        $subitem->setMaxLength(255);
        $item->addSubItem($subitem);

        $subitem = new ilTextInputGUI($lng->txt('lti_con_prov_xapi_launch_key'), 'xapi_launch_key');
        $subitem->setValue($this->provider->getXapiLaunchKey());
        $subitem->setInfo($lng->txt('lti_con_prov_xapi_launch_key_info'));
        $subitem->setRequired(true);
        $subitem->setMaxLength(64);
        $item->addSubItem($subitem);

        $subitem = new ilTextInputGUI($lng->txt('lti_con_prov_xapi_launch_secret'), 'xapi_launch_secret');
        $subitem->setValue($this->provider->getXapiLaunchSecret());
        $subitem->setInfo($lng->txt('lti_con_prov_xapi_launch_secret_info'));
        $subitem->setRequired(true);
        $subitem->setMaxLength(64);
        $item->addSubItem($subitem);

        $subitem = new ilTextInputGUI($lng->txt('lti_con_prov_xapi_activity_id'), 'xapi_activity_id');
        $subitem->setValue($this->provider->getXapiActivityId());
        $subitem->setInfo($lng->txt('lti_con_prov_xapi_activity_id_info'));
        $subitem->setMaxLength(128);
        $item->addSubItem($subitem);

        $this->addItem($item);

        $item = new ilTextAreaInputGUI($lng->txt('lti_con_prov_custom_params'), 'custom_params');
        $item->setValue($this->provider->getCustomParams());
        $item->setRows(6);
        $item->setInfo($lng->txt('lti_con_prov_custom_params_info'));
        $this->addItem($item);

        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle($lng->txt('lti_con_prov_group_options'));
        $this->addItem($sectionHeader);

        $item = new ilTextInputGUI($lng->txt('lti_con_prov_keywords'), 'keywords');
        $item->setValue($this->provider->getKeywords());
        $item->setInfo($lng->txt('lti_con_prov_keywords_info'));
        $item->setMaxLength(1000);
        $this->addItem($item);

        $category = new ilRadioGroupInputGUI($DIC->language()->txt('lti_con_prov_category'), 'category');
        $category->setInfo($DIC->language()->txt('lti_con_prov_category_info'));
        $category->setValue($this->provider->getCategory());
        $category->setRequired(true);
        foreach (ilLTIConsumeProvider::getCategoriesSelectOptions() as $value => $label) {
            $category->addOption(new ilRadioOption($label, $value));
        }
        $this->addItem($category);

        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle($lng->txt('lti_con_prov_hints'));
        $this->addItem($sectionHeader);

        $remarksInp = new ilTextAreaInputGUI($lng->txt('lti_con_prov_remarks'), 'remarks');
        $remarksInp->setValue($this->provider->getRemarks());
        $remarksInp->setRows(6);
        $this->addItem($remarksInp);
    }

    public function initProvider(ilLTIConsumeProvider $provider): void
    {
        $provider->setTitle($this->getInput('title'));
        $provider->setDescription($this->getInput('description'));

        $provider->setProviderIconUploadInput($this->getItemByPostVar('icon'));


        $provider->setHasOutcome((bool) $this->getInput('has_outcome_service'));
        $provider->setMasteryScorePercent($this->getInput('mastery_score'));

        if ($this->isAdminContext()) {
            $provider->setAvailability((int) $this->getInput('availability'));
        }

        //authenticate
        $provider->setLtiVersion($this->getInput('lti_version'));
        if ($provider->getLtiVersion() == 'LTI-1p0') {
            if (null !== $this->getInput('provider_url')) {
                $provider->setProviderUrl($this->getInput('provider_url'));
            }
            if ($this->getInput('provider_key_global') == 1) {
                $provider->setProviderKeyCustomizable(false);
                $provider->setProviderKey($this->getInput('provider_key'));
                $provider->setProviderSecret($this->getInput('provider_secret'));
            } else {
                $provider->setProviderKeyCustomizable(true);
            }
        } else {
            if (null !== $this->getInput('provider_url13')) {
                $provider->setProviderUrl($this->getInput('provider_url13'));
            }
            $provider->setInitiateLogin($this->getInput('initiate_login'));
            if (preg_match_all('/\S+/sm', $this->getInput('redirection_uris'), $redirect_uris_matches)) {
                $provider->setRedirectionUris(implode(",", $redirect_uris_matches[0]));
            } else {
                $provider->setRedirectionUris($this->provider->getInitiateLogin());
            }
            $provider->setKeyType($this->getInput('key_type'));
            if ($provider->getKeyType() == 'RSA_KEY') {
                $provider->setPublicKey($this->getInput('public_key'));
            } else {
                $provider->setPublicKeyset($this->getInput('public_keyset'));
            }
            $provider->setContentItem((bool) $this->getInput('content_item'));
            if ($provider->isContentItem()) {
                $provider->setContentItemUrl($this->getInput('content_item_url'));
            }
        }
        $provider->setPrivacyIdent((int) $this->getInput('privacy_ident'));
        $provider->setInstructorSendEmail((bool) $this->getInput('instructor_email'));
        $provider->setPrivacyName((int) $this->getInput('privacy_name'));
        $provider->setInstructorSendName((bool) $this->getInput('instructor_name'));
        $provider->setIncludeUserPicture((bool) $this->getInput('inc_usr_pic'));
        $provider->setIsExternalProvider((bool) $this->getInput('is_external_provider'));

        $provider->setAlwaysLearner((bool) $this->getInput('always_learner'));

        $provider->setUseProviderId((bool) $this->getInput('use_provider_id'));
        $provider->setXapiActivityId($this->getInput('xapi_activity_id'));

        $provider->setUseXapi((bool) $this->getInput('use_xapi'));
        $provider->setXapiLaunchUrl($this->getInput('xapi_launch_url'));
        $provider->setXapiLaunchKey($this->getInput('xapi_launch_key'));
        $provider->setXapiLaunchSecret($this->getInput('xapi_launch_secret'));
        $provider->setCustomParams($this->getInput('custom_params'));
        $provider->setKeywords($this->getInput('keywords'));

        if ($provider->isValidCategory($this->getInput('category'))) {
            $provider->setCategory($this->getInput('category'));
        }

        if ($provider->isProviderKeyCustomizable()) {
            $provider->setProviderKey($this->getInput('provider_key'));
            $provider->setProviderSecret($this->getInput('provider_secret'));
        }
        $provider->setRemarks($this->getInput('remarks'));
    }
}
