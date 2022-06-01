<?php declare(strict_types=1);

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
 * This class represents a date/time property in a property form.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDateTimeInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem
{
    protected ilObjUser $user;
    protected ?ilDateTime $date = null;
    protected string $time = "00:00:00";
    protected bool $showtime = false;
    protected bool $showseconds = false;
    protected int $minute_step_size = 5;
    protected ?int $startyear = null;
    protected string $invalid_input = '';
    protected bool $side_by_side = true;
    protected bool $valid = false;

    public function __construct(
        string $a_title = "",
        string $a_postvar = ""
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        parent::__construct($a_title, $a_postvar);
        $this->setType("datetime");
    }

    /**
     * set date
     * E.g	$dt_form->setDate(new ilDateTime(time(),IL_CAL_UTC));
     * or 	$dt_form->setDate(new ilDateTime('2008-06-12 08:00:00',IL_CAL_DATETIME));
     *
     * For fullday (no timezone conversion) events use:
     *
     * 		$dt_form->setDate(new ilDate('2008-08-01',IL_CAL_DATE));
     */
    public function setDate(ilDateTime $a_date = null) : void
    {
        $this->date = $a_date;
    }

    public function getDate() : ?ilDateTime
    {
        return $this->date;
    }

    public function setShowTime(bool $a_showtime) : void
    {
        $this->showtime = $a_showtime;
    }

    public function getShowTime() : bool
    {
        return $this->showtime;
    }
    
    public function setStartYear(int $a_year) : void
    {
        $this->startyear = $a_year;
    }
    
    public function getStartYear() : ?int
    {
        return $this->startyear;
    }
    
    /**
     * Set minute step size
     * E.g 5 => The selection will only show 00,05,10... minutes
     * @param int $a_step_size minute step_size 1,5,10,15,20...
     */
    public function setMinuteStepSize(int $a_step_size) : void
    {
        $this->minute_step_size = $a_step_size;
    }
    
    public function getMinuteStepSize() : int
    {
        return $this->minute_step_size;
    }

    public function setShowSeconds(bool $a_showseconds) : void
    {
        $this->showseconds = $a_showseconds;
    }

    public function getShowSeconds() : bool
    {
        return $this->showseconds;
    }

    public function setValueByArray(array $a_values) : void
    {
        $incoming = $a_values[$this->getPostVar()] ?? "";
        $this->setDate(ilCalendarUtil::parseIncomingDate($incoming, (bool) $this->getDatePickerTimeFormat()));
                
        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }
    
    protected function getDatePickerTimeFormat() : int
    {
        return (int) $this->getShowTime() + (int) $this->getShowSeconds();
    }
    
    public function hasInvalidInput() : bool
    {
        return (bool) $this->invalid_input;
    }

    public function checkInput() : bool
    {
        $lng = $this->lng;
        
        if ($this->getDisabled()) {
            return true;
        }

        $post = $this->str($this->getPostVar());
        
        // always done to make sure there are no obsolete values left
        $this->setDate(null);
        
        $valid = false;
        if (trim($post)) {
            $parsed = ilCalendarUtil::parseIncomingDate($post, (bool) $this->getDatePickerTimeFormat());
            if ($parsed) {
                $this->setDate($parsed);
                $valid = true;
            }
        } elseif (!$this->getRequired()) {
            $valid = true;
        }
        
        if ($valid &&
            $this->getDate() &&
            $this->getStartYear() &&
            $this->getDate()->get(IL_CAL_FKT_DATE, "Y") < $this->getStartYear()) {
            $valid = false;
        }

        $this->valid = $valid;

        if (!$valid) {
            $this->invalid_input = $post;
            $this->setAlert($lng->txt("form_msg_wrong_date"));
        }
        
        if ($valid) {
            $valid = $this->checkSubItemsInput();
        }
        
        return $valid;
    }

    public function getInput() : ?string
    {
        if ($this->valid && $this->getDate() !== null) {
            // getInput() should return a generic format
            $post_format = $this->getShowTime()
                ? IL_CAL_DATETIME
                : IL_CAL_DATE;
            return $this->getDate()->get($post_format);
        }
        return null;
    }

    public function setSideBySide(bool $a_val) : void
    {
        $this->side_by_side = $a_val;
    }

    public function getSideBySide() : bool
    {
        return $this->side_by_side;
    }

    protected function parseDatePickerConfig() : array
    {
        $config = null;
        if ($this->getMinuteStepSize()) {
            $config['stepping'] = $this->getMinuteStepSize();
        }
        if ($this->getStartYear()) {
            $config['minDate'] = $this->getStartYear() . '-01-01';
        }
        $config['sideBySide'] = $this->getSideBySide();
        return $config;
    }

    public function render() : string
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        
        $tpl = new ilTemplate("tpl.prop_datetime.html", true, true, "Services/Form");

        // config picker
        if (!$this->getDisabled()) {
            $picker_id = md5($this->getPostVar()); // :TODO: unique?
            $tpl->setVariable('DATEPICKER_ID', $picker_id);
            
            ilCalendarUtil::addDateTimePicker(
                $picker_id,
                $this->getDatePickerTimeFormat(),
                $this->parseDatePickerConfig(),
                null,
                null,
                null,
                "subform_" . $this->getPostVar()
            );
        } else {
            $tpl->setVariable('DATEPICKER_DISABLED', 'disabled="disabled" ');
        }
        
        // :TODO: i18n?
        $pl_format = ilCalendarUtil::getUserDateFormat($this->getDatePickerTimeFormat());
        $tpl->setVariable('PLACEHOLDER', $pl_format);

        // accessibility description
        $tpl->setVariable(
            'DESCRIPTION',
            ilLegacyFormElementsUtil::prepareFormOutput($lng->txt("form_date_aria_desc") . " " . $pl_format)
        );
        
        // current value
        $date_value = htmlspecialchars($this->invalid_input);
        if (!$date_value &&
            $this->getDate()) {
            $out_format = ilCalendarUtil::getUserDateFormat($this->getDatePickerTimeFormat(), true);
            $date_value = $this->getDate()->get(IL_CAL_FKT_DATE, $out_format, $ilUser->getTimeZone());
        }

        $tpl->setVariable('DATEPICKER_VALUE', $date_value);
        $tpl->setVariable('DATE_ID', $this->getPostVar());
        
        if ($this->getRequired()) {
            $tpl->setVariable("REQUIRED", "required=\"required\"");
        }
        
        return $tpl->get();
    }

    public function getOnloadCode() : array
    {
        $code = [];
        if (!$this->getDisabled()) {
            $picker_id = md5($this->getPostVar());

            $code = ilCalendarUtil::getCodeForPicker(
                $picker_id,
                $this->getDatePickerTimeFormat(),
                $this->parseDatePickerConfig(),
                null,
                null,
                null,
                "subform_" . $this->getPostVar()
            );
        }
        return $code;
    }

    public function insert(ilTemplate $a_tpl) : void
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    public function getTableFilterHTML() : string
    {
        $html = $this->render();
        return $html;
    }

    public function serializeData() : string
    {
        if ($this->getDate()) {
            return serialize($this->getDate()->get(IL_CAL_UNIX));
        }
        return "";
    }
    
    public function unserializeData(string $a_data) : void
    {
        $tmp = unserialize($a_data);
        if ($tmp) {
            // we used to serialize the complete instance
            if (is_object($tmp)) {
                $date = $tmp;
            } else {
                $date = $this->getShowTime()
                    ? new ilDateTime($tmp, IL_CAL_UNIX)
                    : new ilDate($tmp, IL_CAL_UNIX);
            }
            $this->setDate($date);
        } else {
            $this->setDate();
        }
    }

    public function getPostValueForComparison() : string
    {
        return trim($this->str($this->getPostVar()));
    }
    
    public function getToolbarHTML() : string
    {
        $html = $this->render();
        return $html;
    }
    
    public function hideSubForm() : bool
    {
        return (!$this->getDate() || $this->getDate()->isNull());
    }
}
