<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    protected ilObjCmiXapi $object;
    private \ilGlobalTemplateInterface $main_tpl;
    private \ILIAS\DI\Container $dic;
    private ilLanguage $language;
    
    public function __construct(ilObjCmiXapi $object)
    {
        global $DIC;
        $this->dic = $DIC;
        $this->language = $DIC->language();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->object = $object;
    }
    
    public function initSubtabs() : void
    {
        $this->dic->tabs()->addSubTab(
            self::SUBTAB_ID_SETTINGS,
            $this->language->txt(self::SUBTAB_ID_SETTINGS),
            $this->dic->ctrl()->getLinkTarget($this, self::CMD_SHOW)
        );

        $validator = new ilCertificateActiveValidator();

        if ($validator->validate()) {
            $this->dic->tabs()->addSubTab(
                self::SUBTAB_ID_CERTIFICATE,
                $this->language->txt(self::SUBTAB_ID_CERTIFICATE),
                $this->dic->ctrl()->getLinkTargetByClass(ilCertificateGUI::class, 'certificateEditor')
            );
        }
    }
    
    public function executeCommand() : void
    {
        $this->initSubtabs();
        
        switch ($this->dic->ctrl()->getNextClass()) {
            case strtolower(ilCertificateGUI::class):

                $validator = new ilCertificateActiveValidator();

                if (!$validator->validate()) {
                    throw new ilCmiXapiException('access denied!');
                }
                
                $this->dic->tabs()->activateSubTab(self::SUBTAB_ID_CERTIFICATE);

                $guiFactory = new ilCertificateGUIFactory();
                $gui = $guiFactory->create($this->object);

                $this->dic->ctrl()->forwardCommand($gui);
                
                break;
                
            default:
                $command = $this->dic->ctrl()->getCmd(self::DEFAULT_CMD) . 'Cmd';
                $this->{$command}();
        }
    }
    
    protected function saveCmd() : void
    {
        $form = $this->buildForm();
        
        if ($form->checkInput()) {
            $this->saveSettings($form);
            
            $this->main_tpl->setOnScreenMessage('success', $this->language->txt('msg_obj_modified'), true);
            $this->dic->ctrl()->redirect($this, self::CMD_SHOW);
        }
        
        $this->showCmd($form);
    }
    
    protected function showCmd(ilPropertyFormGUI $form = null) : void
    {
        $this->dic->tabs()->activateSubTab(self::SUBTAB_ID_SETTINGS);
        
        $form = $this->buildForm();
        
        $this->dic->ui()->mainTemplate()->setContent($form->getHTML());
    }
    
    protected function buildForm() : \ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->dic->ctrl()->getFormAction($this));
        
        $ne = new ilNonEditableValueGUI($this->language->txt('type'), "");
        $ne->setValue($this->language->txt('type_' . $this->object->getContentType()));
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI($this->language->txt('cmix_lrs_type'), "");
        $ne->setValue($this->object->getLrsType()->getTitle());
        $form->addItem($ne);
        
        $item = new ilTextInputGUI($this->language->txt('title'), 'title');
        $item->setSize(40);
        $item->setMaxLength(128);
        $item->setRequired(true);
        $item->setInfo($this->language->txt('title_info'));
        $item->setValue($this->object->getTitle());
        $form->addItem($item);
        
        $item = new ilTextAreaInputGUI($this->language->txt('description'), 'description');
        $item->setInfo($this->language->txt('description_info'));
        $item->setRows(2);
        $item->setCols(80);
        $item->setValue($this->object->getDescription());
        $form->addItem($item);
        
        $item = new ilTextInputGUI($this->language->txt('activity_id'), 'activity_id');
        $item->setRequired(true);
        $item->setSize(40);
        $item->setMaxLength(128);
        // $item->setRequired(true);
        $item->setInfo($this->language->txt('activity_id_info'));
        $item->setValue($this->object->getActivityId());
        $form->addItem($item);
        
        $item = new ilCheckboxInputGUI($this->language->txt('online'), 'online');
        $item->setInfo($this->language->txt("online_info"));
        $item->setValue("1");
        if (!$this->object->getOfflineStatus()) {
            $item->setChecked(true);
        }
        $form->addItem($item);

        $lpDeterioration = new ilCheckboxInputGUI($this->language->txt('conf_keep_lp'), 'avoid_lp_deterioration');
        $lpDeterioration->setInfo($this->language->txt('conf_keep_lp_info'));
        if ($this->object->isKeepLpStatusEnabled()) {
            $lpDeterioration->setChecked(true);
        }

        if (!$this->object->isSourceTypeExternal()) {
            $item = new ilFormSectionHeaderGUI();
            $item->setTitle($this->language->txt("launch_options"));
            $form->addItem($item);
            
            if ($this->object->isSourceTypeRemote()) {
                $item = new ilTextInputGUI($this->language->txt('launch_url'), 'launch_url');
                $item->setSize(40);
                $item->setMaxLength(128);
                $item->setRequired(true);
                $item->setInfo($this->language->txt('launch_url_info'));
                $item->setValue($this->object->getLaunchUrl());
                $form->addItem($item);
            }
            
            if ($this->object->getContentType() != ilObjCmiXapi::CONT_TYPE_CMI5) {
                $item = new ilCheckboxInputGUI($this->language->txt('use_fetch'), 'use_fetch');
                $item->setInfo($this->language->txt("use_fetch_info"));
                $item->setValue("1");
                
                if ($this->object->isAuthFetchUrlEnabled()) {
                    $item->setChecked(true);
                }
                $form->addItem($item);
            }
            
            $display = new ilRadioGroupInputGUI($this->language->txt('launch_options'), 'display');
            $display->setRequired(true);
            $display->setValue($this->object->getLaunchMethod());
            $optOwnWindow = new ilRadioOption($this->language->txt('conf_own_window'), ilObjCmiXapi::LAUNCH_METHOD_OWN_WIN);
            $optOwnWindow->setInfo($this->language->txt('conf_own_window_info'));
            $display->addOption($optOwnWindow);
            $optAnyWindow = new ilRadioOption($this->language->txt('conf_new_window'), ilObjCmiXapi::LAUNCH_METHOD_NEW_WIN);
            $optAnyWindow->setInfo($this->language->txt('conf_new_window_info'));
            $display->addOption($optAnyWindow);
            $form->addItem($display);
            
            $launchMode = new ilRadioGroupInputGUI($this->language->txt('conf_launch_mode'), 'launch_mode');
            $launchMode->setRequired(true);
            $launchMode->setValue($this->object->getLaunchMode());
            $optNormal = new ilRadioOption($this->language->txt('conf_launch_mode_normal'), ilObjCmiXapi::LAUNCH_MODE_NORMAL);
            $optNormal->setInfo($this->language->txt('conf_launch_mode_normal_info'));

            $optNormal->addSubItem($lpDeterioration);

            $launchMode->addOption($optNormal);
            $optBrowse = new ilRadioOption($this->language->txt('conf_launch_mode_browse'), ilObjCmiXapi::LAUNCH_MODE_BROWSE);
            $optBrowse->setInfo($this->language->txt('conf_launch_mode_browse_info'));
            $launchMode->addOption($optBrowse);
            $optReview = new ilRadioOption($this->language->txt('conf_launch_mode_review'), ilObjCmiXapi::LAUNCH_MODE_REVIEW);
            $optReview->setInfo($this->language->txt('conf_launch_mode_review_info'));
            $launchMode->addOption($optReview);
            $form->addItem($launchMode);
        } else {
            $form->addItem($lpDeterioration);
        }

        if ($this->object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5) {
            $switchMode = new ilCheckboxInputGUI($this->language->txt('conf_switch_to_review'), 'switch_to_review');
            $switchMode->setInfo($this->language->txt("conf_switch_to_review_info"));
            if ($this->object->isSwitchToReviewEnabled()) {
                $switchMode->setChecked(true);
            }
            // optNormal not undefined because CONT_TYPE_CMI5 is subtype
            $optNormal->addSubItem($switchMode);
            
            $masteryScore = new ilNumberInputGUI($this->language->txt('conf_mastery_score'), 'mastery_score');
            $masteryScore->setInfo($this->language->txt('conf_mastery_score_info'));
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
            $masteryScore->setValue((string) $this->object->getMasteryScorePercent());
            $optNormal->addSubItem($masteryScore);
        }
        
        if (!$this->object->isSourceTypeExternal()) {
            if ($this->object->getContentType() != ilObjCmiXapi::CONT_TYPE_CMI5) {
                $sectionHeader = new ilFormSectionHeaderGUI();
                $sectionHeader->setTitle($this->language->txt('sect_learning_progress_options'));
                $form->addItem($sectionHeader);
                $bypassProxy = new ilRadioGroupInputGUI($this->language->txt('conf_bypass_proxy'), 'bypass_proxy');
                $bypassProxy->setInfo($this->language->txt('conf_bypass_proxy_info'));
                $bypassProxy->setValue((string) $this->object->isBypassProxyEnabled());
                $opt1 = new ilRadioOption($this->language->txt('conf_bypass_proxy_disabled'), "0");
                $bypassProxy->addOption($opt1);
                $opt2 = new ilRadioOption($this->language->txt('conf_bypass_proxy_enabled'), "1");
                $bypassProxy->addOption($opt2);
                $bypassProxy->setValue((string) ((int) $this->object->getLrsType()->isBypassProxyEnabled()));
                $form->addItem($bypassProxy);
                if ($this->object->getLrsType()->isBypassProxyEnabled()) {
                    $bypassProxy->setDisabled(true);
                }
            }

            $item = new ilFormSectionHeaderGUI();
            $item->setTitle($this->language->txt("privacy_options"));
            $form->addItem($item);
            
            $userIdent = new ilRadioGroupInputGUI($this->language->txt('conf_privacy_ident'), 'privacy_ident');
            $op = new ilRadioOption(
                $this->language->txt('conf_privacy_ident_il_uuid_user_id'),
                (string) ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_USER_ID
            );
            $op->setInfo($this->language->txt('conf_privacy_ident_il_uuid_user_id_info'));
            $userIdent->addOption($op);
            $op = new ilRadioOption(
                $this->language->txt('conf_privacy_ident_il_uuid_login'),
                (string) ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_LOGIN
            );
            $op->setInfo($this->language->txt('conf_privacy_ident_il_uuid_login_info'));
            $userIdent->addOption($op);
            $op = new ilRadioOption(
                $this->language->txt('conf_privacy_ident_il_uuid_ext_account'),
                (string) ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_EXT_ACCOUNT
            );
            $op->setInfo($this->language->txt('conf_privacy_ident_il_uuid_ext_account_info'));
            $userIdent->addOption($op);
            $op = new ilRadioOption(
                $this->language->txt('conf_privacy_ident_il_uuid_random'),
                (string) ilCmiXapiLrsType::PRIVACY_IDENT_IL_UUID_RANDOM
            );
            $op->setInfo($this->language->txt('conf_privacy_ident_il_uuid_random_info'));
            $userIdent->addOption($op);
            $op = new ilRadioOption(
                $this->language->txt('conf_privacy_ident_real_email'),
                (string) ilCmiXapiLrsType::PRIVACY_IDENT_REAL_EMAIL
            );
            $op->setInfo($this->language->txt('conf_privacy_ident_real_email_info'));
            $userIdent->addOption($op);
            $userIdent->setValue((string) $this->object->getPrivacyIdent());
            $userIdent->setInfo(
                $this->language->txt('conf_privacy_ident_info') . ' ' . ilCmiXapiUser::getIliasUuid()
            );
            $userIdent->setRequired(false);
            $form->addItem($userIdent);
            
            $userName = new ilRadioGroupInputGUI($this->language->txt('conf_privacy_name'), 'privacy_name');
            $op = new ilRadioOption(
                $this->language->txt('conf_privacy_name_none'),
                (string) ilCmiXapiLrsType::PRIVACY_NAME_NONE
            );
            $op->setInfo($this->language->txt('conf_privacy_name_none_info'));
            $userName->addOption($op);
            $op = new ilRadioOption(
                $this->language->txt('conf_privacy_name_firstname'),
                (string) ilCmiXapiLrsType::PRIVACY_NAME_FIRSTNAME
            );
            $op->setInfo($this->language->txt('conf_privacy_name_firstname_info'));
            $userName->addOption($op);
            $op = new ilRadioOption(
                $this->language->txt('conf_privacy_name_lastname'),
                (string) ilCmiXapiLrsType::PRIVACY_NAME_LASTNAME
            );
            $op->setInfo($this->language->txt('conf_privacy_name_lastname_info'));
            $userName->addOption($op);
            $op = new ilRadioOption(
                $this->language->txt('conf_privacy_name_fullname'),
                (string) ilCmiXapiLrsType::PRIVACY_NAME_FULLNAME
            );
            $op->setInfo($this->language->txt('conf_privacy_name_fullname_info'));
            $userName->addOption($op);
            $userName->setValue((string) $this->object->getPrivacyName());
            $userName->setInfo($this->language->txt('conf_privacy_name_info'));
            $userName->setRequired(false);
            $form->addItem($userName);

            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $userIdent->setDisabled(true);
                $userName->setDisabled(true);
            }

            $item = new ilCheckboxInputGUI($this->language->txt('only_moveon_label'), 'only_moveon');
            $item->setInfo($this->language->txt('only_moveon_info'));
            $item->setChecked($this->object->getOnlyMoveon());

            $subitem = new ilCheckboxInputGUI($this->language->txt('achieved_label'), 'achieved');
            $subitem->setInfo($this->language->txt('achieved_info'));
            $subitem->setChecked($this->object->getAchieved());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($this->language->txt('answered_label'), 'answered');
            $subitem->setInfo($this->language->txt('answered_info'));
            $subitem->setChecked($this->object->getAnswered());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($this->language->txt('completed_label'), 'completed');
            $subitem->setInfo($this->language->txt('completed_info'));
            $subitem->setChecked($this->object->getCompleted());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($this->language->txt('failed_label'), 'failed');
            $subitem->setInfo($this->language->txt('failed_info'));
            $subitem->setChecked($this->object->getFailed());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($this->language->txt('initialized_label'), 'initialized');
            $subitem->setInfo($this->language->txt('initialized_info'));
            $subitem->setChecked($this->object->getInitialized());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($this->language->txt('passed_label'), 'passed');
            $subitem->setInfo($this->language->txt('passed_info'));
            $subitem->setChecked($this->object->getPassed());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($this->language->txt('progressed_label'), 'progressed');
            $subitem->setInfo($this->language->txt('progressed_info'));
            $subitem->setChecked($this->object->getProgressed());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);
            if ($this->object->getContentType() != ilObjCmiXapi::CONT_TYPE_CMI5) {
                $subitem = new ilCheckboxInputGUI($this->language->txt('satisfied_label'), 'satisfied');
                $subitem->setInfo($this->language->txt('satisfied_info'));
                $subitem->setChecked($this->object->getSatisfied());
                if ($this->object->getLrsType()->getForcePrivacySettings()) {
                    $subitem->setDisabled(true);
                }
                $item->addSubItem($subitem);

                $subitem = new ilCheckboxInputGUI($this->language->txt('terminated_label'), 'terminated');
                $subitem->setInfo($this->language->txt('terminated_info'));
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

            $item = new ilCheckboxInputGUI($this->language->txt('hide_data_label'), 'hide_data');
            $item->setInfo($this->language->txt('hide_data_info'));
            $item->setChecked($this->object->getHideData());

            $subitem = new ilCheckboxInputGUI($this->language->txt('timestamp_label'), 'timestamp');
            $subitem->setInfo($this->language->txt('timestamp_info'));
            $subitem->setChecked($this->object->getTimestamp());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            $subitem = new ilCheckboxInputGUI($this->language->txt('duration_label'), 'duration');
            $subitem->setInfo($this->language->txt('duration_info'));
            $subitem->setChecked($this->object->getDuration());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $subitem->setDisabled(true);
            }
            $item->addSubItem($subitem);

            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $item->setDisabled(true);
            }
            $form->addItem($item);

            $item = new ilCheckboxInputGUI($this->language->txt('no_substatements_label'), 'no_substatements');
            $item->setInfo($this->language->txt('no_substatements_info'));
            $item->setChecked($this->object->getNoSubstatements());
            if ($this->object->getLrsType()->getForcePrivacySettings()) {
                $item->setDisabled(true);
            }
            $form->addItem($item);
        }
        
        $item = new ilFormSectionHeaderGUI();
        $item->setTitle($this->language->txt("log_options"));
        $form->addItem($item);
        
        $item = new ilCheckboxInputGUI($this->language->txt('show_debug'), 'show_debug');
        $item->setInfo($this->language->txt("show_debug_info"));
        $item->setValue("1");
        if ($this->object->isStatementsReportEnabled()) {
            $item->setChecked(true);
        }
        $form->addItem($item);
        
        $highscore = new ilCheckboxInputGUI($this->language->txt("highscore_enabled"), "highscore_enabled");
        $highscore->setValue("1");
        $highscore->setChecked($this->object->getHighscoreEnabled());
        $highscore->setInfo($this->language->txt("highscore_description"));
        $form->addItem($highscore);
        $highscore_tables = new ilRadioGroupInputGUI($this->language->txt('highscore_mode'), 'highscore_mode');
        $highscore_tables->setRequired(true);
        $highscore_tables->setValue((string) $this->object->getHighscoreMode());
        $highscore_table_own = new ilRadioOption(
            $this->language->txt('highscore_own_table'),
            (string) ilObjCmiXapi::HIGHSCORE_SHOW_OWN_TABLE
        );
        $highscore_table_own->setInfo($this->language->txt('highscore_own_table_description'));
        $highscore_tables->addOption($highscore_table_own);
        $highscore_table_other = new ilRadioOption(
            $this->language->txt('highscore_top_table'),
            (string) ilObjCmiXapi::HIGHSCORE_SHOW_TOP_TABLE
        );
        $highscore_table_other->setInfo($this->language->txt('highscore_top_table_description'));
        $highscore_tables->addOption($highscore_table_other);
        $highscore_table_other = new ilRadioOption(
            $this->language->txt('highscore_all_tables'),
            (string) ilObjCmiXapi::HIGHSCORE_SHOW_ALL_TABLES
        );
        $highscore_table_other->setInfo($this->language->txt('highscore_all_tables_description'));
        $highscore_tables->addOption($highscore_table_other);
        $highscore->addSubItem($highscore_tables);
        $highscore_top_num = new ilNumberInputGUI($this->language->txt("highscore_top_num"), "highscore_top_num");
        $highscore_top_num->setSize(4);
        $highscore_top_num->setRequired(true);
        $highscore_top_num->setMinValue(1);
        $highscore_top_num->setSuffix($this->language->txt("highscore_top_num_unit"));
        $highscore_top_num->setValue((string) $this->object->getHighscoreTopNum(null));
        $highscore_top_num->setInfo($this->language->txt("highscore_top_num_description"));
        $highscore->addSubItem($highscore_top_num);
        $highscore_achieved_ts = new ilCheckboxInputGUI($this->language->txt("highscore_achieved_ts"), "highscore_achieved_ts");
        $highscore_achieved_ts->setValue("1");
        $highscore_achieved_ts->setChecked($this->object->getHighscoreAchievedTS());
        $highscore_achieved_ts->setInfo($this->language->txt("highscore_achieved_ts_description"));
        $highscore->addSubItem($highscore_achieved_ts);
        $highscore_percentage = new ilCheckboxInputGUI($this->language->txt("highscore_percentage"), "highscore_percentage");
        $highscore_percentage->setValue("1");
        $highscore_percentage->setChecked($this->object->getHighscorePercentage());
        $highscore_percentage->setInfo($this->language->txt("highscore_percentage_description"));
        $highscore->addSubItem($highscore_percentage);
        $highscore_wtime = new ilCheckboxInputGUI($this->language->txt("highscore_wtime"), "highscore_wtime");
        $highscore_wtime->setValue("1");
        $highscore_wtime->setChecked($this->object->getHighscoreWTime());
        $highscore_wtime->setInfo($this->language->txt("highscore_wtime_description"));
        $highscore->addSubItem($highscore_wtime);
        
        
        $form->setTitle($this->language->txt('settings'));
        $form->addCommandButton(self::CMD_SAVE, $this->language->txt("save"));
        $form->addCommandButton(self::CMD_SHOW, $this->language->txt("cancel"));
        
        return $form;
    }
    
    protected function saveSettings(ilPropertyFormGUI $form) : void
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
                $this->object->setPrivacyIdent((int) $form->getInput('privacy_ident'));
                $this->object->setPrivacyName((int) $form->getInput('privacy_name'));
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
    
    protected function deliverCertificateCmd() : void
    {
        $validator = new ilCertificateDownloadValidator();

        if (!$validator->isCertificateDownloadable((int) $this->dic->user()->getId(), $this->object->getId())) {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt("permission_denied"), true);
            $this->dic->ctrl()->redirectByClass(ilObjCmiXapiGUI::class, ilObjCmiXapiGUI::CMD_INFO_SCREEN);
        }

        $repository = new ilUserCertificateRepository();

        $certLogger = $this->dic->logger()->root();//->cert();
        $pdfGenerator = new ilPdfGenerator($repository, $certLogger);

        $pdfAction = new ilCertificatePdfAction(
            $certLogger,
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $this->language->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf((int) $this->dic->user()->getId(), $this->object->getId());
    }
}
