<?php declare(strict_types=1);
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 * @ilCtrl_Calls ilObjCalendarSettingsGUI: ilPermissionGUI
 * @ingroup      ServicesCalendar
 */
class ilObjCalendarSettingsGUI extends ilObjectGUI
{
    protected ilCalendarSettings $calendar_settings;

    /**
     * Constructor
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->type = 'cals';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->initCalendarSettings();

        $this->lng->loadLanguageModule('dateplaner');
        $this->lng->loadLanguageModule('jscalendar');
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                $this->tabs_gui->setTabActive('settings');
                $this->initCalendarSettings();
                if (!$cmd || $cmd == 'view') {
                    $cmd = "settings";
                }
                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs() : void
    {
        if ($this->access->checkAccess("read", '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "settings"),
                array("settings", "view")
            );
        }

        if ($this->access->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    public function settings(?ilPropertyFormGUI $form = null) : void
    {
        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }
        $this->tabs_gui->setTabActive('settings');
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initFormSettings();
        }
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.settings.html', 'Services/Calendar');
        $this->tpl->setVariable('CAL_SETTINGS', $form->getHTML());
    }

    /**
     * save settings
     * @access protected
     */
    protected function save()
    {
        $this->checkPermission('write');

        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $this->calendar_settings->setEnabled((bool) $form->getInput('enable'));
            $this->calendar_settings->setDefaultWeekStart((int) $form->getInput('default_week_start'));
            $this->calendar_settings->setDefaultTimeZone($form->getInput('default_timezone'));
            $this->calendar_settings->setDefaultDateFormat($form->getInput('default_date_format'));
            $this->calendar_settings->setDefaultTimeFormat($form->getInput('default_time_format'));
            $this->calendar_settings->setEnableGroupMilestones($form->getInput('enable_grp_milestones'));
            $this->calendar_settings->enableCourseCalendar($form->getInput('enabled_crs'));
            $this->calendar_settings->setCourseCalendarVisible($form->getInput('visible_crs'));
            $this->calendar_settings->enableGroupCalendar($form->getInput('enabled_grp'));
            $this->calendar_settings->setGroupCalendarVisible($form->getInput('visible_grp'));
            $this->calendar_settings->setDefaultDayStart($form->getInput('dst'));
            $this->calendar_settings->setDefaultDayEnd($form->getInput('den'));
            $this->calendar_settings->enableSynchronisationCache($form->getInput('sync_cache'));
            $this->calendar_settings->setSynchronisationCacheMinutes($form->getInput('sync_cache_time'));
            $this->calendar_settings->setCacheMinutes($form->getInput('cache_time'));
            $this->calendar_settings->useCache($form->getInput('cache'));
            $this->calendar_settings->enableNotification($form->getInput('cn'));
            $this->calendar_settings->enableUserNotification($form->getInput('cnu'));
            $this->calendar_settings->enableConsultationHours($form->getInput('ch'));
            $this->calendar_settings->enableCGRegistration($form->getInput('cgr'));
            $this->calendar_settings->enableWebCalSync($form->getInput('webcal'));
            $this->calendar_settings->setWebCalSyncHours($form->getInput('webcal_hours'));
            $this->calendar_settings->setShowWeeks($form->getInput('show_weeks'));
            $this->calendar_settings->enableBatchFileDownloads((bool) $form->getInput('batch_files'));
            $this->calendar_settings->setDefaultCal((int) $form->getInput('default_calendar_view'));
            $this->calendar_settings->setDefaultPeriod((int) $form->getInput('default_period'));
            $this->calendar_settings->save();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'settings');
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
        $this->settings($form);
    }

    /**
     * init calendar settings
     */
    protected function initCalendarSettings() : void
    {
        $this->calendar_settings = ilCalendarSettings::_getInstance();
    }

    /**
     * Init settings property form
     * @access protected
     */
    protected function initFormSettings() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('cal_global_settings'));

        if ($this->checkPermissionBool('write')) {
            $form->addCommandButton('save', $this->lng->txt('save'));
        }

        $check = new ilCheckboxInputGUI($this->lng->txt('enable_calendar'), 'enable');
        $check->setValue('1');
        $check->setChecked($this->calendar_settings->isEnabled() ? true : false);
        $form->addItem($check);

        // show weeks
        $cb = new ilCheckboxInputGUI($this->lng->txt("cal_def_show_weeks"), "show_weeks");
        $cb->setInfo($this->lng->txt("cal_show_weeks_info"));
        $cb->setValue('1');
        $cb->setChecked($this->calendar_settings->getShowWeeks());
        $form->addItem($cb);

        $sync = new ilCheckboxInputGUI($this->lng->txt('cal_webcal_sync'), 'webcal');
        $sync->setValue('1');
        $sync->setChecked($this->calendar_settings->isWebCalSyncEnabled());
        $sync->setInfo($this->lng->txt('cal_webcal_sync_info'));

        $sync_min = new ilNumberInputGUI('', 'webcal_hours');
        $sync_min->setSize(2);
        $sync_min->setMaxLength(3);
        $sync_min->setValue((string) $this->calendar_settings->getWebCalSyncHours());
        $sync_min->setSuffix($this->lng->txt('hours'));
        $sync->addSubItem($sync_min);

        $form->addItem($sync);

        //Batch File Downloads in Calendar
        $batch_files_download = new ilCheckboxInputGUI($this->lng->txt('cal_batch_file_downloads'), "batch_files");
        $batch_files_download->setValue('1');
        $batch_files_download->setChecked($this->calendar_settings->isBatchFileDownloadsEnabled());
        $batch_files_download->setInfo($this->lng->txt('cal_batch_file_downloads_info'));
        $form->addItem($batch_files_download);

        $def = new ilFormSectionHeaderGUI();
        $def->setTitle($this->lng->txt('cal_default_settings'));
        $form->addItem($def);

        $server_tz = new ilNonEditableValueGUI($this->lng->txt('cal_server_tz'));
        $server_tz->setValue(ilTimeZone::_getDefaultTimeZone());
        $form->addItem($server_tz);

        $select = new ilSelectInputGUI($this->lng->txt('cal_def_timezone'), 'default_timezone');
        $select->setOptions(ilCalendarUtil::_getShortTimeZoneList());
        $select->setInfo($this->lng->txt('cal_def_timezone_info'));
        $select->setValue($this->calendar_settings->getDefaultTimeZone());
        $form->addItem($select);

        $year = date("Y");
        $select = new ilSelectInputGUI($this->lng->txt('cal_def_date_format'), 'default_date_format');
        $select->setOptions(array(
            ilCalendarSettings::DATE_FORMAT_DMY => '31.10.' . $year,
            ilCalendarSettings::DATE_FORMAT_YMD => $year . "-10-31",
            ilCalendarSettings::DATE_FORMAT_MDY => "10/31/" . $year
        ));
        $select->setInfo($this->lng->txt('cal_def_date_format_info'));
        $select->setValue($this->calendar_settings->getDefaultDateFormat());
        $form->addItem($select);

        $select = new ilSelectInputGUI($this->lng->txt('cal_def_time_format'), 'default_time_format');
        $select->setOptions(array(
            ilCalendarSettings::TIME_FORMAT_24 => '13:00',
            ilCalendarSettings::TIME_FORMAT_12 => '1:00pm'
        ));
        $select->setInfo($this->lng->txt('cal_def_time_format_info'));
        $select->setValue($this->calendar_settings->getDefaultTimeFormat());
        $form->addItem($select);

        // Weekstart
        $radio = new ilRadioGroupInputGUI($this->lng->txt('cal_def_week_start'), 'default_week_start');
        $radio->setValue((string) $this->calendar_settings->getDefaultWeekStart());

        $option = new ilRadioOption($this->lng->txt('l_su'), '0');
        $radio->addOption($option);
        $option = new ilRadioOption($this->lng->txt('l_mo'), '1');
        $radio->addOption($option);

        $form->addItem($radio);

        $default_cal_view = new ilRadioGroupInputGUI($this->lng->txt('cal_def_view'), 'default_calendar_view');

        $option = new ilRadioOption($this->lng->txt("day"), (string) ilCalendarSettings::DEFAULT_CAL_DAY);
        $default_cal_view->addOption($option);
        $option = new ilRadioOption($this->lng->txt("week"), (string) ilCalendarSettings::DEFAULT_CAL_WEEK);
        $default_cal_view->addOption($option);
        $option = new ilRadioOption($this->lng->txt("month"), (string) ilCalendarSettings::DEFAULT_CAL_MONTH);
        $default_cal_view->addOption($option);

        $option = new ilRadioOption($this->lng->txt("cal_list"), (string) ilCalendarSettings::DEFAULT_CAL_LIST);

        $list_views = new ilSelectInputGUI($this->lng->txt("cal_list"), "default_period");
        $list_views->setOptions([
            ilCalendarAgendaListGUI::PERIOD_DAY => "1 " . $this->lng->txt("day"),
            ilCalendarAgendaListGUI::PERIOD_WEEK => "1 " . $this->lng->txt("week"),
            ilCalendarAgendaListGUI::PERIOD_MONTH => "1 " . $this->lng->txt("month"),
            ilCalendarAgendaListGUI::PERIOD_HALF_YEAR => "6 " . $this->lng->txt("months")
        ]);

        $list_views->setValue($this->calendar_settings->getDefaultPeriod());
        $option->addSubItem($list_views);
        $default_cal_view->addOption($option);
        $default_cal_view->setValue((string) $this->calendar_settings->getDefaultCal());

        $form->addItem($default_cal_view);

        // Day start
        $day_start = new ilSelectInputGUI($this->lng->txt('cal_def_day_start'), 'dst');
        $day_start->setOptions(
            ilCalendarUtil::getHourSelection($this->calendar_settings->getDefaultTimeFormat())
        );
        $day_start->setValue($this->calendar_settings->getDefaultDayStart());
        $form->addItem($day_start);

        $day_end = new ilSelectInputGUI($this->lng->txt('cal_def_day_end'), 'den');
        $day_end->setOptions(
            ilCalendarUtil::getHourSelection($this->calendar_settings->getDefaultTimeFormat())
        );
        $day_end->setValue($this->calendar_settings->getDefaultDayEnd());
        $form->addItem($day_end);

        // enable milestone planning in groups
        $mil = new ilFormSectionHeaderGUI();
        $mil->setTitle($this->lng->txt('cal_milestone_settings'));
        $form->addItem($mil);

        $checkm = new ilCheckboxInputGUI($this->lng->txt('cal_enable_group_milestones'), 'enable_grp_milestones');
        $checkm->setValue('1');
        $checkm->setChecked($this->calendar_settings->getEnableGroupMilestones());
        $checkm->setInfo($this->lng->txt('cal_enable_group_milestones_desc'));
        $form->addItem($checkm);

        // Consultation hours
        $con = new ilFormSectionHeaderGUI();
        $con->setTitle($this->lng->txt('cal_ch_form_header'));
        $form->addItem($con);

        $ch = new ilCheckboxInputGUI($this->lng->txt('cal_ch_form'), 'ch');
        $ch->setInfo($this->lng->txt('cal_ch_form_info'));
        $ch->setValue('1');
        $ch->setChecked($this->calendar_settings->areConsultationHoursEnabled());
        $form->addItem($ch);

        // repository visibility default
        $rep = new ilFormSectionHeaderGUI();
        $rep->setTitle($GLOBALS['DIC']['lng']->txt('cal_setting_global_vis_repos'));
        $form->addItem($rep);

        $crs_active = new ilCheckboxInputGUI(
            $this->lng->txt('cal_setting_global_crs_act'),
            'enabled_crs'
        );
        $crs_active->setInfo($this->lng->txt('cal_setting_global_crs_act_info'));
        $crs_active->setValue('1');
        $crs_active->setChecked($this->calendar_settings->isCourseCalendarEnabled());
        $form->addItem($crs_active);

        $crs = new ilCheckboxInputGUI($GLOBALS['DIC']['lng']->txt('cal_setting_global_crs_vis'), 'visible_crs');
        $crs->setInfo($GLOBALS['DIC']['lng']->txt('cal_setting_global_crs_vis_info'));
        $crs->setValue('1');
        $crs->setChecked($this->calendar_settings->isCourseCalendarVisible());
        $crs_active->addSubItem($crs);

        $grp_active = new ilCheckboxInputGUI(
            $this->lng->txt('cal_setting_global_grp_act'),
            'enabled_grp'
        );
        $grp_active->setInfo($this->lng->txt('cal_setting_global_grp_act_info'));
        $grp_active->setValue('1');
        $grp_active->setChecked($this->calendar_settings->isGroupCalendarEnabled());
        $form->addItem($grp_active);

        $grp = new ilCheckboxInputGUI($GLOBALS['DIC']['lng']->txt('cal_setting_global_grp_vis'), 'visible_grp');
        $grp->setInfo($GLOBALS['DIC']['lng']->txt('cal_setting_global_grp_vis_info'));
        $grp->setValue('1');
        $grp->setInfo($GLOBALS['DIC']['lng']->txt('cal_setting_global_grp_vis_info'));
        $grp->setChecked($this->calendar_settings->isGroupCalendarVisible());
        $grp_active->addSubItem($grp);

        // Notifications
        $not = new ilFormSectionHeaderGUI();
        $not->setTitle($this->lng->txt('notifications'));
        $form->addItem($not);

        $cgn = new ilCheckboxInputGUI($this->lng->txt('cal_notification'), 'cn');
        $cgn->setOptionTitle($this->lng->txt('cal_notification_crsgrp'));
        $cgn->setValue('1');
        $cgn->setChecked($this->calendar_settings->isNotificationEnabled());
        $cgn->setInfo($this->lng->txt('cal_adm_notification_info'));
        $form->addItem($cgn);

        $cnu = new ilCheckboxInputGUI('', 'cnu');
        $cnu->setOptionTitle($this->lng->txt('cal_notification_users'));
        $cnu->setValue('1');
        $cnu->setChecked($this->calendar_settings->isUserNotificationEnabled());
        $cnu->setInfo($this->lng->txt('cal_adm_notification_user_info'));
        $form->addItem($cnu);

        // Registration
        $book = new ilFormSectionHeaderGUI();
        $book->setTitle($this->lng->txt('cal_registrations'));
        $form->addItem($book);

        $cgn = new ilCheckboxInputGUI($this->lng->txt('cal_cg_registrations'), 'cgr');
        $cgn->setValue('1');
        $cgn->setChecked($this->calendar_settings->isCGRegistrationEnabled());
        $cgn->setInfo($this->lng->txt('cal_cg_registration_info'));
        $form->addItem($cgn);

        // Synchronisation cache
        $sec = new ilFormSectionHeaderGUI();
        $sec->setTitle($this->lng->txt('cal_cache_settings'));
        $form->addItem($sec);

        $cache = new ilRadioGroupInputGUI($this->lng->txt('cal_sync_cache'), 'sync_cache');
        $cache->setValue((string) $this->calendar_settings->isSynchronisationCacheEnabled());
        $cache->setInfo($this->lng->txt('cal_sync_cache_info'));
        $cache->setRequired(true);

        $sync_cache = new ilRadioOption($this->lng->txt('cal_sync_disabled'), '0');
        $cache->addOption($sync_cache);

        $sync_cache = new ilRadioOption($this->lng->txt('cal_sync_enabled'), '1');
        $cache->addOption($sync_cache);

        $cache_t = new ilNumberInputGUI('', 'sync_cache_time');
        $cache_t->setValue((string) $this->calendar_settings->getSynchronisationCacheMinutes());
        $cache_t->setMinValue(0);
        $cache_t->setSize(3);
        $cache_t->setMaxLength(3);
        $cache_t->setSuffix($this->lng->txt('form_minutes'));
        $sync_cache->addSubItem($cache_t);

        $form->addItem($cache);

        // Calendar cache
        $cache = new ilRadioGroupInputGUI($this->lng->txt('cal_cache'), 'cache');
        $cache->setValue((string) $this->calendar_settings->isCacheUsed());
        $cache->setInfo($this->lng->txt('cal_cache_info'));
        $cache->setRequired(true);

        $sync_cache = new ilRadioOption($this->lng->txt('cal_cache_disabled'), '0');
        $cache->addOption($sync_cache);

        $sync_cache = new ilRadioOption($this->lng->txt('cal_cache_enabled'), '1');
        $cache->addOption($sync_cache);

        $cache_t = new ilNumberInputGUI('', 'cache_time');
        $cache_t->setValue((string) $this->calendar_settings->getCacheMinutes());
        $cache_t->setMinValue(0);
        $cache_t->setSize(3);
        $cache_t->setMaxLength(3);
        $cache_t->setSuffix($this->lng->txt('form_minutes'));
        $sync_cache->addSubItem($cache_t);
        $form->addItem($cache);
        return $form;
    }

    public function addToExternalSettingsForm(int $a_form_id) : array
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_COURSE:

                $this->initCalendarSettings();

                $fields = array();

                $subitems = array(
                    'cal_setting_global_crs_act' => [
                        $this->calendar_settings->isCourseCalendarEnabled(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ],
                    'cal_setting_global_crs_vis' =>
                        array($this->calendar_settings->isCourseCalendarVisible(),
                              ilAdministrationSettingsFormHandler::VALUE_BOOL
                        ),

                );
                $fields['cal_setting_global_vis_repos'] = array(null, null, $subitems);

                $subitems = array(
                    'cal_notification_crsgrp' => array($this->calendar_settings->isNotificationEnabled(),
                                                       ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'cal_notification_users' => array($this->calendar_settings->isUserNotificationEnabled(),
                                                      ilAdministrationSettingsFormHandler::VALUE_BOOL
                    )
                );
                $fields['cal_notification'] = array(null, null, $subitems);

                $fields['cal_cg_registrations'] = array($this->calendar_settings->isCGRegistrationEnabled(),
                                                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                );

                return array(array("settings", $fields));

            case ilAdministrationSettingsFormHandler::FORM_GROUP:

                $this->initCalendarSettings();

                $fields = array();

                $subitems = array(
                    'cal_setting_global_grp_act' => [
                        $this->calendar_settings->isGroupCalendarEnabled(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ],
                    'cal_setting_global_grp_vis' =>
                        array($this->calendar_settings->isGroupCalendarVisible(),
                              ilAdministrationSettingsFormHandler::VALUE_BOOL
                        ),

                );

                $fields['cal_setting_global_vis_repos'] = array(null, null, $subitems);

                $subitems = array(
                    'cal_notification_crsgrp' => array($this->calendar_settings->isNotificationEnabled(),
                                                       ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ),
                    'cal_notification_users' => array($this->calendar_settings->isUserNotificationEnabled(),
                                                      ilAdministrationSettingsFormHandler::VALUE_BOOL
                    )
                );
                $fields['cal_notification'] = array(null, null, $subitems);

                $fields['cal_cg_registrations'] = array($this->calendar_settings->isCGRegistrationEnabled(),
                                                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                );

                return array(array("settings", $fields));
        }
        return [];
    }
}
