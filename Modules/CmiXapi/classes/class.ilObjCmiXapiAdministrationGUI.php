<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjCmiXapiAdministrationGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 *
 * @ilCtrl_Calls ilObjCmiXapiAdministrationGUI: ilPermissionGUI
 */
class ilObjCmiXapiAdministrationGUI extends ilObjectGUI
{
    const TAB_ID_LRS_TYPES = 'tab_lrs_types';
    const TAB_ID_PERMISSIONS = 'perm_settings';
    
    const CMD_SHOW_LRS_TYPES_LIST = 'showLrsTypesList';
    const CMD_SHOW_LRS_TYPE_FORM = 'showLrsTypeForm';
    const CMD_SAVE_LRS_TYPE_FORM = 'saveLrsTypeForm';
    
    const DEFAULT_CMD = self::CMD_SHOW_LRS_TYPES_LIST;
    
    public function getAdminTabs()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->help()->setScreenIdComponent("cmix");
        // lrs types tab
        
        $DIC->tabs()->addTab(
            self::TAB_ID_LRS_TYPES,
            $DIC->language()->txt(self::TAB_ID_LRS_TYPES),
            $DIC->ctrl()->getLinkTargetByClass(self::class)
        );
        
        // permissions tab
        
        $DIC->tabs()->addTab(
            self::TAB_ID_PERMISSIONS,
            $DIC->language()->txt(self::TAB_ID_PERMISSIONS),
            $DIC->ctrl()->getLinkTargetByClass(ilPermissionGUI::class, 'perm')
        );
    }
    
    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->language()->loadLanguageModule('cmix');
        
        $this->prepareOutput();
        
        switch ($DIC->ctrl()->getNextClass()) {
            case 'ilpermissiongui':
                
                $DIC->tabs()->activateTab(self::TAB_ID_PERMISSIONS);
                
                $gui = new ilPermissionGUI($this);
                $DIC->ctrl()->forwardCommand($gui);
                break;
            
            default:
                
                $command = $DIC->ctrl()->getCmd(self::DEFAULT_CMD) . 'Cmd';
                $this->{$command}();
        }
    }
    
    protected function viewCmd()
    {
        $this->showLrsTypesListCmd();
    }
    
    protected function showLrsTypesListCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->tabs()->activateTab(self::TAB_ID_LRS_TYPES);
        
        $toolbar = $this->buildLrsTypesToolbarGUI();
        
        $table = $this->buildLrsTypesTableGUI();
        
        $table->setData(ilCmiXapiLrsTypeList::getTypesData(true));
        
        $DIC->ui()->mainTemplate()->setContent($toolbar->getHTML() . $table->getHTML());
    }
    
    protected function buildLrsTypesTableGUI()
    {
        $table = new ilCmiXapiLrsTypesTableGUI($this, self::CMD_SHOW_LRS_TYPES_LIST);
        return $table;
    }
    
    protected function buildLrsTypesToolbarGUI()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $createTypeButton = ilLinkButton::getInstance();
        $createTypeButton->setCaption('btn_create_lrs_type');
        $createTypeButton->setUrl($DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW_LRS_TYPE_FORM));
        
        $toolbar = new ilToolbarGUI();
        $toolbar->addButtonInstance($createTypeButton);
        
        return $toolbar;
    }
    
    protected function showLrsTypeFormCmd(ilPropertyFormGUI $form = null)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->tabs()->activateTab(self::TAB_ID_LRS_TYPES);
        
        if ($form === null) {
            $lrsType = $this->initLrsType();
            
            $form = $this->buildLrsTypeForm($lrsType);
        }
        
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }
    
    protected function initLrsType()
    {
        if (isset($_POST['lrs_type_id']) && (int) $_POST['lrs_type_id']) {
            return new ilCmiXapiLrsType((int) $_POST['lrs_type_id']);
        }
        
        if (isset($_GET['lrs_type_id']) && (int) $_GET['lrs_type_id']) {
            return new ilCmiXapiLrsType((int) $_GET['lrs_type_id']);
        }
        
        return new ilCmiXapiLrsType();
    }
    
    protected function buildLrsTypeForm(ilCmiXapiLrsType $lrsType)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormAction($this));
        
        if ($lrsType->getTypeId()) {
            $form->setTitle($DIC->language()->txt('edit_lrs_type_form'));
            $form->addCommandButton(self::CMD_SAVE_LRS_TYPE_FORM, $DIC->language()->txt('save'));
        } else {
            $form->setTitle($DIC->language()->txt('create_lrs_type_form'));
            $form->addCommandButton(self::CMD_SAVE_LRS_TYPE_FORM, $DIC->language()->txt('create'));
        }
        
        $form->addCommandButton(self::CMD_SHOW_LRS_TYPES_LIST, $DIC->language()->txt('cancel'));
        
        $hiddenId = new ilHiddenInputGUI('lrs_type_id');
        $hiddenId->setValue($lrsType->getTypeId());
        $form->addItem($hiddenId);
        
        
        $item = new ilTextInputGUI($DIC->language()->txt('conf_title'), 'title');
        $item->setValue($lrsType->getTitle());
        $item->setInfo($DIC->language()->txt('info_title'));
        $item->setRequired(true);
        $item->setMaxLength(255);
        $form->addItem($item);
        
        $item = new ilTextInputGUI($DIC->language()->txt('conf_description'), 'description');
        $item->setValue($lrsType->getDescription());
        $item->setInfo($DIC->language()->txt('info_description'));
        $form->addItem($item);

        $item = new ilRadioGroupInputGUI($DIC->language()->txt('conf_availability'), 'availability');
        $optionCreate = new ilRadioOption(
            $DIC->language()->txt('conf_availability_' . ilCmiXapiLrsType::AVAILABILITY_CREATE),
            ilCmiXapiLrsType::AVAILABILITY_CREATE
        );
        $item->addOption($optionCreate);
        $optionCreate = new ilRadioOption(
            $DIC->language()->txt('conf_availability_' . ilCmiXapiLrsType::AVAILABILITY_EXISTING),
            ilCmiXapiLrsType::AVAILABILITY_EXISTING
        );
        $item->addOption($optionCreate);
        $optionCreate = new ilRadioOption(
            $DIC->language()->txt('conf_availability_' . ilCmiXapiLrsType::AVAILABILITY_NONE),
            ilCmiXapiLrsType::AVAILABILITY_NONE
        );
        $item->addOption($optionCreate);
        $item->setValue($lrsType->getAvailability());
        $item->setInfo($DIC->language()->txt('info_availability'));
        $item->setRequired(true);
        $form->addItem($item);

        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle($DIC->language()->txt('lrs_authentication'));
        $form->addItem($sectionHeader);
        
        $item = new ilTextInputGUI($DIC->language()->txt('conf_lrs_endpoint'), 'lrs_endpoint');
        $item->setValue($lrsType->getLrsEndpoint());
        $item->setInfo($DIC->language()->txt('info_lrs_endpoint'));
        $item->setRequired(true);
        $item->setMaxLength(255);
        $form->addItem($item);
        
        $item = new ilTextInputGUI($DIC->language()->txt('conf_lrs_key'), 'lrs_key');
        $item->setValue($lrsType->getLrsKey());
        $item->setInfo($DIC->language()->txt('info_lrs_key'));
        $item->setRequired(true);
        $item->setMaxLength(128);
        $form->addItem($item);
        
        $item = new ilTextInputGUI($DIC->language()->txt('conf_lrs_secret'), 'lrs_secret');
        $item->setValue($lrsType->getLrsSecret());
        $item->setInfo($DIC->language()->txt('info_lrs_secret'));
        $item->setRequired(true);
        $item->setMaxLength(128);
        $form->addItem($item);
        
        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle($DIC->language()->txt('sect_learning_progress_options'));
        $form->addItem($sectionHeader);
        
        $cronjob = new ilCheckboxInputGUI($DIC->language()->txt('conf_cronjob_neccessary'), 'cronjob_neccessary');
        $cronjob->setInfo($DIC->language()->txt('conf_cronjob_neccessary_info'));
        $cronjob->setChecked($lrsType->isBypassProxyEnabled());
        $form->addItem($cronjob);
        
        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle('Privacy Settings');
        $form->addItem($sectionHeader);
        
        $item = new ilRadioGroupInputGUI($DIC->language()->txt('conf_privacy_ident'), 'privacy_ident');
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_user_id'),
            ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_USER_ID
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_user_id_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_login'),
            ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_LOGIN
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_login_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_ext_account'),
            ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_ext_account_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_sha256'),
            ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_SHA256
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_sha256_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_sha256url'),
            ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_SHA256URL
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_sha256url_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_il_uuid_random'),
            ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_RANDOM
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_random_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_privacy_ident_real_email'),
            ilCmiXapiLrsType::PRIVACY_IDENT_REAL_EMAIL
        );
        $op->setInfo($DIC->language()->txt('conf_privacy_ident_real_email_info'));
        $item->addOption($op);
        $item->setValue($lrsType->getPrivacyIdent());
        $item->setInfo(
            $DIC->language()->txt('conf_privacy_ident_info') . ' ' . ilCmiXapiUser::getIliasUuid()
        );
        $item->setRequired(false);
        $form->addItem($item);
        
        $item = new ilRadioGroupInputGUI($DIC->language()->txt('conf_privacy_name'), 'privacy_name');
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_none'), ilCmiXapiLrsType::PRIVACY_NAME_NONE);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_none_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_firstname'), ilCmiXapiLrsType::PRIVACY_NAME_FIRSTNAME);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_firstname_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_lastname'), ilCmiXapiLrsType::PRIVACY_NAME_LASTNAME);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_lastname_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_fullname'), ilCmiXapiLrsType::PRIVACY_NAME_FULLNAME);
        $op->setInfo($DIC->language()->txt('conf_privacy_name_fullname_info'));
        $item->addOption($op);
        $item->setValue($lrsType->getPrivacyName());
        $item->setInfo($DIC->language()->txt('conf_privacy_name_info'));
        $item->setRequired(false);
        $form->addItem($item);

        $item = new ilCheckboxInputGUI($DIC->language()->txt('only_moveon_label'), 'only_moveon');
        $item->setInfo($DIC->language()->txt('only_moveon_info'));
        $item->setChecked($lrsType->getOnlyMoveon());

        $subitem = new ilCheckboxInputGUI($DIC->language()->txt('achieved_label'), 'achieved');
        $subitem->setInfo($DIC->language()->txt('achieved_info'));
        $subitem->setChecked($lrsType->getAchieved());
        $item->addSubItem($subitem);

        $subitem = new ilCheckboxInputGUI($DIC->language()->txt('answered_label'), 'answered');
        $subitem->setInfo($DIC->language()->txt('answered_info'));
        $subitem->setChecked($lrsType->getAnswered());
        $item->addSubItem($subitem);

        $subitem = new ilCheckboxInputGUI($DIC->language()->txt('completed_label'), 'completed');
        $subitem->setInfo($DIC->language()->txt('completed_info'));
        $subitem->setChecked($lrsType->getCompleted());
        $item->addSubItem($subitem);

        $subitem = new ilCheckboxInputGUI($DIC->language()->txt('failed_label'), 'failed');
        $subitem->setInfo($DIC->language()->txt('failed_info'));
        $subitem->setChecked($lrsType->getFailed());
        $item->addSubItem($subitem);

        $subitem = new ilCheckboxInputGUI($DIC->language()->txt('initialized_label'), 'initialized');
        $subitem->setInfo($DIC->language()->txt('initialized_info'));
        $subitem->setChecked($lrsType->getInitialized());
        $item->addSubItem($subitem);

        $subitem = new ilCheckboxInputGUI($DIC->language()->txt('passed_label'), 'passed');
        $subitem->setInfo($DIC->language()->txt('passed_info'));
        $subitem->setChecked($lrsType->getPassed());
        $item->addSubItem($subitem);

        $subitem = new ilCheckboxInputGUI($DIC->language()->txt('progressed_label'), 'progressed');
        $subitem->setInfo($DIC->language()->txt('progressed_info'));
        $subitem->setChecked($lrsType->getProgressed());
        $item->addSubItem($subitem);

        $subitem = new ilCheckboxInputGUI($DIC->language()->txt('satisfied_label'), 'satisfied');
        $subitem->setInfo($DIC->language()->txt('satisfied_info'));
        $subitem->setChecked($lrsType->getSatisfied());
        $item->addSubItem($subitem);

        $subitem = new ilCheckboxInputGUI($DIC->language()->txt('terminated_label'), 'terminated');
        $subitem->setInfo($DIC->language()->txt('terminated_info'));
        $subitem->setChecked($lrsType->getTerminated());
        $item->addSubItem($subitem);

        $form->addItem($item);

        $item = new ilCheckboxInputGUI($DIC->language()->txt('hide_data_label'), 'hide_data');
        $item->setInfo($DIC->language()->txt('hide_data_info'));
        $item->setChecked($lrsType->getHideData());

        $subitem = new ilCheckboxInputGUI($DIC->language()->txt('timestamp_label'), 'timestamp');
        $subitem->setInfo($DIC->language()->txt('timestamp_info'));
        $subitem->setChecked($lrsType->getTimestamp());
        $item->addSubItem($subitem);

        $subitem = new ilCheckboxInputGUI($DIC->language()->txt('duration_label'), 'duration');
        $subitem->setInfo($DIC->language()->txt('duration_info'));
        $subitem->setChecked($lrsType->getDuration());
        $item->addSubItem($subitem);

        $form->addItem($item);

        $item = new ilCheckboxInputGUI($DIC->language()->txt('no_substatements_label'), 'no_substatements');
        $item->setInfo($DIC->language()->txt('no_substatements_info'));
        $item->setChecked($lrsType->getNoSubstatements());
        $form->addItem($item);

        $item = new ilRadioGroupInputGUI($DIC->language()->txt('conf_privacy_setting_conf'), 'force_privacy_setting');
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_setting_default'), 0);
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_setting_force'), 1);
        $item->addOption($op);
        $item->setValue($lrsType->getForcePrivacySettings());
        $form->addItem($item);
        
        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle('Hints');
        $form->addItem($sectionHeader);

        $item = new ilCheckboxInputGUI($DIC->language()->txt('conf_external_lrs'), 'external_lrs');
        $item->setChecked($lrsType->getExternalLrs());
        $item->setInfo($DIC->language()->txt('info_external_lrs'));
        $form->addItem($item);

        $item = new ilTextAreaInputGUI($DIC->language()->txt('conf_privacy_comment_default'), 'privacy_comment_default');
        $item->setInfo($DIC->language()->txt('info_privacy_comment_default'));
        $item->setValue($lrsType->getPrivacyCommentDefault());
        $item->setRows(5);
        $form->addItem($item);
        
        $item = new ilTextAreaInputGUI($DIC->language()->txt('conf_remarks'), 'remarks');
        $item->setInfo($DIC->language()->txt('info_remarks'));
        $item->setValue($lrsType->getRemarks());
        $item->setRows(5);
        $form->addItem($item);
        
        return $form;
    }
    
    protected function saveLrsTypeFormCmd() : void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $lrsType = $this->initLrsType();
        
        $form = $this->buildLrsTypeForm($lrsType);
        
        if (!$form->checkInput()) {
            $this->showLrsTypeFormCmd($form);
            return;
        }
        
        $lrsType->setTitle($form->getInput("title"));
        $lrsType->setDescription($form->getInput("description"));
        $lrsType->setAvailability($form->getInput("availability"));
        
        $lrsType->setLrsEndpoint(
            ilUtil::removeTrailingPathSeparators($form->getInput("lrs_endpoint"))
        );
        
        $lrsType->setLrsKey($form->getInput("lrs_key"));
        $lrsType->setLrsSecret($form->getInput("lrs_secret"));
        $lrsType->setExternalLrs($form->getInput("external_lrs"));
        $lrsType->setPrivacyIdent($form->getInput("privacy_ident"));
        $lrsType->setPrivacyName($form->getInput("privacy_name"));
        $lrsType->setPrivacyCommentDefault($form->getInput("privacy_comment_default"));
        $lrsType->setRemarks($form->getInput("remarks"));
        
        $oldBypassProxyEnabled = $lrsType->isBypassProxyEnabled();
        $newBypassProxyEnabled = $form->getInput("cronjob_neccessary");
        $lrsType->setBypassProxyEnabled((bool) $newBypassProxyEnabled);
        if ($newBypassProxyEnabled && $newBypassProxyEnabled != $oldBypassProxyEnabled) {
            ilObjCmiXapi::updateByPassProxyFromLrsType($lrsType);
        }

        $lrsType->setOnlyMoveon((bool) $form->getInput("only_moveon"));
        $lrsType->setAchieved((bool) $form->getInput("achieved"));
        $lrsType->setAnswered((bool) $form->getInput("answered"));
        $lrsType->setCompleted((bool) $form->getInput("completed"));
        $lrsType->setFailed((bool) $form->getInput("failed"));
        $lrsType->setInitialized((bool) $form->getInput("initialized"));
        $lrsType->setPassed((bool) $form->getInput("passed"));
        $lrsType->setProgressed((bool) $form->getInput("progressed"));
        $lrsType->setSatisfied((bool) $form->getInput("satisfied"));
        $lrsType->setTerminated((bool) $form->getInput("terminated"));
        $lrsType->setHideData((bool) $form->getInput("hide_data"));
        $lrsType->setTimestamp((bool) $form->getInput("timestamp"));
        $lrsType->setDuration((bool) $form->getInput("duration"));
        $lrsType->setNoSubstatements((bool) $form->getInput("no_substatements"));

        $lrsType->setForcePrivacySettings((bool) $form->getInput("force_privacy_setting"));
        if ($lrsType->getForcePrivacySettings()) {
            ilObjCmiXapi::updatePrivacySettingsFromLrsType($lrsType);
        }
        
        $lrsType->save();
        
        $DIC->ctrl()->redirect($this, self::CMD_SHOW_LRS_TYPES_LIST);
    }
}
