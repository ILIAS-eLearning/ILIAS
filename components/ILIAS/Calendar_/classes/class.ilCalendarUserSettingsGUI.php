<?php

declare(strict_types=1);
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
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @ilCtrl_Calls ilCalendarUserSettingsGUI:
 * @ingroup      ServicesCalendar
 */
class ilCalendarUserSettingsGUI
{
    protected ilGlobalTemplateInterface $tpl;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilCalendarSettings $settings;
    protected ilCalendarUserSettings $user_settings;

    /**
     * Constructor
     * @access public
     * @param
     */
    public function __construct()
    {
        global $DIC;

        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('dateplaner');
        $this->lng->loadLanguageModule('jscalendar');

        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();
        $this->settings = ilCalendarSettings::_getInstance();
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd("show");
                $this->$cmd();
                break;
        }
    }

    public function show(?ilPropertyFormGUI $form = null): void
    {
        if (!$form instanceof ilPropertyFormGUI) {
            $form = $this->initSettingsForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function cancel(): void
    {
        $this->ctrl->returnToParent($this);
    }

    public function save()
    {
        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $this->user_settings->setTimeZone($form->getInput('timezone'));
            $this->user_settings->setExportTimeZoneType((int) $form->getInput('export_tz'));
            $this->user_settings->setWeekStart((int) $form->getInput('weekstart'));
            $this->user_settings->setDateFormat((int) $form->getInput('date_format'));
            $this->user_settings->setTimeFormat((int) $form->getInput('time_format'));
            $this->user_settings->setDayStart((int) $form->getInput('dst'));
            $this->user_settings->setDayEnd((int) $form->getInput('den'));
            $this->user_settings->setShowWeeks((bool) $form->getInput('show_weeks'));
            $this->user_settings->save();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, "show");
        } else {
            $form->setValuesByPost();
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'), true);
            $this->show();
        }
    }

    public function initSettingsForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'save'));
        $form->setTitle($this->lng->txt('cal_user_settings'));
        $form->addCommandButton('save', $this->lng->txt('save'));
        //$form->addCommandButton('cancel',$this->lng->txt('cancel'));

        $select = new ilSelectInputGUI($this->lng->txt('cal_user_timezone'), 'timezone');
        $select->setOptions(ilCalendarUtil::_getShortTimeZoneList());
        $select->setInfo($this->lng->txt('cal_timezone_info'));
        $select->setValue($this->user_settings->getTimeZone());
        $form->addItem($select);

        $export_type = new ilRadioGroupInputGUI($this->lng->txt('cal_export_timezone'), 'export_tz');
        $export_type->setValue((string) $this->user_settings->getExportTimeZoneType());

        $export_tz = new ilRadioOption(
            $this->lng->txt('cal_export_timezone_tz'),
            (string) ilCalendarUserSettings::CAL_EXPORT_TZ_TZ
        );
        $export_type->addOption($export_tz);
        $export_utc = new ilRadioOption(
            $this->lng->txt('cal_export_timezone_utc'),
            (string) ilCalendarUserSettings::CAL_EXPORT_TZ_UTC
        );
        $export_type->addOption($export_utc);
        $form->addItem($export_type);

        $year = date("Y");
        $select = new ilSelectInputGUI($this->lng->txt('cal_user_date_format'), 'date_format');
        $select->setOptions(array(
            ilCalendarSettings::DATE_FORMAT_DMY => '31.10.' . $year,
            ilCalendarSettings::DATE_FORMAT_YMD => $year . "-10-31",
            ilCalendarSettings::DATE_FORMAT_MDY => "10/31/" . $year
        ));
        $select->setInfo($this->lng->txt('cal_date_format_info'));
        $select->setValue($this->user_settings->getDateFormat());
        $form->addItem($select);

        $select = new ilSelectInputGUI($this->lng->txt('cal_user_time_format'), 'time_format');
        $select->setOptions(array(
            ilCalendarSettings::TIME_FORMAT_24 => '13:00',
            ilCalendarSettings::TIME_FORMAT_12 => '1:00pm'
        ));
        $select->setInfo($this->lng->txt('cal_time_format_info'));
        $select->setValue($this->user_settings->getTimeFormat());
        $form->addItem($select);

        // Week/Month View
        $week_month = new ilFormSectionHeaderGUI();
        $week_month->setTitle($this->lng->txt('cal_week_month_view'));
        $form->addItem($week_month);

        $radio = new ilRadioGroupInputGUI($this->lng->txt('cal_week_start'), 'weekstart');
        $radio->setValue((string) $this->user_settings->getWeekStart());

        $option = new ilRadioOption($this->lng->txt('l_su'), "0");
        $radio->addOption($option);
        $option = new ilRadioOption($this->lng->txt('l_mo'), "1");
        $radio->addOption($option);
        $form->addItem($radio);

        if ($this->settings->getShowWeeks()) {
            //
            $cb = new ilCheckboxInputGUI($this->lng->txt("cal_usr_show_weeks"), "show_weeks");
            $cb->setInfo($this->lng->txt("cal_usr_show_weeks_info"));
            $cb->setValue("1");
            $cb->setChecked($this->user_settings->getShowWeeks());
            $form->addItem($cb);
        }

        // Day/Week View
        $week_month = new ilFormSectionHeaderGUI();
        $week_month->setTitle($this->lng->txt('cal_day_week_view'));
        $form->addItem($week_month);

        $day_start = new ilSelectInputGUI($this->lng->txt('cal_day_start'), 'dst');
        $day_start->setOptions(
            ilCalendarUtil::getHourSelection($this->user_settings->getTimeFormat())
        );
        $day_start->setValue($this->user_settings->getDayStart());
        $form->addItem($day_start);

        $day_end = new ilSelectInputGUI($this->lng->txt('cal_day_end'), 'den');
        $day_end->setOptions(
            ilCalendarUtil::getHourSelection($this->user_settings->getTimeFormat())
        );
        $day_end->setValue($this->user_settings->getDayEnd());
        $form->addItem($day_end);
        return $form;
    }
}
