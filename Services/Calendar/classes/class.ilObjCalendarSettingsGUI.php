<?php
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
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls ilObjCalendarSettingsGUI: ilPermissionGUI
* @ingroup ServicesCalendar
*/
class ilObjCalendarSettingsGUI extends ilObjectGUI
{

    /**
     * @var ilCalendarSettings
     */
    protected $settings;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $this->type = 'cals';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng = $lng;
        $this->lng->loadLanguageModule('dateplaner');
        $this->lng->loadLanguageModule('jscalendar');
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilAccess = $DIC['ilAccess'];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$ilAccess->checkAccess('read', '', $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
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
        return true;
    }
    

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];

        if ($ilAccess->checkAccess("read", '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "settings"),
                array("settings", "view")
            );
        }

        if ($ilAccess->checkAccess('edit_permission', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    /**
    * Edit settings.
    */
    public function settings()
    {
        $this->checkPermission('read');
        
        $this->tabs_gui->setTabActive('settings');
        $this->initFormSettings();
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.settings.html', 'Services/Calendar');
        $this->tpl->setVariable('CAL_SETTINGS', $this->form->getHTML());
        return true;
    }
    
    /**
     * save settings
     *
     * @access protected
     */
    protected function save()
    {
        $this->checkPermission('write');
        
        $this->settings->setEnabled((int) $_POST['enable']);
        $this->settings->setDefaultWeekStart((int) $_POST['default_week_start']);
        $this->settings->setDefaultTimeZone(ilUtil::stripSlashes($_POST['default_timezone']));
        $this->settings->setDefaultDateFormat((int) $_POST['default_date_format']);
        $this->settings->setDefaultTimeFormat((int) $_POST['default_time_format']);
        $this->settings->setEnableGroupMilestones((int) $_POST['enable_grp_milestones']);
        $this->settings->enableCourseCalendar((int) $_POST['visible_crs']);
        $this->settings->enableGroupCalendar((int) $_POST['visible_grp']);
        $this->settings->setDefaultDayStart((int) $_POST['dst']);
        $this->settings->setDefaultDayEnd((int) $_POST['den']);
        $this->settings->enableSynchronisationCache((bool) $_POST['sync_cache']);
        $this->settings->setSynchronisationCacheMinutes((int) $_POST['sync_cache_time']);
        $this->settings->setCacheMinutes((int) $_POST['cache_time']);
        $this->settings->useCache((bool) $_POST['cache']);
        $this->settings->enableNotification((bool) $_POST['cn']);
        $this->settings->enableUserNotification((bool) $_POST['cnu']);
        $this->settings->enableConsultationHours((bool) $_POST['ch']);
        $this->settings->enableCGRegistration((bool) $_POST['cgr']);
        $this->settings->enableWebCalSync((bool) $_POST['webcal']);
        $this->settings->setWebCalSyncHours((int) $_POST['webcal_hours']);
        $this->settings->setShowWeeks((int) $_POST['show_weeks']);
        $this->settings->enableBatchFileDownloads((bool) $_POST['batch_files']);

        if (((int) $_POST['den']) < (int) $_POST['dst']) {
            ilUtil::sendFailure($this->lng->txt('cal_dstart_dend_warn'));
            $this->settings();
            return false;
        }
        
        $this->settings->save();
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->settings();
    }

    /**
     * init calendar settings
     *
     * @access protected
     */
    protected function initCalendarSettings()
    {
        include_once('Services/Calendar/classes/class.ilCalendarSettings.php');
        $this->settings = ilCalendarSettings::_getInstance();
    }
    
    /**
     * Init settings property form
     *
     * @access protected
     */
    protected function initFormSettings()
    {
        if (is_object($this->form)) {
            return true;
        }
        
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setTitle($this->lng->txt('cal_global_settings'));


        if ($this->checkPermissionBool('write')) {
            $this->form->addCommandButton('save', $this->lng->txt('save'));
        }
        
        $check = new ilCheckboxInputGUI($this->lng->txt('enable_calendar'), 'enable');
        $check->setValue(1);
        $check->setChecked($this->settings->isEnabled() ? true : false);
        $this->form->addItem($check);
        
        $server_tz = new ilNonEditableValueGUI($this->lng->txt('cal_server_tz'));
        $server_tz->setValue(ilTimeZone::_getDefaultTimeZone());
        $this->form->addItem($server_tz);
        
        $select = new ilSelectInputGUI($this->lng->txt('cal_def_timezone'), 'default_timezone');
        $select->setOptions(ilCalendarUtil::_getShortTimeZoneList());
        $select->setInfo($this->lng->txt('cal_def_timezone_info'));
        $select->setValue($this->settings->getDefaultTimeZone());
        $this->form->addItem($select);

        $year = date("Y");
        $select = new ilSelectInputGUI($this->lng->txt('cal_def_date_format'), 'default_date_format');
        $select->setOptions(array(
            ilCalendarSettings::DATE_FORMAT_DMY => '31.10.' . $year,
            ilCalendarSettings::DATE_FORMAT_YMD => $year . "-10-31",
            ilCalendarSettings::DATE_FORMAT_MDY => "10/31/" . $year));
        $select->setInfo($this->lng->txt('cal_def_date_format_info'));
        $select->setValue($this->settings->getDefaultDateFormat());
        $this->form->addItem($select);
        
        $select = new ilSelectInputGUI($this->lng->txt('cal_def_time_format'), 'default_time_format');
        $select->setOptions(array(
            ilCalendarSettings::TIME_FORMAT_24 => '13:00',
            ilCalendarSettings::TIME_FORMAT_12 => '1:00pm'));
        $select->setInfo($this->lng->txt('cal_def_time_format_info'));
        $select->setValue($this->settings->getDefaultTimeFormat());
        $this->form->addItem($select);

        // Weekstart
        $radio = new ilRadioGroupInputGUI($this->lng->txt('cal_def_week_start'), 'default_week_start');
        $radio->setValue($this->settings->getDefaultWeekStart());

        $option = new ilRadioOption($this->lng->txt('l_su'), 0);
        $radio->addOption($option);
        $option = new ilRadioOption($this->lng->txt('l_mo'), 1);
        $radio->addOption($option);

        $this->form->addItem($radio);

        // show weeks
        $cb = new ilCheckboxInputGUI($this->lng->txt("cal_def_show_weeks"), "show_weeks");
        $cb->setInfo($this->lng->txt("cal_show_weeks_info"));
        $cb->setValue(1);
        $cb->setChecked($this->settings->getShowWeeks());
        $this->form->addItem($cb);


        // Day start
        $day_start = new ilSelectInputGUI($this->lng->txt('cal_def_day_start'), 'dst');
        $day_start->setOptions(
            ilCalendarUtil::getHourSelection($this->settings->getDefaultTimeFormat())
        );
        $day_start->setValue($this->settings->getDefaultDayStart());
        $this->form->addItem($day_start);

        $day_end = new ilSelectInputGUI($this->lng->txt('cal_def_day_end'), 'den');
        $day_end->setOptions(
            ilCalendarUtil::getHourSelection($this->settings->getDefaultTimeFormat())
        );
        $day_end->setValue($this->settings->getDefaultDayEnd());
        $this->form->addItem($day_end);
        
        $sync = new ilCheckboxInputGUI($this->lng->txt('cal_webcal_sync'), 'webcal');
        $sync->setValue(1);
        $sync->setChecked($this->settings->isWebCalSyncEnabled());
        $sync->setInfo($this->lng->txt('cal_webcal_sync_info'));
        
        $sync_min = new ilNumberInputGUI('', 'webcal_hours');
        $sync_min->setSize(2);
        $sync_min->setMaxLength(3);
        $sync_min->setValue($this->settings->getWebCalSyncHours());
        $sync_min->setSuffix($this->lng->txt('hours'));
        $sync->addSubItem($sync_min);
        
        $this->form->addItem($sync);

        //Batch File Downloads in Calendar
        $batch_files_download = new ilCheckboxInputGUI($this->lng->txt('cal_batch_file_downloads'), "batch_files");
        $batch_files_download->setValue(1);
        $batch_files_download->setChecked($this->settings->isBatchFileDownloadsEnabled());
        $batch_files_download->setInfo($this->lng->txt('cal_batch_file_downloads_info'));
        $this->form->addItem($batch_files_download);

        // enable milestone planning in groups
        $mil = new ilFormSectionHeaderGUI();
        $mil->setTitle($this->lng->txt('cal_milestone_settings'));
        $this->form->addItem($mil);

        $checkm = new ilCheckboxInputGUI($this->lng->txt('cal_enable_group_milestones'), 'enable_grp_milestones');
        $checkm->setValue(1);
        $checkm->setChecked($this->settings->getEnableGroupMilestones() ? true : false);
        $checkm->setInfo($this->lng->txt('cal_enable_group_milestones_desc'));
        $this->form->addItem($checkm);
        
        // Consultation hours
        $con = new ilFormSectionHeaderGUI();
        $con->setTitle($this->lng->txt('cal_ch_form_header'));
        $this->form->addItem($con);

        $ch = new ilCheckboxInputGUI($this->lng->txt('cal_ch_form'), 'ch');
        $ch->setInfo($this->lng->txt('cal_ch_form_info'));
        $ch->setValue(1);
        $ch->setChecked($this->settings->areConsultationHoursEnabled());
        $this->form->addItem($ch);

        // repository visibility default
        $rep = new ilFormSectionHeaderGUI();
        $rep->setTitle($GLOBALS['DIC']['lng']->txt('cal_setting_global_vis_repos'));
        $this->form->addItem($rep);
        
        $crs = new ilCheckboxInputGUI($GLOBALS['DIC']['lng']->txt('cal_setting_global_crs_vis'), 'visible_crs');
        $crs->setInfo($GLOBALS['DIC']['lng']->txt('cal_setting_global_crs_vis_info'));
        $crs->setValue(1);
        $crs->setInfo($GLOBALS['DIC']['lng']->txt('cal_setting_global_crs_vis_info'));
        $crs->setChecked($this->settings->isCourseCalendarEnabled());
        $this->form->addItem($crs);

        $grp = new ilCheckboxInputGUI($GLOBALS['DIC']['lng']->txt('cal_setting_global_grp_vis'), 'visible_grp');
        $grp->setInfo($GLOBALS['DIC']['lng']->txt('cal_setting_global_grp_vis_info'));
        $grp->setValue(1);
        $grp->setInfo($GLOBALS['DIC']['lng']->txt('cal_setting_global_grp_vis_info'));
        $grp->setChecked($this->settings->isGroupCalendarEnabled());
        $this->form->addItem($grp);

        
        
        
        // Notifications
        $not = new ilFormSectionHeaderGUI();
        $not->setTitle($this->lng->txt('notifications'));
        $this->form->addItem($not);
        
        $cgn = new ilCheckboxInputGUI($this->lng->txt('cal_notification'), 'cn');
        $cgn->setOptionTitle($this->lng->txt('cal_notification_crsgrp'));
        $cgn->setValue(1);
        $cgn->setChecked($this->settings->isNotificationEnabled());
        $cgn->setInfo($this->lng->txt('cal_adm_notification_info'));
        $this->form->addItem($cgn);

        $cnu = new ilCheckboxInputGUI('', 'cnu');
        $cnu->setOptionTitle($this->lng->txt('cal_notification_users'));
        $cnu->setValue(1);
        $cnu->setChecked($this->settings->isUserNotificationEnabled());
        $cnu->setInfo($this->lng->txt('cal_adm_notification_user_info'));
        $this->form->addItem($cnu);

        
        // Registration
        $book = new ilFormSectionHeaderGUI();
        $book->setTitle($this->lng->txt('cal_registrations'));
        $this->form->addItem($book);
        
        $cgn = new ilCheckboxInputGUI($this->lng->txt('cal_cg_registrations'), 'cgr');
        $cgn->setValue(1);
        $cgn->setChecked($this->settings->isCGRegistrationEnabled());
        $cgn->setInfo($this->lng->txt('cal_cg_registration_info'));
        $this->form->addItem($cgn);

        // Synchronisation cache
        $sec = new ilFormSectionHeaderGUI();
        $sec->setTitle($this->lng->txt('cal_cache_settings'));
        $this->form->addItem($sec);

        $cache = new ilRadioGroupInputGUI($this->lng->txt('cal_sync_cache'), 'sync_cache');
        $cache->setValue((int) $this->settings->isSynchronisationCacheEnabled());
        $cache->setInfo($this->lng->txt('cal_sync_cache_info'));
        $cache->setRequired(true);

        $sync_cache = new ilRadioOption($this->lng->txt('cal_sync_disabled'), 0);
        $cache->addOption($sync_cache);

        $sync_cache = new ilRadioOption($this->lng->txt('cal_sync_enabled'), 1);
        $cache->addOption($sync_cache);

        $cache_t = new ilNumberInputGUI('', 'sync_cache_time');
        $cache_t->setValue($this->settings->getSynchronisationCacheMinutes());
        $cache_t->setMinValue(0);
        $cache_t->setSize(3);
        $cache_t->setMaxLength(3);
        $cache_t->setSuffix($this->lng->txt('form_minutes'));
        $sync_cache->addSubItem($cache_t);

        $this->form->addItem($cache);

        // Calendar cache
        $cache = new ilRadioGroupInputGUI($this->lng->txt('cal_cache'), 'cache');
        $cache->setValue((int) $this->settings->isCacheUsed());
        $cache->setInfo($this->lng->txt('cal_cache_info'));
        $cache->setRequired(true);

        $sync_cache = new ilRadioOption($this->lng->txt('cal_cache_disabled'), 0);
        $cache->addOption($sync_cache);

        $sync_cache = new ilRadioOption($this->lng->txt('cal_cache_enabled'), 1);
        $cache->addOption($sync_cache);

        $cache_t = new ilNumberInputGUI('', 'cache_time');
        $cache_t->setValue($this->settings->getCacheMinutes());
        $cache_t->setMinValue(0);
        $cache_t->setSize(3);
        $cache_t->setMaxLength(3);
        $cache_t->setSuffix($this->lng->txt('form_minutes'));
        $sync_cache->addSubItem($cache_t);
        $this->form->addItem($cache);
    }
    
    public function addToExternalSettingsForm($a_form_id)
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_COURSE:
                
                $this->initCalendarSettings();
                
                $fields = array();
                
                $subitems = array('cal_setting_global_crs_vis' => array($this->settings->isCourseCalendarEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL));
                $fields['cal_setting_global_vis_repos'] = array(null, null, $subitems);
                        
                $subitems = array(
                    'cal_notification_crsgrp' => array($this->settings->isNotificationEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
                    'cal_notification_users' => array($this->settings->isUserNotificationEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL)
                );
                $fields['cal_notification'] = array(null, null, $subitems);
                
                $fields['cal_cg_registrations'] = array($this->settings->isCGRegistrationEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL);
                                
                return array(array("settings", $fields));
                
            case ilAdministrationSettingsFormHandler::FORM_GROUP:
                
                $this->initCalendarSettings();
                
                $fields = array();
                
                $subitems = array('cal_setting_global_grp_vis' => array($this->settings->isGroupCalendarEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL));
                $fields['cal_setting_global_vis_repos'] = array(null, null, $subitems);
                
                $subitems = array(
                    'cal_notification_crsgrp' => array($this->settings->isNotificationEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL),
                    'cal_notification_users' => array($this->settings->isUserNotificationEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL)
                );
                $fields['cal_notification'] = array(null, null, $subitems);
                    
                $fields['cal_cg_registrations'] = array($this->settings->isCGRegistrationEnabled(), ilAdministrationSettingsFormHandler::VALUE_BOOL);
                
                return array(array("settings", $fields));
        }
    }
}
