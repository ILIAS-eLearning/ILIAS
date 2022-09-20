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
 * input GUI for a time span (start and end date)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilDateDurationInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem
{
    protected ilObjUser $user;

    protected ?ilDateTime $start = null;
    protected ?int $startyear = null;
    protected string $start_text = "";
    protected string $end_text = "";
    protected int $minute_step_size = 5;
    protected ?ilDateTime $end = null;
    protected bool $showtime = false;
    protected bool $toggle_fulltime = false;
    protected string $toggle_fulltime_txt = '';
    protected bool $toggle_fulltime_checked = false;
    protected bool $allowOpenIntervals = false;
    protected string $invalid_input_start = '';
    protected string $invalid_input_end = '';

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        parent::__construct($a_title, $a_postvar);
        $this->setType("dateduration");
    }

    // Enable toggling between date and time
    public function enableToggleFullTime(
        string $a_title,
        bool $a_checked
    ): void {
        $this->toggle_fulltime_txt = $a_title;
        $this->toggle_fulltime_checked = $a_checked;
        $this->toggle_fulltime = true;
    }

    public function enabledToggleFullTime(): bool
    {
        return $this->toggle_fulltime;
    }

    /**
     * Set start date
     * E.g	$dt_form->setDate(new ilDateTime(time(),IL_CAL_UTC));
     * or 	$dt_form->setDate(new ilDateTime('2008-06-12 08:00:00',IL_CAL_DATETIME));
     *
     * For fullday (no timezone conversion) events use:
     *
     * 		$dt_form->setDate(new ilDate('2008-08-01',IL_CAL_DATE));
     */
    public function setStart(ilDateTime $a_date = null): void
    {
        $this->start = $a_date;
    }

    public function setStartText(string $a_txt): void
    {
        $this->start_text = $a_txt;
    }

    public function getStartText(): string
    {
        return $this->start_text;
    }

    public function setEndText(string $a_txt): void
    {
        $this->end_text = $a_txt;
    }

    public function getEndText(): string
    {
        return $this->end_text;
    }

    public function getStart(): ?ilDateTime
    {
        return $this->start;
    }

    /**
     * Set end date
     * E.g	$dt_form->setDate(new ilDateTime(time(),IL_CAL_UTC));
     * or 	$dt_form->setDate(new ilDateTime('2008-06-12 08:00:00',IL_CAL_DATETIME));
     *
     * For fullday (no timezone conversion) events use:
     *
     * 		$dt_form->setDate(new ilDate('2008-08-01',IL_CAL_DATE));
     */
    public function setEnd(ilDateTime $a_date = null): void
    {
        $this->end = $a_date;
    }

    public function getEnd(): ?ilDateTime
    {
        return $this->end;
    }

    public function setShowTime(bool $a_showtime): void
    {
        $this->showtime = $a_showtime;
    }

    public function getShowTime(): bool
    {
        return $this->showtime;
    }

    public function getShowSeconds(): bool
    {
        return false;
    }

    public function setStartYear(int $a_year): void
    {
        $this->startyear = $a_year;
    }

    public function getStartYear(): ?int
    {
        return $this->startyear;
    }

    /**
     * Set minute step size
     * E.g 5 => The selection will only show 00,05,10... minutes
     * @param int $a_step_size minute step_size 1,5,10,15,20...
     */
    public function setMinuteStepSize(int $a_step_size): void
    {
        $this->minute_step_size = $a_step_size;
    }

    public function getMinuteStepSize(): int
    {
        return $this->minute_step_size;
    }

    public function setValueByArray(array $a_values): void
    {
        $incoming = $a_values[$this->getPostVar()] ?? [];
        if (is_array($incoming) && $incoming !== []) {
            $format = isset($incoming['tgl']) ? 0 : $this->getDatePickerTimeFormat();
            $this->toggle_fulltime_checked = (bool) ($incoming['tgl'] ?? false);

            if ($this->openIntervalsAllowed()) {
                if (isset($incoming['start']) && is_string($incoming['start']) && trim($incoming['start']) !== '') {
                    $this->setStart(ilCalendarUtil::parseIncomingDate($incoming["start"], (bool) $format));
                } else {
                    $this->setStart(new ilDate(null, IL_CAL_UNIX));
                }

                if (isset($incoming['end']) && is_string($incoming['end']) && trim($incoming['end']) !== '') {
                    $this->setEnd(ilCalendarUtil::parseIncomingDate($incoming["end"], (bool) $format));
                } else {
                    $this->setEnd(new ilDate(null, IL_CAL_UNIX));
                }
            } else {
                # 0033160
                if ($incoming['start'] instanceof ilDateTime) {
                    $this->setStart($incoming['start']);
                } else {
                    $this->setStart(ilCalendarUtil::parseIncomingDate((string) $incoming["start"], (bool) $format));
                }
                if ($incoming['end'] instanceof ilDateTime) {
                    $this->setEnd($incoming['end']);
                } else {
                    $this->setEnd(ilCalendarUtil::parseIncomingDate((string) $incoming["end"], (bool) $format));
                }
            }
        }

        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }

    public function checkInput(): bool
    {
        $lng = $this->lng;

        if ($this->getDisabled()) {
            return true;
        }

        $post = $this->strArray($this->getPostVar());

        $start = $post["start"];
        $end = $post["end"];

        // if full day is active, ignore time format
        $format = isset($post['tgl'])
            ? 0
            : $this->getDatePickerTimeFormat();

        // always done to make sure there are no obsolete values left
        $this->setStart();
        $this->setEnd();

        $valid_start = false;
        if (trim($start)) {
            $parsed = ilCalendarUtil::parseIncomingDate($start, (bool) $format);
            if ($parsed) {
                $this->setStart($parsed);
                $valid_start = true;
            }
        } else {
            if (!$this->getRequired() && !trim($end)) {
                $valid_start = true;
            } else {
                if ($this->openIntervalsAllowed() && !strlen(trim($start))) {
                    $valid_start = true;
                }
            }
        }

        $valid_end = false;
        if (trim($end)) {
            $parsed = ilCalendarUtil::parseIncomingDate($end, (bool) $format);
            if ($parsed) {
                $this->setEnd($parsed);
                $valid_end = true;
            }
        } else {
            if (!$this->getRequired() && !trim($start)) {
                $valid_end = true;
            } else {
                if ($this->openIntervalsAllowed() && !strlen(trim($end))) {
                    $valid_end = true;
                }
            }
        }

        if ($this->getStartYear()) {
            if ($valid_start &&
                $this->getStart()->get(IL_CAL_FKT_DATE, "Y") < $this->getStartYear()) {
                $valid_start = false;
            }
            if ($valid_end &&
                $this->getEnd()->get(IL_CAL_FKT_DATE, "Y") < $this->getStartYear()) {
                $valid_end = false;
            }
        }
        $valid = ($valid_start && $valid_end);

        if ($valid &&
            $this->getStart() &&
            $this->getEnd() &&
            ilDateTime::_after($this->getStart(), $this->getEnd())) {
            $valid = false;
        }

        if ($this->openIntervalsAllowed()) {
            $valid = true;
        } elseif (!$valid) {
            $this->invalid_input_start = $start;
            $this->invalid_input_end = $end;
            $this->setAlert($lng->txt("form_msg_wrong_date"));
        }

        if ($valid) {
            $valid = $this->checkSubItemsInput();
        }

        return $valid;
    }

    public function getInput(): array
    {
        $ret = $this->strArray($this->getPostVar());

        if ($this->openIntervalsAllowed()) {
            if (!$this->getStart()) {
                $ret["start"] = null;
            }

            if (!$this->getEnd()) {
                $ret["end"] = null;
            }
        } else {
            if (
                !$this->getStart() ||
                !$this->getEnd()
            ) {
                $ret["start"] = null;
                $ret["end"] = null;
            }
        }
        return $ret;
    }

    protected function getDatePickerTimeFormat(): int
    {
        return (int) $this->getShowTime() + (int) $this->getShowSeconds();
    }

    /**
     * parse properties to datepicker config
     */
    protected function parseDatePickerConfig(): array
    {
        $config = null;
        if ($this->getMinuteStepSize()) {
            $config['stepping'] = $this->getMinuteStepSize();
        }
        if ($this->getStartYear()) {
            $config['minDate'] = $this->getStartYear() . '-01-01';
        }
        return $config;
    }

    public function render(): string
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $toggle_id = null;

        $tpl = new ilTemplate("tpl.prop_datetime_duration.html", true, true, "Services/Form");

        if ($this->enabledToggleFullTime()) {
            $this->setShowTime(true);

            $toggle_id = md5($this->getPostVar() . '_fulltime'); // :TODO: unique?

            $tpl->setCurrentBlock('toggle_fullday');
            $tpl->setVariable('DATE_TOGGLE_ID', $this->getPostVar() . '[tgl]');
            $tpl->setVariable('FULLDAY_TOGGLE_ID', $toggle_id);
            $tpl->setVariable('FULLDAY_TOGGLE_CHECKED', $this->toggle_fulltime_checked ? 'checked="checked"' : '');
            $tpl->setVariable('FULLDAY_TOGGLE_DISABLED', $this->getDisabled() ? 'disabled="disabled"' : '');
            $tpl->setVariable('TXT_TOGGLE_FULLDAY', $this->toggle_fulltime_txt);
            $tpl->parseCurrentBlock();
        }

        // config picker
        if (!$this->getDisabled()) {
            // :TODO: unique?
            $picker_start_id = md5($this->getPostVar() . '_start');
            $picker_end_id = md5($this->getPostVar() . '_end');

            $tpl->setVariable('DATEPICKER_START_ID', $picker_start_id);
            $tpl->setVariable('DATEPICKER_END_ID', $picker_end_id);

            ilCalendarUtil::addDateTimePicker(
                $picker_start_id,
                $this->getDatePickerTimeFormat(),
                $this->parseDatePickerConfig(),
                $picker_end_id,
                $this->parseDatePickerConfig(),
                $toggle_id,
                "subform_" . $this->getPostVar()
            );
        } else {
            $tpl->setVariable('DATEPICKER_START_DISABLED', 'disabled="disabled" ');
            $tpl->setVariable('DATEPICKER_END_DISABLED', 'disabled="disabled" ');
        }

        $start_txt = $this->getStartText();
        if ($start_txt === null) {
            $start_txt = $lng->txt("form_date_duration_start");
        }
        if (trim($start_txt)) {
            $tpl->setVariable('START_LABEL', $start_txt);
            $tpl->setVariable('START_ARIA_LABEL', ilLegacyFormElementsUtil::prepareFormOutput($start_txt));
            $tpl->touchBlock('start_width_bl');
        }

        $end_txt = $this->getEndText();
        if ($end_txt === null) {
            $end_txt = $lng->txt("form_date_duration_end");
        }
        if (trim($end_txt)) {
            $tpl->setVariable('END_LABEL', $end_txt);
            $tpl->setVariable('END_ARIA_LABEL', ilLegacyFormElementsUtil::prepareFormOutput($end_txt));
            $tpl->touchBlock('end_width_bl');
        }


        $tpl->setVariable('DATE_START_ID', $this->getPostVar() . '[start]');
        $tpl->setVariable('DATE_END_ID', $this->getPostVar() . '[end]');

        // placeholder
        // :TODO: i18n?
        $pl_format = ilCalendarUtil::getUserDateFormat($this->getDatePickerTimeFormat());
        $tpl->setVariable('START_PLACEHOLDER', $pl_format);
        $tpl->setVariable('END_PLACEHOLDER', $pl_format);

        // accessibility description
        $tpl->setVariable(
            'DESCRIPTION',
            ilLegacyFormElementsUtil::prepareFormOutput($lng->txt("form_date_aria_desc") . " " . $pl_format)
        );


        // values

        $date_value = htmlspecialchars($this->invalid_input_start);
        if (!$date_value &&
            $this->getStart()) {
            $out_format = ilCalendarUtil::getUserDateFormat($this->getDatePickerTimeFormat(), true);
            $date_value = $this->getStart()->get(IL_CAL_FKT_DATE, $out_format, $ilUser->getTimeZone());
        }
        $tpl->setVariable('DATEPICKER_START_VALUE', $date_value);

        $date_value = htmlspecialchars($this->invalid_input_end);
        if (!$date_value &&
            $this->getEnd()) {
            $out_format = ilCalendarUtil::getUserDateFormat($this->getDatePickerTimeFormat(), true);
            $date_value = $this->getEnd()->get(IL_CAL_FKT_DATE, $out_format, $ilUser->getTimeZone());
        }
        $tpl->setVariable('DATEPICKER_END_VALUE', $date_value);

        if ($this->getRequired()) {
            $tpl->setVariable("START_REQUIRED", "required=\"required\"");
            $tpl->setVariable("END_REQUIRED", "required=\"required\"");
        }

        return $tpl->get();
    }


    public function insert(ilTemplate $a_tpl): void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    public function getTableFilterHTML(): string
    {
        return $this->render();
    }

    public function getValue(): array
    {
        return array(
            'start' => $this->getStart() ? $this->getStart()->get(IL_CAL_UNIX) : null,
            'end' => $this->getEnd() ? $this->getEnd()->get(IL_CAL_UNIX) : null
        );
    }

    /**
     * Called from table gui with the stored session value
     * Attention: If the user resets the table filter, a boolean false is passed by the table gui
     * @param array|bool $value
     * @throws ilDateTimeException
     */
    public function setValue($value): void
    {
        if (is_array($value)) {
            $this->setStart(new ilDateTime($value['start'], IL_CAL_UNIX));
            $this->setEnd(new ilDateTime($value['end'], IL_CAL_UNIX));
        }
    }

    public function hideSubForm(): bool
    {
        if ($this->invalid_input_start ||
            $this->invalid_input_end) {
            return false;
        }

        return ((!$this->getStart() || $this->getStart()->isNull()) &&
            (!$this->getEnd() || $this->getEnd()->isNull()));
    }

    public function openIntervalsAllowed(): bool
    {
        return $this->allowOpenIntervals;
    }

    public function setAllowOpenIntervals(bool $allowOpenInterval): void
    {
        $this->allowOpenIntervals = $allowOpenInterval;
    }

    public function getTableFilterLabelFor(): string
    {
        return $this->getFieldId() . "[start]";
    }

    public function getFormLabelFor(): string
    {
        return $this->getFieldId() . "[start]";
    }
}
