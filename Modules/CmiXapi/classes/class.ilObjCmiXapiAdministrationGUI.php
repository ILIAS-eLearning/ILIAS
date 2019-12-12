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
        return $this->showLrsTypesListCmd();
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
        
        $item = new ilRadioGroupInputGUI($DIC->language()->txt('conf_user_ident'), 'user_ident');
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_user_ident_il_uuid_user_id'),
            ilCmiXapiLrsType::USER_IDENT_IL_UUID_USER_ID
        );
        $op->setInfo($DIC->language()->txt('conf_user_ident_il_uuid_user_id_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_user_ident_il_uuid_login'),
            ilCmiXapiLrsType::USER_IDENT_IL_UUID_LOGIN
        );
        $op->setInfo($DIC->language()->txt('conf_user_ident_il_uuid_login_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_user_ident_il_uuid_ext_account'),
            ilCmiXapiLrsType::USER_IDENT_IL_UUID_EXT_ACCOUNT
        );
        $op->setInfo($DIC->language()->txt('conf_user_ident_il_uuid_ext_account_info'));
        $item->addOption($op);
        $op = new ilRadioOption(
            $DIC->language()->txt('conf_user_ident_real_email'),
            ilCmiXapiLrsType::USER_IDENT_REAL_EMAIL
        );
        $op->setInfo($DIC->language()->txt('conf_user_ident_real_email_info'));
        $item->addOption($op);
        $item->setValue($lrsType->getUserIdent());
        $item->setInfo(
            $DIC->language()->txt('conf_user_ident_info') . ' ' . ilCmiXapiUser::getIliasUuid()
        );
        $item->setRequired(false);
        $form->addItem($item);
        
        $item = new ilRadioGroupInputGUI($DIC->language()->txt('conf_user_name'), 'user_name');
        $op = new ilRadioOption($DIC->language()->txt('conf_user_name_none'), ilCmiXapiLrsType::USER_NAME_NONE);
        $op->setInfo($DIC->language()->txt('conf_user_name_none_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_user_name_firstname'), ilCmiXapiLrsType::USER_NAME_FIRSTNAME);
        $op->setInfo($DIC->language()->txt('conf_user_name_firstname_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_user_name_lastname'), ilCmiXapiLrsType::USER_NAME_LASTNAME);
        $op->setInfo($DIC->language()->txt('conf_user_name_lastname_info'));
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_user_name_fullname'), ilCmiXapiLrsType::USER_NAME_FULLNAME);
        $op->setInfo($DIC->language()->txt('conf_user_name_fullname_info'));
        $item->addOption($op);
        $item->setValue($lrsType->getUserName());
        $item->setInfo($DIC->language()->txt('conf_user_name_info'));
        $item->setRequired(false);
        $form->addItem($item);
        
        $item = new ilRadioGroupInputGUI($DIC->language()->txt('conf_privacy_setting_conf'), 'force_privacy_setting');
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_setting_default'), 0);
        $item->addOption($op);
        $op = new ilRadioOption($DIC->language()->txt('conf_privacy_setting_force'), 1);
        $item->addOption($op);
        $item->setValue($lrsType->getForcePrivacySettings());
        $form->addItem($item);
        
        $item = new ilCheckboxInputGUI($DIC->language()->txt('conf_external_lrs'), 'external_lrs');
        $item->setChecked($lrsType->getExternalLrs());
        $item->setInfo($DIC->language()->txt('info_external_lrs'));
        $form->addItem($item);
        
        $sectionHeader = new ilFormSectionHeaderGUI();
        $sectionHeader->setTitle('Hints');
        $form->addItem($sectionHeader);
        
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
    
    protected function saveLrsTypeFormCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $lrsType = $this->initLrsType();
        
        $form = $this->buildLrsTypeForm($lrsType);
        
        if (!$form->checkInput()) {
            return $this->showLrsTypeFormCmd($form);
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
        $lrsType->setUserIdent($form->getInput("user_ident"));
        $lrsType->setUserName($form->getInput("user_name"));
        $lrsType->setPrivacyCommentDefault($form->getInput("privacy_comment_default"));
        $lrsType->setRemarks($form->getInput("remarks"));
        
        $oldBypassProxyEnabled = $lrsType->isBypassProxyEnabled();
        $newBypassProxyEnabled = $form->getInput("cronjob_neccessary");
        $lrsType->setBypassProxyEnabled((bool) $newBypassProxyEnabled);
        if ($newBypassProxyEnabled && $newBypassProxyEnabled != $oldBypassProxyEnabled) {
            ilObjCmiXapi::updateByPassProxyFromLrsType($lrsType);
        }
        
        $lrsType->setForcePrivacySettings((bool) $form->getInput("force_privacy_setting"));
        if ($lrsType->getForcePrivacySettings()) {
            ilObjCmiXapi::updatePrivacySettingsFromLrsType($lrsType);
        }
        
        $lrsType->save();
        
        $DIC->ctrl()->redirect($this, self::CMD_SHOW_LRS_TYPES_LIST);
    }
}
