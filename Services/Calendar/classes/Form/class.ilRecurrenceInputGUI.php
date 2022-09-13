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
 * This class represents an input GUI for recurring events/appointments (course events or calendar appointments)
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilRecurrenceInputGUI extends ilCustomInputGUI
{
    protected const REC_LIMITED = 2;
    protected const REC_UNLIMITED = 1;

    protected ilCalendarRecurrence $recurrence;
    protected ilObjUser $user;
    protected ilCalendarUserSettings $user_settings;

    protected bool $allow_unlimited_recurrences = true;

    protected array $enabled_subforms = array(
        ilCalendarRecurrence::FREQ_DAILY,
        ilCalendarRecurrence::FREQ_WEEKLY,
        ilCalendarRecurrence::FREQ_MONTHLY,
        ilCalendarRecurrence::FREQ_YEARLY
    );

    public function __construct(string $a_title, string $a_postvar)
    {
        global $DIC;

        $DIC->ui()->mainTemplate()->addJavaScript("./Services/Calendar/js/recurrence_input.js");
        $this->user = $DIC->user();
        $this->user_settings = ilCalendarUserSettings::_getInstanceByUserId($this->user->getId());
        $this->recurrence = new ilCalendarRecurrence();
        parent::__construct($a_title, $a_postvar);
        $this->lng->loadLanguageModule('dateplaner');
    }

    /**
     * @inheritDoc
     */
    public function checkInput(): bool
    {
        global $DIC;

        $lng = $DIC['lng'];

        if (!$this->loadRecurrence()) {
            return false;
        }

        if ($this->getRecurrenceInputByTypeAsString('frequence') === ilCalendarRecurrence::FREQ_NONE) {
            return true;
        }

        if (
            (
                $this->getRecurrenceInputByTypeAsInt('until_type') === 0 ||
                $this->getRecurrenceInputByTypeAsInt('until_type') == self::REC_LIMITED
            ) &&
            (
                $this->getRecurrenceInputByTypeAsInt('count') <= 0 ||
                $this->getRecurrenceInputByTypeAsInt('count') >= 100
            )
        ) {
            $this->setAlert($this->lng->txt("cal_rec_err_limit"));
            return false;
        }
        return true;
    }

    protected function getRecurrenceInputByTypeAsInt(string $input): int
    {
        if ($this->http->wrapper()->post()->has($input)) {
            return $this->http->wrapper()->post()->retrieve(
                $input,
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    protected function getRecurrenceInputByTypeAsString(string $input): string
    {
        if ($this->http->wrapper()->post()->has($input)) {
            return $this->http->wrapper()->post()->retrieve(
                $input,
                $this->refinery->kindlyTo()->string()
            );
        }
        return '';
    }

    protected function loadRecurrence(): bool
    {
        if (!$this->getRecurrence() instanceof ilCalendarRecurrence) {
            return false;
        }
        switch ($this->getRecurrenceInputByTypeAsString('frequence')) {
            case ilCalendarRecurrence::FREQ_DAILY:
                $this->getRecurrence()->setFrequenceType($this->getRecurrenceInputByTypeAsString('frequence'));
                $this->getRecurrence()->setInterval($this->getRecurrenceInputByTypeAsInt('count_DAILY'));
                break;

            case ilCalendarRecurrence::FREQ_WEEKLY:
                $this->getRecurrence()->setFrequenceType($this->getRecurrenceInputByTypeAsString('frequence'));
                $this->getRecurrence()->setInterval($this->getRecurrenceInputByTypeAsInt('count_WEEKLY'));

                $weekly_days = [];
                if ($this->http->wrapper()->post()->has('byday_WEEKLY')) {
                    $weekly_days = $this->http->wrapper()->post()->retrieve(
                        'byday_WEEKLY',
                        $this->refinery->kindlyTo()->dictOf(
                            $this->refinery->kindlyTo()->string()
                        )
                    );
                }
                if ($weekly_days !== []) {
                    $this->getRecurrence()->setBYDAY(implode(',', $weekly_days));
                }
                break;

            case ilCalendarRecurrence::FREQ_MONTHLY:
                $this->getRecurrence()->setFrequenceType($this->getRecurrenceInputByTypeAsString('frequence'));
                $this->getRecurrence()->setInterval($this->getRecurrenceInputByTypeAsInt('count_MONTHLY'));
                switch ($this->getRecurrenceInputByTypeAsInt('subtype_MONTHLY')) {
                    case 0:
                        // nothing to do;
                        break;
                    case 1:
                        switch ($this->getRecurrenceInputByTypeAsString('monthly_byday_day')) {
                            case '8':
                                // Weekday
                                $this->getRecurrence()->setBYSETPOS($this->getRecurrenceInputByTypeAsString('monthly_byday_num'));
                                $this->getRecurrence()->setBYDAY('MO,TU,WE,TH,FR');
                                break;

                            case '9':
                                // Day of month
                                $this->getRecurrence()->setBYMONTHDAY($this->getRecurrenceInputByTypeAsString('monthly_byday_num'));
                                break;

                            default:
                                $this->getRecurrence()->setBYDAY(
                                    $this->getRecurrenceInputByTypeAsString('monthly_byday_num') .
                                    $this->getRecurrenceInputByTypeAsString('monthly_byday_day')
                                );
                                break;
                        }
                        break;

                    case 2:
                        $this->getRecurrence()->setBYMONTHDAY($this->getRecurrenceInputByTypeAsString('monthly_bymonthday'));
                        break;
                }
                break;

            case ilCalendarRecurrence::FREQ_YEARLY:
                $this->getRecurrence()->setFrequenceType($this->getRecurrenceInputByTypeAsString('frequence'));
                $this->getRecurrence()->setInterval($this->getRecurrenceInputByTypeAsInt('count_YEARLY'));
                switch ($this->getRecurrenceInputByTypeAsInt('subtype_YEARLY')) {
                    case 0:
                        // nothing to do;
                        break;

                    case 1:
                        $this->getRecurrence()->setBYDAY(
                            $this->getRecurrenceInputByTypeAsString('yearly_byday_num') .
                            $this->getRecurrenceInputByTypeAsString('yearly_byday')
                        );
                        $this->getRecurrence()->setBYMONTH($this->getRecurrenceInputByTypeAsString('yearly_bymonth_byday'));
                        break;

                    case 2:
                        $this->getRecurrence()->setBYMONTH($this->getRecurrenceInputByTypeAsString('yearly_bymonthday'));
                        $this->getRecurrence()->setBYMONTHDAY($this->getRecurrenceInputByTypeAsString('yearly_bymonthday'));
                        break;
                }
                break;
        }

        // UNTIL
        switch ($this->getRecurrenceInputByTypeAsInt('until_type')) {
            case 1:
                $this->getRecurrence()->setFrequenceUntilDate(null);
                // nothing to do
                break;

            case 2:
                $this->getRecurrence()->setFrequenceUntilDate(null);
                $this->getRecurrence()->setFrequenceUntilCount($this->getRecurrenceInputByTypeAsInt('count'));
                break;

            case 3:
                $dt = new ilDateTimeInputGUI('', 'until_end');
                $dt->setRequired(true);
                if ($dt->checkInput()) {
                    $this->getRecurrence()->setFrequenceUntilCount(0);
                    $this->getRecurrence()->setFrequenceUntilDate($dt->getDate());
                } else {
                    return false;
                }
                break;
        }
        return true;
    }

    public function setRecurrence(ilCalendarRecurrence $a_rec): void
    {
        $this->recurrence = $a_rec;
    }

    public function getRecurrence(): ilCalendarRecurrence
    {
        return $this->recurrence;
    }

    /**
     * Allow unlimited recurrences
     */
    public function allowUnlimitedRecurrences(bool $a_status): void
    {
        $this->allow_unlimited_recurrences = $a_status;
    }

    public function isUnlimitedRecurrenceAllowed(): bool
    {
        return $this->allow_unlimited_recurrences;
    }

    /**
     * set enabled subforms
     * @param array(IL_CAL_FREQ_DAILY,FREQ_WEEKLY...)
     * @return void
     */
    public function setEnabledSubForms(array $a_sub_forms): void
    {
        $this->enabled_subforms = $a_sub_forms;
    }

    public function getEnabledSubForms(): array
    {
        return $this->enabled_subforms;
    }

    /**
     * @inheritDoc
     */
    public function insert(ilTemplate $a_tpl): void
    {
        $tpl = new ilTemplate('tpl.recurrence_input.html', true, true, 'Services/Calendar');

        $options = array('NONE' => $this->lng->txt('cal_no_recurrence'));
        if (in_array(ilCalendarRecurrence::FREQ_DAILY, $this->getEnabledSubForms())) {
            $options[ilCalendarRecurrence::FREQ_DAILY] = $this->lng->txt('cal_daily');
        }
        if (in_array(ilCalendarRecurrence::FREQ_WEEKLY, $this->getEnabledSubForms())) {
            $options[ilCalendarRecurrence::FREQ_WEEKLY] = $this->lng->txt('cal_weekly');
        }
        if (in_array(ilCalendarRecurrence::FREQ_MONTHLY, $this->getEnabledSubForms())) {
            $options[ilCalendarRecurrence::FREQ_MONTHLY] = $this->lng->txt('cal_monthly');
        }
        if (in_array(ilCalendarRecurrence::FREQ_YEARLY, $this->getEnabledSubForms())) {
            $options[ilCalendarRecurrence::FREQ_YEARLY] = $this->lng->txt('cal_yearly');
        }

        $tpl->setVariable(
            'FREQUENCE',
            ilLegacyFormElementsUtil::formSelect(
                $this->recurrence->getFrequenceType(),
                'frequence',
                $options,
                false,
                true,
                0,
                '',
                ['onchange' => 'ilHideFrequencies();', 'id' => 'il_recurrence_1']
            )
        );

        $tpl->setVariable('TXT_EVERY', $this->lng->txt('cal_every'));

        // DAILY
        if (in_array(ilCalendarRecurrence::FREQ_DAILY, $this->getEnabledSubForms())) {
            $tpl->setVariable('TXT_DAILY_FREQ_UNIT', $this->lng->txt('cal_day_s'));
            $tpl->setVariable('COUNT_DAILY_VAL', $this->recurrence->getInterval());
        }

        // WEEKLY
        if (in_array(ilCalendarRecurrence::FREQ_WEEKLY, $this->getEnabledSubForms())) {
            $tpl->setVariable('TXT_WEEKLY_FREQ_UNIT', $this->lng->txt('cal_week_s'));
            $tpl->setVariable('COUNT_WEEKLY_VAL', $this->recurrence->getInterval());
            $this->buildWeekDaySelection($tpl);
        }

        // MONTHLY
        if (in_array(ilCalendarRecurrence::FREQ_MONTHLY, $this->getEnabledSubForms())) {
            $tpl->setVariable('TXT_MONTHLY_FREQ_UNIT', $this->lng->txt('cal_month_s'));
            $tpl->setVariable('COUNT_MONTHLY_VAL', $this->recurrence->getInterval());
            $tpl->setVariable('TXT_ON_THE', $this->lng->txt('cal_on_the'));
            $tpl->setVariable('TXT_BYMONTHDAY', $this->lng->txt('cal_on_the'));
            $tpl->setVariable('TXT_OF_THE_MONTH', $this->lng->txt('cal_of_the_month'));
            $this->buildMonthlyByDaySelection($tpl);
            $this->buildMonthlyByMonthDaySelection($tpl);
        }

        // YEARLY
        if (in_array(ilCalendarRecurrence::FREQ_YEARLY, $this->getEnabledSubForms())) {
            $tpl->setVariable('TXT_YEARLY_FREQ_UNIT', $this->lng->txt('cal_year_s'));
            $tpl->setVariable('COUNT_YEARLY_VAL', $this->recurrence->getInterval());
            $tpl->setVariable('TXT_ON_THE', $this->lng->txt('cal_on_the'));
            $this->buildYearlyByMonthDaySelection($tpl);
            $this->buildYearlyByDaySelection($tpl);
        }

        // UNTIL
        $this->buildUntilSelection($tpl);

        $a_tpl->setCurrentBlock("prop_custom");
        $a_tpl->setVariable("CUSTOM_CONTENT", $tpl->get());
        $a_tpl->parseCurrentBlock();
    }

    /**
     * build weekday checkboxes
     */
    protected function buildWeekDaySelection(ilTemplate $tpl): void
    {
        $days = array(0 => 'SU', 1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU');

        $checked_days = array();
        foreach ($this->recurrence->getBYDAYList() as $byday) {
            if (in_array($byday, $days)) {
                $checked_days[] = $byday;
            }
        }

        for ($i = $this->user_settings->getWeekStart(); $i < 7 + $this->user_settings->getWeekStart(); $i++) {
            $tpl->setCurrentBlock('byday_simple');

            if (in_array($days[$i], $checked_days)) {
                $tpl->setVariable('BYDAY_WEEKLY_CHECKED', 'checked="checked"');
            }
            $tpl->setVariable('TXT_ON', $this->lng->txt('cal_on'));
            $tpl->setVariable('DAY_COUNT', $i);
            $tpl->setVariable('BYDAY_WEEKLY_VAL', $days[$i]);
            $tpl->setVariable('TXT_DAY_SHORT', ilCalendarUtil::_numericDayToString($i, false));
            $tpl->parseCurrentBlock();
        }
    }

    /**
     * build monthly by day list (e.g second monday)
     */
    protected function buildMonthlyByDaySelection(ilTemplate $tpl): void
    {
        $byday_list = $this->recurrence->getBYDAYList();
        $chosen_num_day = 1;
        $chosen_day = 'MO';
        $chosen = false;
        foreach ($byday_list as $byday) {
            if (preg_match('/^(-?\d)([A-Z][A-Z])/', $byday, $parsed) === 1) {
                $chosen = true;
                $chosen_num_day = $parsed[1];
                $chosen_day = $parsed[2];
            }
        }
        // check for last day
        if (count($this->recurrence->getBYMONTHDAYList()) == 1) {
            $bymonthday = $this->recurrence->getBYMONTHDAY();
            if (in_array($bymonthday, array(1, 2, 3, 4, 5, -1))) {
                $chosen = true;
                $chosen_num_day = $bymonthday;
                $chosen_day = 9;
            }
        }
        // Check for first, second... last weekday
        if (count($this->recurrence->getBYSETPOSList()) == 1) {
            $bysetpos = $this->recurrence->getBYSETPOS();
            if (in_array($bysetpos, array(1, 2, 3, 4, 5, -1))) {
                if ($this->recurrence->getBYDAYList() == array('MO', 'TU', 'WE', 'TH', 'FR')) {
                    $chosen = true;
                    $chosen_num_day = $bysetpos;
                    $chosen_day = 8;
                }
            }
        }

        if ($chosen) {
            $tpl->setVariable('M_BYDAY_CHECKED', 'checked="checked"');
        }

        $num_options = array(
            1 => $this->lng->txt('cal_first'),
            2 => $this->lng->txt('cal_second'),
            3 => $this->lng->txt('cal_third'),
            4 => $this->lng->txt('cal_fourth'),
            5 => $this->lng->txt('cal_fifth'),
            -1 => $this->lng->txt('cal_last')
        );

        $tpl->setVariable('SELECT_BYDAY_NUM_MONTHLY', ilLegacyFormElementsUtil::formSelect(
            $chosen_num_day,
            'monthly_byday_num',
            $num_options,
            false,
            true,
            0,
            '',
            array('onchange' => "ilUpdateSubTypeSelection('sub_monthly_radio_1');")
        ));

        $days = array(0 => 'SU', 1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU');

        for ($i = $this->user_settings->getWeekStart(); $i < 7 + $this->user_settings->getWeekStart(); $i++) {
            $days_select[$days[$i]] = ilCalendarUtil::_numericDayToString($i);
        }
        $days_select[8] = $this->lng->txt('cal_weekday');
        $days_select[9] = $this->lng->txt('cal_day_of_month');
        $tpl->setVariable('SEL_BYDAY_DAY_MONTHLY', ilLegacyFormElementsUtil::formSelect(
            $chosen_day,
            'monthly_byday_day',
            $days_select,
            false,
            true,
            0,
            '',
            array('onchange' => "ilUpdateSubTypeSelection('sub_monthly_radio_1');")
        ));
    }

    /**
     * build monthly bymonthday selection
     */
    protected function buildMonthlyByMonthDaySelection(ilTemplate $tpl): void
    {
        $tpl->setVariable('TXT_IN', $this->lng->txt('cal_in'));

        $chosen_day = 1;
        $chosen = false;
        if (count($bymonthday = $this->recurrence->getBYMONTHDAYList()) == 1) {
            foreach ($bymonthday as $mday) {
                if ($mday > 0 and $mday < 32) {
                    $chosen = true;
                    $chosen_day = $mday;
                }
            }
        }

        if ($chosen) {
            $tpl->setVariable('M_BYMONTHDAY_CHECKED', 'checked="checked"');
        }
        $options = [];
        for ($i = 1; $i < 32; $i++) {
            $options[$i] = $i;
        }
        $tpl->setVariable('SELECT_BYMONTHDAY', ilLegacyFormElementsUtil::formSelect(
            $chosen_day,
            'monthly_bymonthday',
            $options,
            false,
            true,
            0,
            '',
            array('onchange' => "ilUpdateSubTypeSelection('sub_monthly_radio_2');")
        ));
    }

    protected function buildYearlyByMonthDaySelection(ilTemplate $tpl): void
    {
        $tpl->setVariable('TXT_Y_EVERY', $this->lng->txt('cal_every'));

        $chosen = false;
        $chosen_month = 1;
        $chosen_day = 1;
        foreach ($this->recurrence->getBYMONTHList() as $month) {
            if ($this->recurrence->getBYMONTHDAYList()) {
                $chosen_month = $month;
                $chosen = true;
                break;
            }
        }
        foreach ($this->recurrence->getBYMONTHDAYList() as $day) {
            $chosen_day = $day;
        }
        $options = [];
        for ($i = 1; $i < 32; $i++) {
            $options[$i] = $i;
        }
        $tpl->setVariable('SELECT_BYMONTHDAY_NUM_YEARLY', ilLegacyFormElementsUtil::formSelect(
            $chosen_day,
            'yearly_bymonthday',
            $options,
            false,
            true,
            0,
            '',
            array('onchange' => "ilUpdateSubTypeSelection('sub_yearly_radio_2');")
        ));

        $options = array();
        for ($m = 1; $m < 13; $m++) {
            $options[$m] = ilCalendarUtil::_numericMonthToString($m);
        }
        $tpl->setVariable('SELECT_BYMONTH_YEARLY', ilLegacyFormElementsUtil::formSelect(
            $chosen_month,
            'yearly_bymonth_by_monthday',
            $options,
            false,
            true,
            0,
            '',
            array('onchange' => "ilUpdateSubTypeSelection('sub_yearly_radio_2');")
        ));

        if ($chosen) {
            $tpl->setVariable('Y_BYMONTHDAY_CHECKED', 'checked="checked"');
        }
    }

    protected function buildYearlyByDaySelection(ilTemplate $tpl): void
    {
        $tpl->setVariable('TXT_ON_THE', $this->lng->txt('cal_on_the'));

        $chosen_num_day = 1;
        $chosen_day = 'MO';
        $chosen = false;
        foreach ($this->recurrence->getBYDAYList() as $byday) {
            if (preg_match('/^(-?\d)([A-Z][A-Z])/', $byday, $parsed) === 1) {
                $chosen = true;
                $chosen_num_day = $parsed[1];
                $chosen_day = $parsed[2];
            }
        }

        $num_options = array(
            1 => $this->lng->txt('cal_first'),
            2 => $this->lng->txt('cal_second'),
            3 => $this->lng->txt('cal_third'),
            4 => $this->lng->txt('cal_fourth'),
            5 => $this->lng->txt('cal_fifth'),
            -1 => $this->lng->txt('cal_last')
        );

        $tpl->setVariable('SELECT_BYDAY_NUM_YEARLY', ilLegacyFormElementsUtil::formSelect(
            $chosen_num_day,
            'yearly_byday_num',
            $num_options,
            false,
            true,
            0,
            '',
            array('onchange' => "ilUpdateSubTypeSelection('sub_yearly_radio_1');")
        ));

        $days = array(0 => 'SU', 1 => 'MO', 2 => 'TU', 3 => 'WE', 4 => 'TH', 5 => 'FR', 6 => 'SA', 7 => 'SU');
        $days_select = [];
        for ($i = $this->user_settings->getWeekStart(); $i < 7 + $this->user_settings->getWeekStart(); $i++) {
            $days_select[$days[$i]] = ilCalendarUtil::_numericDayToString($i);
        }
        $tpl->setVariable('SELECT_BYDAY_DAY_YEARLY', ilLegacyFormElementsUtil::formSelect(
            $chosen_day,
            'yearly_byday',
            $days_select,
            false,
            true,
            0,
            '',
            array('onchange' => "ilUpdateSubTypeSelection('sub_yearly_radio_1');")
        ));

        $chosen = false;
        $chosen_month = 1;
        foreach ($this->recurrence->getBYMONTHList() as $month) {
            if ($this->recurrence->getBYMONTHDAYList()) {
                $chosen_month = $month;
                $chosen = true;
                break;
            }
        }
        $options = array();
        for ($m = 1; $m < 13; $m++) {
            $options[$m] = ilCalendarUtil::_numericMonthToString($m);
        }
        $tpl->setVariable('SELECT_BYMONTH_BYDAY', ilLegacyFormElementsUtil::formSelect(
            $chosen_month,
            'yearly_bymonth_byday',
            $options,
            false,
            true,
            0,
            '',
            array('onchange' => "ilUpdateSubTypeSelection('sub_yearly_radio_1');")
        ));
    }

    /**
     * build selection for ending date
     */
    protected function buildUntilSelection(ilTemplate $tpl): void
    {
        if ($this->isUnlimitedRecurrenceAllowed()) {
            $tpl->setVariable('TXT_NO_ENDING', $this->lng->txt('cal_no_ending'));
        }

        $tpl->setVariable('TXT_UNTIL_CREATE', $this->lng->txt('cal_create'));
        $tpl->setVariable('TXT_APPOINTMENTS', $this->lng->txt('cal_appointments'));

        $tpl->setVariable('VAL_COUNT', $this->recurrence->getFrequenceUntilCount() ?:
            2);

        if ($this->recurrence->getFrequenceUntilDate()) {
            $tpl->setVariable('UNTIL_END_CHECKED', 'checked="checked"');
        } elseif ($this->recurrence->getFrequenceUntilCount() or !$this->isUnlimitedRecurrenceAllowed()) {
            $tpl->setVariable('UNTIL_COUNT_CHECKED', 'checked="checked"');
        } else {
            $tpl->setVariable('UNTIL_NO_CHECKED', 'checked="checked"');
        }

        $tpl->setVariable('TXT_UNTIL_END', $this->lng->txt('cal_repeat_until'));
        $dt = new ilDateTimeInputGUI('', 'until_end');
        // no proper subform
        // $dt->setRequired(true);
        $dt->setDate(
            $this->recurrence->getFrequenceUntilDate() ?: null
        );
        $tpl->setVariable('UNTIL_END_DATE', $dt->getTableFilterHTML());
    }
}
