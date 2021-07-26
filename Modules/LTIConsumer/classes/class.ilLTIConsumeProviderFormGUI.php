<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    protected $provider;
    
    /**
     * @var bool
     */
    protected $adminContext = false;
    
    /**
     * ilLTIConsumeProviderFormGUI constructor.
     * @param ilLTIConsumeProvider $provider
     */
    public function __construct(ilLTIConsumeProvider $provider)
    {
        parent::__construct();
        
        $this->provider = $provider;
    }
    
    /**
     * @return bool
     */
    public function isAdminContext() : bool
    {
        return $this->adminContext;
    }
    
    /**
     * @param bool $adminContext
     */
    public function setAdminContext(bool $adminContext)
    {
        $this->adminContext = $adminContext;
    }
    
    /**
     * @param $formaction
     * @param $saveCmd
     * @param $cancelCmd
     */
    public function initForm($formaction, $saveCmd, $cancelCmd)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $lng = $DIC->language();
        
        $this->setFormAction($formaction);
        $this->addCommandButton($saveCmd, $lng->txt('save'));
        $this->addCommandButton($cancelCmd, $lng->txt('cancel'));
        
        if ($this->provider->getId()) {
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
            $availabilityInp->setValue($this->provider->getAvailability());
            $availabilityInp->setRequired(true);
            $optionCreate = new ilRadioOption(
                $lng->txt('lti_con_prov_availability_create'),
                ilLTIConsumeProvider::AVAILABILITY_CREATE
            );
            $availabilityInp->addOption($optionCreate);
            $optionCreate = new ilRadioOption(
                $lng->txt('lti_con_prov_availability_existing'),
                ilLTIConsumeProvider::AVAILABILITY_EXISTING
            );
            $availabilityInp->addOption($optionCreate);
            $optionCreate = new ilRadioOption(
                $lng->txt('lti_con_prov_availability_non'),
                ilLTIConsumeProvider::AVAILABILITY_NONE
            );
            $availabilityInp->addOption($optionCreate);
            $this->addItem($availabilityInp);
        }
        
        
        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle($lng->txt('lti_con_prov_authentication'));
        $this->addItem($sectionHeader);
        
        
        $providerUrlInp = new ilTextInputGUI($lng->txt('lti_con_prov_url'), 'provider_url');
        $providerUrlInp->setValue($this->provider->getProviderUrl());
        $providerUrlInp->setRequired(true);
        $this->addItem($providerUrlInp);
        //Abfrage ob Key und secret von Objekterstellern eingegeben werden soll
        $item = new ilCheckboxInputGUI($lng->txt('lti_con_prov_provider_key_global'), 'provider_key_global');
        $item->setValue("1");
        if (!$this->provider->isProviderKeyCustomizable()) {
            $item->setChecked(true);
        }
        $item->setInfo($lng->txt('lti_con_prov_provider_key_global_info'));

        $providerKeyInp = new ilTextInputGUI($lng->txt('lti_con_prov_key'), 'provider_key');
        $providerKeyInp->setValue($this->provider->getProviderKey());
        $providerKeyInp->setRequired(true);
        $item->addSubItem($providerKeyInp);
        
        $providerSecretInp = new ilTextInputGUI($lng->txt('lti_con_prov_secret'), 'provider_secret');
        $providerSecretInp->setValue($this->provider->getProviderSecret());
        $providerSecretInp->setRequired(true);
        $item->addSubItem($providerSecretInp);
        
        $this->addItem($item);

        //privacy-settings

        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle($lng->txt('lti_con_prov_privacy_settings'));
        $this->addItem($sectionHeader);
        
        $item = new ilRadioGroupInputGUI($DIC->language()->txt('conf_privacy_ident'), 'privacy_ident');
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_user_id'),
            ilLTIConsumeProvider::PRIVACY_IDENT_IL_UUID_USER_ID
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_user_id_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_login'),
            ilLTIConsumeProvider::PRIVACY_IDENT_IL_UUID_LOGIN
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_login_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_ext_account'),
            ilLTIConsumeProvider::PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_ext_account_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_real_email'),
            ilLTIConsumeProvider::PRIVACY_IDENT_REAL_EMAIL
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_real_email_info'));
        $item->addOption($op);
        $item->setValue($this->provider->getPrivacyIdent());
        $item->setInfo(
            $DIC->language()->txt('conf_privacy_ident_info') . ' ' . ilCmiXapiUser::getIliasUuid()
        );
        $item->setRequired(false);
        $this->addItem($item);
        
        $item = new ilRadioGroupInputGUI($DIC->language()->txt('conf_privacy_name'), 'privacy_name');
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_none'), ilLTIConsumeProvider::PRIVACY_NAME_NONE);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_none_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_firstname'), ilLTIConsumeProvider::PRIVACY_NAME_FIRSTNAME);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_firstname_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_lastname'), ilLTIConsumeProvider::PRIVACY_NAME_LASTNAME);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_lastname_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_fullname'), ilLTIConsumeProvider::PRIVACY_NAME_FULLNAME);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_fullname_info'));
        $item->addOption($op);
        $item->setValue($this->provider->getPrivacyName());
        $item->setInfo($DIC->language()->txt('conf_privacy_name_info'));
        $item->setRequired(false);
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
        $masteryScore->setValue($this->provider->getMasteryScorePercent());
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
    
    public function initProvider(ilLTIConsumeProvider $provider)
    {
        $provider->setTitle($this->getInput('title'));
        $provider->setDescription($this->getInput('description'));
        
        $provider->setProviderIconUploadInput($this->getItemByPostVar('icon'));
        
        
        $provider->setHasOutcome((bool) $this->getInput('has_outcome_service'));
        $provider->setMasteryScorePercent($this->getInput('mastery_score'));
        
        if ($this->isAdminContext()) {
            $provider->setAvailability($this->getInput('availability'));
        }
        
        if ($this->getInput('provider_key_global') == 1) {
            $provider->setProviderKeyCustomizable(false);
            $provider->setProviderKey($this->getInput('provider_key'));
            $provider->setProviderSecret($this->getInput('provider_secret'));
        } else {
            $provider->setProviderKeyCustomizable(true);
        }
        $provider->setPrivacyIdent($this->getInput('privacy_ident'));
        $provider->setPrivacyName($this->getInput('privacy_name'));
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
        
        if (null !== $this->getInput('provider_url')) {
            $provider->setProviderUrl($this->getInput('provider_url'));
        }
        if ($provider->isProviderKeyCustomizable()) {
            $provider->setProviderKey($this->getInput('provider_key'));
            $provider->setProviderSecret($this->getInput('provider_secret'));
        }
        $provider->setRemarks($this->getInput('remarks'));
    }
}
