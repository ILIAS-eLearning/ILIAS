<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilCmiXapiSettingsGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 *
 * @ilCtrl_Calls ilCmiXapiSettingsGUI: ilCertificateGUI
 */
class ilCmiXapiSettingsGUI
{
    const CMD_SHOW = 'show';
    const CMD_DELIVER_CERTIFICATE = 'deliverCertificate';
    
    const CMD_SAVE = 'save';
    
    const DEFAULT_CMD = self::CMD_SHOW;
    
    const SUBTAB_ID_SETTINGS = 'settings';
    const SUBTAB_ID_CERTIFICATE = 'certificate';
    
    /**
     * @var ilObjCmiXapi
     */
    protected $object;
    
    /**
     * @param ilObjCmiXapi $object
     */
    public function __construct(ilObjCmiXapi $object)
    {
        $this->object = $object;
    }
    
    public function initSubtabs()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->tabs()->addSubTab(
            self::SUBTAB_ID_SETTINGS,
            $DIC->language()->txt(self::SUBTAB_ID_SETTINGS),
            $DIC->ctrl()->getLinkTarget($this, self::CMD_SHOW)
        );

        $validator = new ilCertificateActiveValidator();

        if ($validator->validate()) {
            $DIC->tabs()->addSubTab(
                self::SUBTAB_ID_CERTIFICATE,
                $DIC->language()->txt(self::SUBTAB_ID_CERTIFICATE),
                $DIC->ctrl()->getLinkTargetByClass(ilCertificateGUI::class, 'certificateEditor')
            );
        }
    }
    
    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $this->initSubtabs();
        
        switch ($DIC->ctrl()->getNextClass()) {
            case strtolower(ilCertificateGUI::class):

                $validator = new ilCertificateActiveValidator();

                if (!$validator->validate()) {
                    throw new ilCmiXapiException('access denied!');
                }
                
                $DIC->tabs()->activateSubTab(self::SUBTAB_ID_CERTIFICATE);

                $guiFactory = new ilCertificateGUIFactory();
                $gui = $guiFactory->create($this->object);

                $DIC->ctrl()->forwardCommand($gui);
                
                break;
                
            default:
                $command = $DIC->ctrl()->getCmd(self::DEFAULT_CMD) . 'Cmd';
                $this->{$command}();
        }
    }
    
    protected function saveCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $form = $this->buildForm();
        
        if ($form->checkInput()) {
            $this->saveSettings($form);
            
            ilUtil::sendSuccess($DIC->language()->txt('msg_obj_modified'), true);
            $DIC->ctrl()->redirect($this, self::CMD_SHOW);
        }
        
        $this->showCmd($form);
    }
    
    protected function showCmd(ilPropertyFormGUI $form = null)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->tabs()->activateSubTab(self::SUBTAB_ID_SETTINGS);
        
        $form = $this->buildForm();
        
        $DIC->ui()->mainTemplate()->setContent($form->getHTML());
    }
    
    protected function buildForm()
    {
        global $DIC;
        /* @var \ILIAS\DI\Container $DIC */
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($DIC->ctrl()->getFormAction($this));
        
        $ne = new ilNonEditableValueGUI($DIC->language()->txt('type'), "");
        $ne->setValue($DIC->language()->txt('type_' . $this->object->getContentType()));
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI($DIC->language()->txt('cmix_lrs_type'), "");
        $ne->setValue($this->object->getLrsType()->getTitle());
        $form->addItem($ne);
        
        $item = new ilTextInputGUI($DIC->language()->txt('title'), 'title');
        $item->setSize(40);
        $item->setMaxLength(128);
        $item->setRequired(true);
        $item->setInfo($DIC->language()->txt('title_info'));
        $item->setValue($this->object->getTitle());
        $form->addItem($item);
        
        $item = new ilTextAreaInputGUI($DIC->language()->txt('description'), 'description');
        $item->setInfo($DIC->language()->txt('description_info'));
        $item->setRows(2);
        $item->setCols(80);
        $item->setValue($this->object->getDescription());
        $form->addItem($item);
        
        $item = new ilTextInputGUI($DIC->language()->txt('activity_id'), 'activity_id');
        $item->setRequired(true);
        $item->setSize(40);
        $item->setMaxLength(128);
        // $item->setRequired(true);
        $item->setInfo($DIC->language()->txt('activity_id_info'));
        $item->setValue($this->object->getActivityId());
        $form->addItem($item);
        
        $item = new ilCheckboxInputGUI($DIC->language()->txt('online'), 'online');
        $item->setInfo($DIC->language()->txt("online_info"));
        $item->setValue("1");
        if (!$this->object->getOfflineStatus()) {
            $item->setChecked(true);
        }
        $form->addItem($item);
        
        if (!$this->object->isSourceTypeExternal()) {
            $item = new ilFormSectionHeaderGUI();
            $item->setTitle($DIC->language()->txt("launch_options"));
            $form->addItem($item);
            
            if ($this->object->isSourceTypeRemote()) {
                $item = new ilTextInputGUI($DIC->language()->txt('launch_url'), 'launch_url');
                $item->setSize(40);
                $item->setMaxLength(128);
                $item->setRequired(true);
                $item->setInfo($DIC->language()->txt('launch_url_info'));
                $item->setValue($this->object->getLaunchUrl());
                $form->addItem($item);
            }
            
            if ($this->object->getContentType() != ilObjCmiXapi::CONT_TYPE_CMI5) {
                $item = new ilCheckboxInputGUI($DIC->language()->txt('use_fetch'), 'use_fetch');
                $item->setInfo($DIC->language()->txt("use_fetch_info"));
                $item->setValue("1");
                
                if ($this->object->isAuthFetchUrlEnabled()) {
                    $item->setChecked(true);
                }
                $form->addItem($item);
            }
            
            $display = new ilRadioGroupInputGUI($DIC->language()->txt('launch_options'), 'display');
            $display->setRequired(true);
            $display->setValue($this->object->getLaunchMethod());
            $optOwnWindow = new ilRadioOption($DIC->language()->txt('conf_own_window'), ilObjCmiXapi::LAUNCH_METHOD_OWN_WIN);
            $optOwnWindow->setInfo($DIC->language()->txt('conf_own_window_info'));
            $display->addOption($optOwnWindow);
            $optAnyWindow = new ilRadioOption($DIC->language()->txt('conf_new_window'), ilObjCmiXapi::LAUNCH_METHOD_NEW_WIN);
            $optAnyWindow->setInfo($DIC->language()->txt('conf_new_window_info'));
            $display->addOption($optAnyWindow);
            $form->addItem($display);
            
            $launchMode = new ilRadioGroupInputGUI($DIC->language()->txt('conf_launch_mode'), 'launch_mode');
            $launchMode->setRequired(true);
            $launchMode->setValue($this->object->getLaunchMode());
            $optNormal = new ilRadioOption($DIC->language()->txt('conf_launch_mode_normal'), ilObjCmiXapi::LAUNCH_MODE_NORMAL);
            $optNormal->setInfo($DIC->language()->txt('conf_launch_mode_normal_info'));
            $launchMode->addOption($optNormal);
            $optBrowse = new ilRadioOption($DIC->language()->txt('conf_launch_mode_browse'), ilObjCmiXapi::LAUNCH_MODE_BROWSE);
            $optBrowse->setInfo($DIC->language()->txt('conf_launch_mode_browse_info'));
            $launchMode->addOption($optBrowse);
            $optReview = new ilRadioOption($DIC->language()->txt('conf_launch_mode_review'), ilObjCmiXapi::LAUNCH_MODE_REVIEW);
            $optReview->setInfo($DIC->language()->txt('conf_launch_mode_review_info'));
            $launchMode->addOption($optReview);
            $form->addItem($launchMode);
        }
            
        $lpDeterioration = new ilCheckboxInputGUI($DIC->language()->txt('conf_keep_lp'), 'avoid_lp_deterioration');
        $lpDeterioration->setInfo($DIC->language()->txt('conf_keep_lp_info'));
        if ($this->object->isKeepLpStatusEnabled()) {
            $lpDeterioration->setChecked(true);
        }
        if (!$this->object->isSourceTypeExternal()) {
            $optNormal->addSubItem($lpDeterioration);
        } else {
            $form->addItem($lpDeterioration);
        }

        if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
            $switchMode = new ilCheckboxInputGUI($DIC->language()->txt('conf_switch_to_review'), 'switch_to_review');
            $switchMode->setInfo($DIC->language()->txt("conf_switch_to_review_info"));
            if ($this->object->isSwitchToReviewEnabled()) {
                $switchMode->setChecked(true);
            }
            $optNormal->addSubItem($switchMode);
            
            $masteryScore = new ilNumberInputGUI($DIC->language()->txt('conf_mastery_score'), 'mastery_score');
            $masteryScore->setInfo($DIC->language()->txt('conf_mastery_score_info'));
            $masteryScore->setSuffix('%');
            $masteryScore->allowDecimals(true);
            $masteryScore->setDecimals(2);
            $masteryScore->setMinvalueShouldBeGreater(false);
            $masteryScore->setMinValue(0);
            $masteryScore->setMaxvalueShouldBeLess(false);
            $masteryScore->setMaxValue(100);
            $masteryScore->setSize(4);
            if (empty($this->object->getMasteryScore())) {
                $this->object->setMasteryScorePercent(ilObjCmiXapi::LMS_MASTERY_SCORE);
            }
            $masteryScore->setValue($this->object->getMasteryScorePercent());
            $optNormal->addSubItem($masteryScore);
        }
        
        if (!$this->object->isSourceTypeExternal()) {
            if ($this->object->getContentType() != ilObjCmiXapi::CONT_TYPE_CMI5) {
                $sectionHeader = new ilFormSectionHeaderGUI();
                $sectionHeader->setTitle($DIC->language()->txt('sect_learning_progress_options'));
                $form->addItem($sectionHeader);
                $bypassProxy = new ilRadioGroupInputGUI($DIC->language()->txt('conf_bypass_proxy'), 'bypass_proxy');
                $bypassProxy->setInfo($DIC->language()->txt('conf_bypass_proxy_info'));
                $bypassProxy->setValue($this->object->isBypassProxyEnabled());
                $opt1 = new ilRadioOption($DIC->language()->txt('conf_bypass_proxy_disabled'), 0);
                $bypassProxy->addOption($opt1);
                $opt2 = new ilRadioOption($DIC->language()->txt('conf_bypass_proxy_enabled'), 1);
                $bypassProxy->addOption($opt2);
                $form->addItem($bypassProxy);
                if ($this->object->getLrsType()->isBypassProxyEnabled()) {
                    $bypassProxy->setDisabled(true);
                }
            }

            $item = new ilFormSectionHeaderGUI();
            $item->setTitle($DIC->language()->txt("privacy_options"));
            $form->addItem($item);
            
            $userIdent = new ilRadioGroupInputGUI($DIC->language()->txt('conf_privacy_ident'), 'privacy_ident');
            $op = new ilRadioOption(
                $DIC->language()->txt('conf_privacy_ident_il_uuid_user_id'),
                ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_USER_ID
            );
            $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_user_id_info'));
            $userIdent->addOption($op);
            $op = new ilRadioOption(
                $DIC->language()->txt('conf_privacy_ident_il_uuid_login'),
                ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_LOGIN
            );
            $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_login_info'));
            $userIdent->addOption($op);
            $op = new ilRadioOption(
                $DIC->language()->txt('conf_privacy_ident_il_uuid_ext_account'),
                ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT
            );
            $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_ext_account_info'));
            $userIdent->addOption($op);
            $op = new ilRadioOption(
                $DIC->language()->txt('conf_privacy_ident_il_uuid_sha256'),
                ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_SHA256
            );
            $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_sha256_info'));
            $userIdent->addOption($op);
            $op = new ilRadioOption(
                $DIC->language()->txt('conf_privacy_ident_il_uuid_sha256url'),
                ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_SHA256URL
            );
            $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_sha256url_info'));
            $userIdent->addOption($op);
            $op = new ilRadioOption(
                $DIC->language()->txt('conf_privacy_ident_il_uuid_random'),
                ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_RANDOM
            );
            $op->setInfo($DIC->language()->txt('conf_privacy_ident_il_uuid_random_info'));
            $userIdent->addOption($op);
            $op = new ilRadioOption(
                $DIC->language()->txt('conf_privacy_ident_real_email'),
                ilCmiXapiLrsType::PRIVACY_IDENT_REAL_EMAIL
            );
            $op->setInfo($DIC->language()->txt('conf_privacy_ident_real_email_info'));
            $userIdent->addOption($op);
            $userIdent->setValue($this->object->getPrivacyIdent());
            $userIdent->setInfo(
                $DIC->language()->txt('conf_privacy_ident_info') . ' ' . ilCmiXapiUser::getIliasUuid()
            );
            $userIdent->setRequired(false);
            $form->addItem($userIdent);
            
            $userName = new ilRadioGroupInputGUI($DIC->language()->txt('conf_privacy_name'), 'privacy_name');
            $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_none'), ilCmiXapiLrsType::PRIVACY_NAME_NONE);
            $op->setInfo($DIC->language()->txt('conf_privacy_name_none_info'));
            $userName->addOption($op);
            $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_firstname'), ilCmiXapiLrsType::PRIVACY_NAME_FIRSTNAME);
            $op->setInfo($DIC->language()->txt('conf_privacy_name_firstname_info'));
            $userName->addOption($op);
            $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_lastname'), ilCmiXapiLrsType::PRIVACY_NAME_LASTNAME);
            $op->setInfo($DIC->language()->txt('conf_privacy_name_lastname_info'));
            $userName->addOption($op);
            $op = new ilRadioOption($DIC->language()->txt('conf_privacy_name_fullname'), ilCmiXapiLrsType::PRIVACY_NAME_FULLNAME);
            $op->setInfo($DIC->language()->txt('conf_privacy_name_fullname_info'));
            $userName->addOption($op);
            $userName->setValue($this->object->getPrivacyName());
            $userName->setInfo($DIC->language()->txt('conf_privacy_name_info'));
            $userName->setRequired(false);
            $form->addItem($userName);

            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $userIdent->setDisabled(true);
                $userName->setDisabled(true);
            }

            $item = new ilCheckboxInputGUI($DIC->language()->txt('only_moveon_label'), 'only_moveon');
            $item->setInfo($DIC->language()->txt('only_moveon_info'));
            $item->setChecked($this->object->getOnlyMoveon());

            $subitem = new ilCheckboxInputGUI($DIC->language()->txt('achieved_label'), 'achieved');
            $subitem->setInfo($DIC->language()->txt('achieved_info'));
            $subitem->setChecked($this->object->getAchieved());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($DIC->language()->txt('answered_label'), 'answered');
            $subitem->setInfo($DIC->language()->txt('answered_info'));
            $subitem->setChecked($this->object->getAnswered());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($DIC->language()->txt('completed_label'), 'completed');
            $subitem->setInfo($DIC->language()->txt('completed_info'));
            $subitem->setChecked($this->object->getCompleted());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($DIC->language()->txt('failed_label'), 'failed');
            $subitem->setInfo($DIC->language()->txt('failed_info'));
            $subitem->setChecked($this->object->getFailed());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($DIC->language()->txt('initialized_label'), 'initialized');
            $subitem->setInfo($DIC->language()->txt('initialized_info'));
            $subitem->setChecked($this->object->getInitialized());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($DIC->language()->txt('passed_label'), 'passed');
            $subitem->setInfo($DIC->language()->txt('passed_info'));
            $subitem->setChecked($this->object->getPassed());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($DIC->language()->txt('progressed_label'), 'progressed');
            $subitem->setInfo($DIC->language()->txt('progressed_info'));
            $subitem->setChecked($this->object->getProgressed());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);
            if ($this->object->getContentType() != ilObjCmiXapi::CONT_TYPE_CMI5) {
                $subitem = new ilCheckboxInputGUI($DIC->language()->txt('satisfied_label'), 'satisfied');
                $subitem->setInfo($DIC->language()->txt('satisfied_info'));
                $subitem->setChecked($this->object->getSatisfied());
                if ($this->object->getLrsType()->getForcePrivacySettings()) {
                    $subitem->setDisabled(true);
                }
                $item->addSubItem($subitem);

                $subitem = new ilCheckboxInputGUI($DIC->language()->txt('terminated_label'), 'terminated');
                $subitem->setInfo($DIC->language()->txt('terminated_info'));
                $subitem->setChecked($this->object->getTerminated());
                if ($this->object->getLrsType()->getForcePrivacySettings()) {
                    $subitem->setDisabled(true);
                }
                $item->addSubItem($subitem);
            }
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $item->setDisabled(true);
            }
            $form->addItem($item);

            $item = new ilCheckboxInputGUI($DIC->language()->txt('hide_data_label'), 'hide_data');
            $item->setInfo($DIC->language()->txt('hide_data_info'));
            $item->setChecked($this->object->getHideData());

            $subitem = new ilCheckboxInputGUI($DIC->language()->txt('timestamp_label'), 'timestamp');
            $subitem->setInfo($DIC->language()->txt('timestamp_info'));
            $subitem->setChecked($this->object->getTimestamp());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($DIC->language()->txt('duration_label'), 'duration');
            $subitem->setInfo($DIC->language()->txt('duration_info'));
            $subitem->setChecked($this->object->getDuration());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $item->setDisabled(true);
            }
            $form->addItem($item);

            $item = new ilCheckboxInputGUI($DIC->language()->txt('no_substatements_label'), 'no_substatements');
            $item->setInfo($DIC->language()->txt('no_substatements_info'));
            $item->setChecked($this->object->getNoSubstatements());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $item->setDisabled(true);
            }
            $form->addItem($item);
        }
        
        $item = new ilFormSectionHeaderGUI();
        $item->setTitle($DIC->language()->txt("log_options"));
        $form->addItem($item);
        
        $item = new ilCheckboxInputGUI($DIC->language()->txt('show_debug'), 'show_debug');
        $item->setInfo($DIC->language()->txt("show_debug_info"));
        $item->setValue("1");
        if ($this->object->isStatementsReportEnabled()) {
            $item->setChecked(true);
        }
        $form->addItem($item);
        
        $highscore = new ilCheckboxInputGUI($DIC->language()->txt("highscore_enabled"), "highscore_enabled");
        $highscore->setValue(1);
        $highscore->setChecked($this->object->getHighscoreEnabled());
        $highscore->setInfo($DIC->language()->txt("highscore_description"));
        $form->addItem($highscore);
        $highscore_tables = new ilRadioGroupInputGUI($DIC->language()->txt('highscore_mode'), 'highscore_mode');
        $highscore_tables->setRequired(true);
        $highscore_tables->setValue($this->object->getHighscoreMode());
        $highscore_table_own = new ilRadioOption($DIC->language()->txt('highscore_own_table'), ilObjCmiXapi::HIGHSCORE_SHOW_OWN_TABLE);
        $highscore_table_own->setInfo($DIC->language()->txt('highscore_own_table_description'));
        $highscore_tables->addOption($highscore_table_own);
        $highscore_table_other = new ilRadioOption($DIC->language()->txt('highscore_top_table'), ilObjCmiXapi::HIGHSCORE_SHOW_TOP_TABLE);
        $highscore_table_other->setInfo($DIC->language()->txt('highscore_top_table_description'));
        $highscore_tables->addOption($highscore_table_other);
        $highscore_table_other = new ilRadioOption($DIC->language()->txt('highscore_all_tables'), ilObjCmiXapi::HIGHSCORE_SHOW_ALL_TABLES);
        $highscore_table_other->setInfo($DIC->language()->txt('highscore_all_tables_description'));
        $highscore_tables->addOption($highscore_table_other);
        $highscore->addSubItem($highscore_tables);
        $highscore_top_num = new ilNumberInputGUI($DIC->language()->txt("highscore_top_num"), "highscore_top_num");
        $highscore_top_num->setSize(4);
        $highscore_top_num->setRequired(true);
        $highscore_top_num->setMinValue(1);
        $highscore_top_num->setSuffix($DIC->language()->txt("highscore_top_num_unit"));
        $highscore_top_num->setValue($this->object->getHighscoreTopNum(null));
        $highscore_top_num->setInfo($DIC->language()->txt("highscore_top_num_description"));
        $highscore->addSubItem($highscore_top_num);
        $highscore_achieved_ts = new ilCheckboxInputGUI($DIC->language()->txt("highscore_achieved_ts"), "highscore_achieved_ts");
        $highscore_achieved_ts->setValue(1);
        $highscore_achieved_ts->setChecked($this->object->getHighscoreAchievedTS());
        $highscore_achieved_ts->setInfo($DIC->language()->txt("highscore_achieved_ts_description"));
        $highscore->addSubItem($highscore_achieved_ts);
        $highscore_percentage = new ilCheckboxInputGUI($DIC->language()->txt("highscore_percentage"), "highscore_percentage");
        $highscore_percentage->setValue(1);
        $highscore_percentage->setChecked($this->object->getHighscorePercentage());
        $highscore_percentage->setInfo($DIC->language()->txt("highscore_percentage_description"));
        $highscore->addSubItem($highscore_percentage);
        $highscore_wtime = new ilCheckboxInputGUI($DIC->language()->txt("highscore_wtime"), "highscore_wtime");
        $highscore_wtime->setValue(1);
        $highscore_wtime->setChecked($this->object->getHighscoreWTime());
        $highscore_wtime->setInfo($DIC->language()->txt("highscore_wtime_description"));
        $highscore->addSubItem($highscore_wtime);
        
        
        $form->setTitle($DIC->language()->txt('settings'));
        $form->addCommandButton(self::CMD_SAVE, $DIC->language()->txt("save"));
        $form->addCommandButton(self::CMD_SHOW, $DIC->language()->txt("cancel"));
        
        return $form;
    }
    
    protected function saveSettings(ilPropertyFormGUI $form)
    {
        $this->object->setTitle($form->getInput('title'));
        $this->object->setDescription($form->getInput('description'));
        
        $this->object->setActivityId($form->getInput('activity_id'));
        $this->object->setOfflineStatus(!(bool) $form->getInput('online'));
        
        if (!$this->object->isSourceTypeExternal()) {
            $this->object->setLaunchMethod($form->getInput('display'));
            
            $this->object->setLaunchMode($form->getInput('launch_mode'));
            
            if ($this->object->getLaunchMode() == ilObjCmiXapi::LAUNCH_MODE_NORMAL) {
                if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
                    $this->object->setMasteryScorePercent($form->getInput('mastery_score'));
                }
                $this->object->setKeepLpStatusEnabled((bool) $form->getInput('avoid_lp_deterioration'));
                $this->object->setSwitchToReviewEnabled((bool) $form->getInput('switch_to_review'));
            } else {
                $this->object->setKeepLpStatusEnabled(true);
                $this->object->setSwitchToReviewEnabled(false);
            }

            if ($this->object->isSourceTypeRemote()) {
                $this->object->setLaunchUrl($form->getInput('launch_url'));
            }
            
            if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
                $this->object->setAuthFetchUrlEnabled(true);
            } else {
                $this->object->setAuthFetchUrlEnabled((bool) $form->getInput('use_fetch'));
            }

            if (!$this->object->getLrsType()->isBypassProxyEnabled()) {
                if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
                    $this->object->setBypassProxyEnabled(false);
                } else {
                    $this->object->setBypassProxyEnabled((bool) $form->getInput('bypass_proxy'));
                }
            }
            
            if (!$this->object->getLrsType()->getForcePrivacySettings()) {
                $this->object->setPrivacyIdent($form->getInput('privacy_ident'));
                $this->object->setPrivacyName($form->getInput('privacy_name'));
                $this->object->setOnlyMoveon((bool) $form->getInput("only_moveon"));
                $this->object->setAchieved((bool) $form->getInput("achieved"));
                $this->object->setAnswered((bool) $form->getInput("answered"));
                $this->object->setCompleted((bool) $form->getInput("completed"));
                $this->object->setFailed((bool) $form->getInput("failed"));
                $this->object->setInitialized((bool) $form->getInput("initialized"));
                $this->object->setPassed((bool) $form->getInput("passed"));
                $this->object->setProgressed((bool) $form->getInput("progressed"));
                if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
                    $this->object->setSatisfied(true);
                    $this->object->setTerminated(true);
                } else {
                    $this->object->setSatisfied((bool) $form->getInput("satisfied"));
                    $this->object->setTerminated((bool) $form->getInput("terminated"));
                }
                $this->object->setHideData((bool) $form->getInput("hide_data"));
                $this->object->setTimestamp((bool) $form->getInput("timestamp"));
                $this->object->setDuration((bool) $form->getInput("duration"));
                $this->object->setNoSubstatements((bool) $form->getInput("no_substatements"));
            }
        } else { //SourceTypeExternal
            $this->object->setBypassProxyEnabled(true);
            $this->object->setKeepLpStatusEnabled((bool) $form->getInput('avoid_lp_deterioration'));
        }
        
        $this->object->setStatementsReportEnabled((bool) $form->getInput('show_debug'));
        
        $this->object->setHighscoreEnabled((bool) $form->getInput('highscore_enabled'));
        if ($this->object->getHighscoreEnabled()) {
            // highscore settings
            $this->object->setHighscoreEnabled((bool) $form->getInput('highscore_enabled'));
            $this->object->setHighscoreAchievedTS((bool) $form->getInput('highscore_achieved_ts'));
            $this->object->setHighscorePercentage((bool) $form->getInput('highscore_percentage'));
            $this->object->setHighscoreWTime((bool) $form->getInput('highscore_wtime'));
            $this->object->setHighscoreMode((int) $form->getInput('highscore_mode'));
            $this->object->setHighscoreTopNum((int) $form->getInput('highscore_top_num'));
        }
        
        $this->object->update();
    }
    
    protected function deliverCertificateCmd()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $validator = new ilCertificateDownloadValidator();

        if (!$validator->isCertificateDownloadable((int) $DIC->user()->getId(), (int) $this->object->getId())) {
            ilUtil::sendFailure($DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilObjCmiXapiGUI::class, ilObjCmiXapiGUI::CMD_INFO_SCREEN);
        }

        $repository = new ilUserCertificateRepository();

        $certLogger = $DIC->logger()->cert();
        $pdfGenerator = new ilPdfGenerator($repository, $certLogger);

        $pdfAction = new ilCertificatePdfAction(
            $certLogger,
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $DIC->language()->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf((int) $DIC->user()->getId(), (int) $this->object->getId());
    }
}
