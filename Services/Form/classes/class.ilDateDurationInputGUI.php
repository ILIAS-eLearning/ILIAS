<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Table/interfaces/interface.ilTableFilterItem.php';

/**
* input GUI for a time span (start and end date)
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesForm
*/
class ilDateDurationInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    protected $start = null;
    protected $startyear = null;
    protected $start_text = null;
    protected $end_text = null;
    protected $minute_step_size = 5;
    protected $end = null;
    protected $showtime = false;
    protected $toggle_fulltime = false;
    protected $toggle_fulltime_txt = '';
    protected $toggle_fulltime_checked = false;
    protected $allowOpenIntervals = false;

    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        parent::__construct($a_title, $a_postvar);
        $this->setType("dateduration");
    }
    
    /**
     * Enable toggling between date and time
     * @param object $a_title
     * @param object $a_checked
     * @return
     */
    public function enableToggleFullTime($a_title, $a_checked)
    {
        $this->toggle_fulltime_txt = $a_title;
        $this->toggle_fulltime_checked = $a_checked;
        $this->toggle_fulltime = true;
    }
    
    /**
     * Check if toggling between date and time enabled
     * @return
     */
    public function enabledToggleFullTime()
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
    *
    * @param	object	$a_date	ilDate or ilDateTime  object
    */
    public function setStart(ilDateTime $a_date = null)
    {
        $this->start = $a_date;
    }
    
    /**
     * Set text, which will be shown before the start date
     * @param object $a_txt
     * @return
     */
    public function setStartText($a_txt)
    {
        $this->start_text = $a_txt;
    }
    
    /**
     * get start text
     * @return
     */
    public function getStartText()
    {
        return $this->start_text;
    }

    /**
     * Set text, which will be shown before the end date
     * @param object $a_txt
     * @return
     */
    public function setEndText($a_txt)
    {
        $this->end_text = $a_txt;
    }
    
    /**
     * Get end text
     * @return
     */
    public function getEndText()
    {
        return $this->end_text;
    }

    /**
    * Get Date, yyyy-mm-dd.
    *
    * @return	object	Date, yyyy-mm-dd
    */
    public function getStart()
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
    *
    * @param	object	$a_date	ilDate or ilDateTime  object
    */
    public function setEnd(ilDateTime $a_date = null)
    {
        $this->end = $a_date;
    }

    /**
    * Get Date, yyyy-mm-dd.
    *
    * @return	object	Date, yyyy-mm-dd
    */
    public function getEnd()
    {
        return $this->end;
    }
    
    /**
    * Set Show Time Information.
    *
    * @param	boolean	$a_showtime	Show Time Information
    */
    public function setShowTime($a_showtime)
    {
        $this->showtime = $a_showtime;
    }

    /**
    * Get Show Time Information.
    *
    * @return	boolean	Show Time Information
    */
    public function getShowTime()
    {
        return $this->showtime;
    }
    
    /**
     * Show seconds not implemented yet
     * @return
     */
    public function getShowSeconds()
    {
        return false;
    }
    
    /**
    * Set start year
    *
    * @param	integer	Start year
    */
    public function setStartYear($a_year)
    {
        $this->startyear = $a_year;
    }
    
    /**
    * Get start year
    *
    * @return	integer	Start year
    */
    public function getStartYear()
    {
        return $this->startyear;
    }
    
    /**
     * Set minute step size
     * E.g 5 => The selection will only show 00,05,10... minutes
     *
     * @access public
     * @param int minute step_size 1,5,10,15,20...
     *
     */
    public function setMinuteStepSize($a_step_size)
    {
        $this->minute_step_size = $a_step_size;
    }
    
    /**
     * Get minute step size
     *
     * @access public
     *
     */
    public function getMinuteStepSize()
    {
        return $this->minute_step_size;
    }
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $incoming = $a_values[$this->getPostVar()];
        if (is_array($incoming)) {
            $format = $incoming['tgl'] ? 0 : $this->getDatePickerTimeFormat();
            $this->toggle_fulltime_checked = (bool) $incoming['tgl'];

            if ($this->openIntervalsAllowed()) {
                if (is_string($incoming['start']) && trim($incoming['start']) !== '') {
                    $this->setStart(ilCalendarUtil::parseIncomingDate($incoming["start"], $format));
                } else {
                    $this->setStart(new ilDate(null, IL_CAL_UNIX));
                }

                if (is_string($incoming['end']) && trim($incoming['end']) !== '') {
                    $this->setEnd(ilCalendarUtil::parseIncomingDate($incoming["end"], $format));
                } else {
                    $this->setEnd(new ilDate(null, IL_CAL_UNIX));
                }
            } else {
                $this->setStart(ilCalendarUtil::parseIncomingDate($incoming["start"], $format));
                $this->setEnd(ilCalendarUtil::parseIncomingDate($incoming["end"], $format));
            }
        }

        foreach ($this->getSubItems() as $item) {
            $item->setValueByArray($a_values);
        }
    }
    
    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        $lng = $this->lng;
        
        if ($this->getDisabled()) {
            return true;
        }
        
        $post = $_POST[$this->getPostVar()];
        if (!is_array($post)) {
            return false;
        }
        
        $start = $post["start"];
        $end = $post["end"];
        
        // if full day is active, ignore time format
        $format = $post['tgl']
            ? 0
            : $this->getDatePickerTimeFormat();
        
        // always done to make sure there are no obsolete values left
        $this->setStart(null);
        $this->setEnd(null);

        $valid_start = false;
        if (trim($start)) {
            $parsed = ilCalendarUtil::parseIncomingDate($start, $format);
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
            $parsed = ilCalendarUtil::parseIncomingDate($end, $format);
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
            if (!$this->getStart()) {
                $_POST[$this->getPostVar()]["start"] = null;
            }

            if (!$this->getEnd()) {
                $_POST[$this->getPostVar()]["end"] = null;
            }
            $valid = true;
        } elseif (!$valid) {
            $this->invalid_input_start = $start;
            $this->invalid_input_end = $end;

            $_POST[$this->getPostVar()]["start"] = null;
            $_POST[$this->getPostVar()]["end"] = null;

            $this->setAlert($lng->txt("form_msg_wrong_date"));
        } else {
            if (
                !$this->getStart() ||
                !$this->getEnd()
            ) {
                $_POST[$this->getPostVar()]["start"] = null;
                $_POST[$this->getPostVar()]["end"] = null;
            }
        }

        if ($valid) {
            $valid = $this->checkSubItemsInput();
        }
        
        return $valid;
    }
    
    protected function getDatePickerTimeFormat()
    {
        return (int) $this->getShowTime() + (int) $this->getShowSeconds();
    }
    
    /**
     * parse properties to datepicker config
     *
     * @return array
     */
    protected function parseDatePickerConfig()
    {
        $config = null;
        if ($this->getMinuteStepSize()) {
            $config['stepping'] = (int) $this->getMinuteStepSize();
        }
        if ($this->getStartYear()) {
            $config['minDate'] = $this->getStartYear() . '-01-01';
        }
        return $config;
    }
    
    /**
    * Insert property html
    *
    */
    public function render()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        
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
            $tpl->setVariable('START_ARIA_LABEL', ilUtil::prepareFormOutput($start_txt));
            $tpl->touchBlock('start_width_bl');
        }
        
        $end_txt = $this->getEndText();
        if ($end_txt === null) {
            $end_txt = $lng->txt("form_date_duration_end");
        }
        if (trim($end_txt)) {
            $tpl->setVariable('END_LABEL', $end_txt);
            $tpl->setVariable('END_ARIA_LABEL', ilUtil::prepareFormOutput($end_txt));
            $tpl->touchBlock('end_width_bl');
        }
        
        
        $tpl->setVariable('DATE_START_ID', $this->getPostVar() . '[start]');
        $tpl->setVariable('DATE_END_ID', $this->getPostVar() . '[end]');
        
        // placeholder
        // :TODO: i18n?
        $pl_format = ilCalendarUtil::getUserDateFormat($this->getDatePickerTimeFormat());
        $tpl->setVariable('START_PLACEHOLDER', $pl_format);
        $tpl->setVariable('END_PLACEHOLDER', $pl_format);
        
        
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
    
    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Used for table filter presentation
     * @return string
     */
    public function getTableFilterHTML()
    {
        return $this->render();
    }

    /**
     * Used for storing the date duration data in session for table gui filters
     * @return array
     */
    public function getValue()
    {
        return array(
            'start' => $this->getStart()->get(IL_CAL_UNIX),
            'end' => $this->getEnd()->get(IL_CAL_UNIX)
        );
    }

    /**
     * Called from table gui with the stored session value
     * Attention: If the user resets the table filter, a boolean false is passed by the table gui
     * @see getValue()
     * @param array|bool $value
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->setStart(new ilDateTime($value['start'], IL_CAL_UNIX));
            $this->setEnd(new ilDateTime($value['end'], IL_CAL_UNIX));
        }
    }

    public function hideSubForm()
    {
        if ($this->invalid_input_start ||
            $this->invalid_input_end) {
            return false;
        }

        return ((!$this->getStart() || $this->getStart()->isNull()) &&
            (!$this->getEnd() || $this->getEnd()->isNull()));
    }

    /**
     * @return bool
     */
    public function openIntervalsAllowed() : bool
    {
        return $this->allowOpenIntervals;
    }

    /**
     * @param bool $allowOpenInterval
     */
    public function setAllowOpenIntervals(bool $allowOpenInterval)
    {
        $this->allowOpenIntervals = $allowOpenInterval;
    }
}
