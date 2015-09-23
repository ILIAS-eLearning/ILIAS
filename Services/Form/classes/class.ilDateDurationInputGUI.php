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
	protected $start = null;
	protected $startyear = null;	
	protected $start_text = '';
	protected $end_text = '';	
	protected $minute_step_size = 5;
	protected $end = null;	
	protected $showtime = false;	
	protected $toggle_fulltime = false;
	protected $toggle_fulltime_txt = '';
	protected $toggle_fulltime_checked = false;
	
	/**
	* Constructor
	*
	* @param	string	$a_title	Title
	* @param	string	$a_postvar	Post Variable
	*/
	public function __construct($a_title = "", $a_postvar = "")
	{
		parent::__construct($a_title, $a_postvar);
		$this->setType("dateduration");
	}
	
	/**
	 * Enable toggling between date and time
	 * @param object $a_title
	 * @param object $a_checked
	 * @return 
	 */
	public function enableToggleFullTime($a_title,$a_checked)
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
	 * Try to parse incoming value to date object
	 * 
	 * @param mixed $a_value
	 * @param int $a_format
	 * @return ilDateTime|ilDate
	 */
	protected function parseIncoming($a_value, $a_format = null)
	{								
		if($a_format === null)
		{
			$a_format = $this->getDatePickerTimeFormat();
		}
		
		if(is_object($a_value) && 
			$a_value instanceof ilDateTime)
		{
			return $a_value;
		}
		else if(trim($a_value))
		{							
			$parsed = ilCalendarUtil::parseDateString($a_value, $a_format);	
			if(is_object($parsed["date"]))
			{
				return $parsed["date"];
			}
		}		
	}
		
	/**
	* Set value by array
	*
	* @param	array	$a_values	value array
	*/
	public function setValueByArray($a_values)
	{		
		$incoming = $a_values[$this->getPostVar()];		
		if(is_array($incoming))
		{
			$this->setStart($this->parseIncoming($incoming["start"]));
			$this->setEnd($this->parseIncoming($incoming["end"]));
		}
	}
	
	/**
	* Check input, strip slashes etc. set alert, if input is not ok.
	*
	* @return	boolean		Input ok, true/false
	*/	
	public function checkInput()
	{
		global $lng,$ilUser;
		
		if($this->getDisabled())
		{
			return true;
		}
		
		$post = $_POST[$this->getPostVar()];
		if(!is_array($post))
		{
			return false;
		}
		
		$start = $post["start"];
		$end = $post["end"];
		
		// if full day is active, ignore time format
		$format = $post['tgl']
			? 0
			: null;
		
		// always done to make sure there are no obsolete values left
		$this->setStart(null);
		$this->setEnd(null);
		
		$valid_start = false;
		if(trim($start))
		{
			$parsed = $this->parseIncoming($start, $format);
			if($parsed)
			{									
				$this->setStart($parsed);
				$valid_start = true;
			}		
			else
			{
				$this->invalid_input_start = $start;
			}		
		}
		else if(!$this->getRequired() && !trim($end))
		{
			$valid_start = true;			
		}
								
		$valid_end = false;		
		if(trim($end))
		{			
			$parsed = $this->parseIncoming($end, $format);		
			if($parsed)
			{
				$this->setEnd($parsed);
				$valid_end = true;
			}		
			else
			{
				$this->invalid_input_end = $end;
			}
		}
		else if(!$this->getRequired() && !trim($start))
		{					
			$valid_end = true;			
		}
		
		$valid = ($valid_start && $valid_end);		
		
		if($this->getStart() && 
			$this->getEnd())
		{
			if($this->getEnd()->get(IL_CAL_UNIX) < $this->getStart()->get(IL_CAL_UNIX))
			{
				$valid = false;
			}
		}
		
		// :TODO: proper messages?
		if(!$valid)
		{
			$this->setAlert($lng->txt("exc_date_not_valid"));
		}				
		
		return $valid;
	}
	
	protected function getDatePickerTimeFormat()
	{					
		return (int)$this->getShowTime() + (int)$this->getShowSeconds();
	}
	
	/**
	 * parse properties to datepicker config
	 * 
	 * @return array
	 */
	protected function parseDatePickerConfig()
	{
		$config = null;
		if($this->getMinuteStepSize())
		{
			$config['stepping'] = (int)$this->getMinuteStepSize();
		}
		if($this->getStartYear())
		{
			$config['minDate'] = $this->getStartYear().'-01-01';
		}					
		return $config;
	}
	
	/**
	* Insert property html
	*
	*/
	public function render()
	{
		global $ilUser;
		
		$tpl = new ilTemplate("tpl.prop_datetime_duration.html", true, true, "Services/Form");
		
		if($this->enabledToggleFullTime())
		{
			$toggle_id = md5($this->getPostVar().'_fulltime'); // :TODO: unique?
			
			$tpl->setCurrentBlock('toggle_fullday');
			$tpl->setVariable('DATE_TOGGLE_ID', $this->getPostVar().'[tgl]');
			$tpl->setVariable('FULLDAY_TOGGLE_ID', $toggle_id);
			$tpl->setVariable('FULLDAY_TOGGLE_CHECKED', $this->toggle_fulltime_checked ? 'checked="checked"' : '');
			$tpl->setVariable('FULLDAY_TOGGLE_DISABLED', $this->getDisabled() ? 'disabled="disabled"' : '');
			$tpl->setVariable('TXT_TOGGLE_FULLDAY', $this->toggle_fulltime_txt);
			$tpl->parseCurrentBlock();			
		}
		
		// config picker		
		if(!$this->getDisabled())
		{											
			// :TODO: unique?
			$picker_start_id = md5($this->getPostVar().'_start'); 
			$picker_end_id = md5($this->getPostVar().'_end');	
			
			$tpl->setVariable('DATEPICKER_START_ID', $picker_start_id);				
			$tpl->setVariable('DATEPICKER_END_ID', $picker_end_id);			
			
			ilCalendarUtil::addDateTimePicker(
				$picker_start_id, 
				$this->getDatePickerTimeFormat(),
				$this->parseDatePickerConfig(),
				$picker_end_id,
				$this->parseDatePickerConfig(),
				$toggle_id
			);			
		}
		else
		{
			$tpl->setVariable('DATEPICKER_START_DISABLED', 'disabled="disabled" ');	
			$tpl->setVariable('DATEPICKER_END_DISABLED', 'disabled="disabled" ');	
		}		
		
		if(strlen($this->getStartText()))
		{
			$tpl->setVariable('START_LABEL',$this->getStartText());
			$tpl->touchBlock('start_width_bl');
		}
		if(strlen($this->getEndText()))
		{
			$tpl->setVariable('END_LABEL',$this->getEndText());
			$tpl->touchBlock('end_width_bl');
		}
		
		
		$tpl->setVariable('DATE_START_ID', $this->getPostVar().'[start]');		
		$tpl->setVariable('DATE_END_ID', $this->getPostVar().'[end]');
		
		
		// values
		
		$date_value = $this->invalid_input_start;			
		if(!$date_value &&
			$this->getStart())
		{			
			$out_format = ilCalendarUtil::getUserDateFormat($this->getDatePickerTimeFormat(), true);		
			$date_value = $this->getStart()->get(IL_CAL_FKT_DATE, $out_format, $ilUser->getTimeZone());								
		}
		$tpl->setVariable('DATEPICKER_START_VALUE', $date_value);
		
		$date_value = $this->invalid_input_end;			
		if(!$date_value &&
			$this->getEnd())
		{			
			$out_format = ilCalendarUtil::getUserDateFormat($this->getDatePickerTimeFormat(), true);		
			$date_value = $this->getEnd()->get(IL_CAL_FKT_DATE, $out_format, $ilUser->getTimeZone());								
		}
		$tpl->setVariable('DATEPICKER_END_VALUE', $date_value);
				
		
		return $tpl->get();
	}
	
	/**
	* Insert property html
	*
	* @return	int	Size
	*/
	public function insert(&$a_tpl)
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
			'end'   => $this->getEnd()->get(IL_CAL_UNIX)
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
		if(is_array($value))
		{
			$this->setStart(new ilDateTime($value['start'], IL_CAL_UNIX));
			$this->setEnd(new ilDateTime($value['end'], IL_CAL_UNIX));
		}
	}
}
